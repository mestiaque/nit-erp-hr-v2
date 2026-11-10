<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ME\Hr\Models\HrEmployee;
use ME\Hr\Models\HrEmployeeFinalSettlement;
use ME\Hr\Models\HrEmployeeSeparation;

/**
 * Absence letters under Bangladesh Labour Act 2006, section 27(3A), run every morning (hr:absence-letters).
 *
 *  - Absent `days` calendar days in a row (default 10) → 1st letter.
 *  - Still absent `days` after the 1st letter       → 2nd letter.
 *  - Still absent `days` after the 2nd letter       → 3rd letter, and the employee is marked
 *    Resign from the first day absent (exited_at + separation effective date).
 *
 * The dates go into the same Final Settlement fields HR fills by hand (absent_date, first / second /
 * third_letter_date), so the existing "Print Letter" button prints them unchanged — including the
 * reference no (সুহানা/মানবসম্পদ ও কমপ্লায়েন্স/<year>/<month>/<letter no>).
 *
 * Absent = calendar day with no punch since the last punch. Approved leave days are not counted.
 * An employee who punches again restarts from zero. Employees who have never punched are skipped
 * (an unmapped machine ID must not produce letters), and the whole run is skipped when nobody
 * punched on the day checked (machine data not synced yet).
 *
 * The letters issued each morning are mailed (with a print link per letter) to the recipients
 * picked in Settings > Mail Notifications — the employees themselves are not mailed.
 */
class AbsenceLetters
{
    /** Settings > Mail Notifications key (on / off + hand-picked recipients). */
    public const MAIL_ACTION = 'hr.absence_letters';

    private const LEFT_STATUSES = ['lefty', 'left', 'resign', 'resigned', 'termination', 'transfer'];

    public function days(): int
    {
        return max(1, (int) config('hr.absence_letters.days', 10));
    }

    /**
     * Works out and (unless $dryRun) saves every letter due on $today, checking attendance up to the day before.
     *
     * @return Collection<int, array{employee: HrEmployee, letter: int, absent_from: string, date: string, resigned: bool}>
     */
    public function run(Carbon $today, bool $dryRun = false): Collection
    {
        $today = $today->copy()->startOfDay();
        $asOf = $today->copy()->subDay();
        $need = $this->days();

        if (! DB::table('hr_attendances')->whereDate('date', $asOf->toDateString())
            ->where(fn ($q) => $q->whereNotNull('in_time')->orWhereNotNull('out_time'))->exists()) {
            Log::info('Absence letters skipped: no punch on ' . $asOf->toDateString() . ' (attendance not synced yet?).');

            return collect();
        }

        $employees = HrEmployee::query()->with('finalSettlement')
            ->where('status', 1)
            ->where(fn ($q) => $q->whereNull('employment_status')->orWhereNotIn(DB::raw('LOWER(employment_status)'), self::LEFT_STATUSES))
            ->get();

        $lastPunch = DB::table('hr_attendances')
            ->whereIn('employee_id', $employees->pluck('id'))
            ->where('date', '<=', $asOf->toDateString())
            ->where(fn ($q) => $q->whereNotNull('in_time')->orWhereNotNull('out_time'))
            ->groupBy('employee_id')->selectRaw('employee_id, MAX(date) as last_date')
            ->pluck('last_date', 'employee_id');

        $leaves = DB::table('hr_employee_leaves')->where('status', 'approved')
            ->whereIn('employee_id', $employees->pluck('id'))
            ->where('leave_to', '>=', $asOf->copy()->subDays($need * 4 + 60)->toDateString())
            ->get(['employee_id', 'leave_from', 'leave_to'])->groupBy('employee_id');

        $done = collect();
        foreach ($employees as $emp) {
            if (! isset($lastPunch[$emp->id])) {
                continue;
            }
            $absentFrom = Carbon::parse($lastPunch[$emp->id])->addDay()->startOfDay();
            if ($absentFrom->gt($asOf)) {
                continue; // punched on the day checked
            }

            $empLeaves = $leaves->get($emp->id, collect());
            $onLeave = fn (string $d) => $empLeaves->contains(fn ($l) => $d >= $l->leave_from && $d <= $l->leave_to);
            $absentDays = function (Carbon $from, Carbon $to) use ($onLeave) {
                $n = 0;
                for ($d = $from->copy(); $d->lte($to); $d->addDay()) {
                    $n += $onLeave($d->toDateString()) ? 0 : 1;
                }

                return $n;
            };

            $fs = $emp->finalSettlement;
            $sameRun = $fs && $fs->absent_date && Carbon::parse($fs->absent_date)->toDateString() === $absentFrom->toDateString();
            $l1 = $sameRun && $fs->first_letter_date ? Carbon::parse($fs->first_letter_date)->startOfDay() : null;
            $l2 = $sameRun && $fs->second_letter_date ? Carbon::parse($fs->second_letter_date)->startOfDay() : null;
            $l3 = $sameRun && $fs->third_letter_date ? Carbon::parse($fs->third_letter_date)->startOfDay() : null;

            $letter = match (true) {
                $l3 !== null => null,
                $l2 !== null => $absentDays($l2, $asOf) >= $need ? 3 : null,
                $l1 !== null => $absentDays($l1, $asOf) >= $need ? 2 : null,
                default => $absentDays($absentFrom, $asOf) >= $need ? 1 : null,
            };
            if ($letter === null) {
                continue;
            }

            $done->push(['employee' => $emp, 'letter' => $letter, 'absent_from' => $absentFrom->toDateString(), 'date' => $today->toDateString(), 'resigned' => $letter === 3]);
            if (! $dryRun) {
                DB::transaction(fn () => $this->save($emp, $letter, $absentFrom, $today, $sameRun));
            }
        }

        Log::info('Absence letters ' . ($dryRun ? '(dry run) ' : '') . 'for ' . $today->toDateString() . ': ' . $done->count() . ' letter(s).', [
            'letters' => $done->map(fn ($x) => $x['employee']->employee_id . ' #' . $x['letter'])->all(),
        ]);

        return $done;
    }

