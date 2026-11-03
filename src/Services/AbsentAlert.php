<?php

namespace ME\Hr\Services;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use ME\Hr\Models\HrEmployee;

/**
 * Continuous-absence alert: employees absent N working days in a row (config hr.absent_alert.days,
 * default 3), mailed every morning (hr:absent-alert, scheduled) to the recipients picked in
 * Settings > Mail Notifications.
 *
 * Absent = a working day with no punch at all — same rule as the Daily Attendance page. The
 * employee's own weekend, holidays and approved leave days are not working days and are skipped,
 * and so is a day nobody in the company punched (machine data not synced yet, or factory closed) —
 * otherwise a sync gap would report everyone as absent.
 * An employee is listed once per absence run: on the day the run reaches exactly N days.
 */
class AbsentAlert
{
    /** Settings > Mail Notifications key (on / off + hand-picked recipients). */
    public const MAIL_ACTION = 'hr.absent_alert';

    private const LEFT_STATUSES = ['lefty', 'left', 'resign', 'resigned'];

    public function days(): int
    {
        return max(1, (int) config('hr.absent_alert.days', 3));
    }

    /**
     * Employees whose absence run reached exactly days() on $asOf (the last day checked, normally yesterday).
     *
     * @return Collection<int, array{employee: HrEmployee, dates: list<string>}>
     */
    public function find(Carbon $asOf): Collection
    {
        $need = $this->days();
        $asOf = $asOf->copy()->startOfDay();
        // Wide enough to step back over weekends, holidays and leave to find need + 1 working days.
        $from = $asOf->copy()->subDays($need * 2 + 30);

        $employees = HrEmployee::query()
            ->with(['department', 'designation', 'section'])
            ->where('status', 1)
            ->where(fn ($q) => $q->whereNull('employment_status')->orWhereNotIn(DB::raw('LOWER(employment_status)'), self::LEFT_STATUSES))
            ->get();

        $punched = DB::table('hr_attendances')
            ->whereBetween('date', [$from->toDateString(), $asOf->toDateString()])
            ->where(fn ($q) => $q->whereNotNull('in_time')->orWhereNotNull('out_time'))
            ->get(['employee_id', 'date'])
            ->mapWithKeys(fn ($r) => [$r->employee_id . '|' . Carbon::parse($r->date)->toDateString() => true]);

        $holidays = DB::table('hr_holidays')
            ->where('from_date', '<=', $asOf->toDateString())
            ->where('to_date', '>=', $from->toDateString())
            ->get(['from_date', 'to_date']);

        $leaves = DB::table('hr_employee_leaves')
            ->where('status', 'approved')
            ->where('leave_from', '<=', $asOf->toDateString())
            ->where('leave_to', '>=', $from->toDateString())
            ->get(['employee_id', 'leave_from', 'leave_to'])
            ->groupBy('employee_id');

        $isHoliday = fn (string $d) => $holidays->contains(fn ($h) => $d >= $h->from_date && $d <= $h->to_date);
        $hasData = $punched->keys()->map(fn ($k) => explode('|', $k)[1])->flip();

        return $employees->map(function (HrEmployee $emp) use ($need, $asOf, $from, $punched, $isHoliday, $hasData, $leaves) {
            $weekend = strtolower($emp->weekend ?: 'friday');
            $joined = ! blank($emp->join_date) ? Carbon::parse($emp->join_date)->toDateString() : null;
            $empLeaves = $leaves->get($emp->id, collect());

            // The last need + 1 working days, newest first.
            $workDays = [];
            for ($day = $asOf->copy(); $day->gte($from) && count($workDays) <= $need; $day->subDay()) {
                $d = $day->toDateString();
                if ($joined && $d < $joined) {
                    break;
                }
                if (! isset($hasData[$d]) || strtolower($day->format('l')) === $weekend || $isHoliday($d)
                    || $empLeaves->contains(fn ($l) => $d >= $l->leave_from && $d <= $l->leave_to)) {
                    continue;
                }
                $workDays[] = $d;
            }

            $run = array_slice($workDays, 0, $need);
            if (count($run) < $need || $run[0] !== $asOf->toDateString()) {
                return null;
            }
            foreach ($run as $d) {
                if (isset($punched[$emp->id . '|' . $d])) {
                    return null;
                }
            }
            // Already absent the working day before: the run started earlier and was reported then.
            $before = $workDays[$need] ?? null;
            if ($before && ! isset($punched[$emp->id . '|' . $before])) {
                return null;
            }

            return ['employee' => $emp, 'dates' => array_reverse($run)];
        })->filter()->values();
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
     * Mails the alert for $asOf; returns the addresses it went to (empty = off, nobody absent, or
     * no recipient). Every attempt is written to the Laravel log.
     */
    public function send(Carbon $asOf, ?array $to = null): array
    {
        $context = ['date' => $asOf->toDateString()];
        if ($to === null && ! $this->mailEnabled()) {
            Log::info('Absent alert skipped: off in Settings > Mail Notifications.', $context);

            return [];
        }

        $rows = $this->find($asOf);
        if ($rows->isEmpty()) {
            Log::info('Absent alert: nobody reached ' . $this->days() . ' days absent.', $context);

            return [];
        }

        $emails = $to !== null ? collect($to)->filter(fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL))->values()->all() : $this->recipients()->all();
        if ($emails === []) {
            Log::warning('Absent alert not sent: no recipient picked in Settings > Mail Notifications.', $context + ['employees' => $rows->count()]);

            return [];
        }

        $subject = $rows->count() . ' employee(s) absent ' . $this->days() . ' days in a row (up to ' . $asOf->format('d M Y') . ')';
        try {
            Mail::send('hr::emails.absent-alert', ['rows' => $rows, 'days' => $this->days(), 'asOf' => $asOf], function ($message) use ($emails, $subject) {
                $message->to($emails)->subject($subject);
                // Tells the host's test-mode redirect to keep these recipients (it only adds the test ones).
                $message->getHeaders()->addTextHeader('X-Keep-Recipients', '1');
            });
        } catch (\Throwable $e) {
            Log::error('Absent alert failed: ' . $e->getMessage(), $context + ['recipients' => $emails]);

            throw $e;
        }
        Log::info('Absent alert sent.', $context + ['recipients' => $emails, 'employees' => $rows->count()]);

        return $emails;
    }
}