    public function mailEnabled(): bool
    {
        return ! class_exists(\App\Models\MailNotificationSetting::class) || \App\Models\MailNotificationSetting::enabled(self::MAIL_ACTION);
    }

    /** The addresses picked for this action in Settings > Mail Notifications (+ approval test recipients). */
    public function recipients(): Collection
    {
        $picked = class_exists(\App\Models\MailNotificationSetting::class)
            ? (\App\Models\MailNotificationSetting::recipientEmailOverride(self::MAIL_ACTION) ?? []) : [];

        return collect($picked)->concat(config('approval.test_recipients', []))
            ->map(fn ($e) => strtolower(trim((string) $e)))
            ->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL) !== false)->unique()->values();
    }

    /**
     * Mails the letters issued on $today (rows from run()); returns the addresses it went to
     * (empty = off, nothing issued, or no recipient). Every attempt is written to the Laravel log.
     */
    public function send(Collection $rows, Carbon $today, ?array $to = null): array
    {
        $context = ['date' => $today->toDateString()];
        if ($rows->isEmpty()) {
            return [];
        }
        if ($to === null && ! $this->mailEnabled()) {
            Log::info('Absence letters mail skipped: off in Settings > Mail Notifications.', $context);

            return [];
        }
        $emails = $to !== null ? collect($to)->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))->values()->all() : $this->recipients()->all();
        if ($emails === []) {
            Log::warning('Absence letters mail not sent: no recipient picked in Settings > Mail Notifications.', $context + ['letters' => $rows->count()]);

            return [];
        }

        $rows = $rows->map(function ($x) {
            $x['employee']->loadMissing(['department', 'designation', 'section']);

            return $x;
        });
        $resigned = $rows->where('resigned', true)->count();
        $subject = $rows->count() . ' absence letter(s) issued on ' . $today->format('d M Y') . ($resigned ? " — {$resigned} marked Resign" : '');
        try {
            Mail::send('hr::emails.absence-letters', ['rows' => $rows, 'today' => $today, 'days' => $this->days()], function ($message) use ($emails, $subject) {
                $message->to($emails)->subject($subject);
                $message->getHeaders()->addTextHeader('X-Keep-Recipients', '1');
            });
        } catch (\Throwable $e) {
            Log::error('Absence letters mail failed: ' . $e->getMessage(), $context + ['recipients' => $emails]);

            throw $e;
        }
        Log::info('Absence letters mail sent.', $context + ['recipients' => $emails, 'letters' => $rows->count()]);

        return $emails;
    }

    private function save(HrEmployee $emp, int $letter, Carbon $absentFrom, Carbon $today, bool $sameRun): void
    {
        $fs = HrEmployeeFinalSettlement::firstOrNew(['employee_id' => $emp->id]);
        if (! $sameRun) {
            // A new absence run: earlier letters belonged to a run the employee came back from.
            $fs->first_letter_date = null;
            $fs->second_letter_date = null;
            $fs->third_letter_date = null;
        }
        $fs->absent_date = $absentFrom->toDateString();
        $fs->{[1 => 'first_letter_date', 2 => 'second_letter_date', 3 => 'third_letter_date'][$letter]} = $today->toDateString();
        $fs->selected_letter_print = [1 => '1st Letter', 2 => '2nd Letter', 3 => '3rd Letter'][$letter];
        $fs->status = 1;
        $fs->save();

        if ($letter === 3) {
            // Section 27(3A): deemed to have resigned from the first day absent.
            $emp->employment_status = 'resign';
            $emp->exited_at = $absentFrom->toDateString();
            $emp->save();

            $sep = HrEmployeeSeparation::firstOrNew(['employee_id' => $emp->id]);
            $sep->status = 'resign';
            $sep->effective_date = $absentFrom->toDateString();
            $sep->remarks = 'Auto: 3rd absence letter issued ' . $today->format('d-m-Y') . ' — Bangladesh Labour Act 2006, section 27(3A).';
            $sep->save();
        }
    }
}
