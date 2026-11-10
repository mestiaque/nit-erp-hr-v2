<?php

namespace ME\Hr\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use ME\Hr\Services\AbsenceLetters;

/** Issues the 1st / 2nd / 3rd absence letters due today (and marks Resign on the 3rd). Scheduled every morning — see HrServiceProvider. */
class AbsenceLettersCommand extends Command
{
    use Concerns\UsesHostMailSettings;

    protected $signature = 'hr:absence-letters {--date= : Issue date (default today); attendance is checked up to the day before} {--dry-run : Only list, save and mail nothing} {--to=* : Mail only to these addresses}';

    protected $description = 'Issue the section 27(3A) absence letters due today and mark Resign after the 3rd';

    public function handle(AbsenceLetters $letters): int
    {
        $today = $this->option('date') ? Carbon::parse($this->option('date')) : today();
        $rows = $letters->run($today, (bool) $this->option('dry-run'));

        if ($rows->isEmpty()) {
            $this->line('No absence letter due on ' . $today->toDateString() . '.');

            return self::SUCCESS;
        }

        $this->table(['Emp ID', 'Name', 'Letter', 'Absent from', 'Letter date', 'Resign'], $rows->map(fn ($x) => [
            $x['employee']->employee_id, $x['employee']->name, $x['letter'], $x['absent_from'], $x['date'], $x['resigned'] ? 'Yes' : '',
        ])->all());
        if ($this->option('dry-run')) {
            $this->warn('Dry run — nothing saved or mailed.');

            return self::SUCCESS;
        }

        $this->useHostMailSettings();
        $sent = $letters->send($rows, $today, $this->option('to') ?: null);
        $this->line($sent ? 'Mailed to: ' . implode(', ', $sent) : 'Not mailed (off in Settings > Mail Notifications, or no recipient picked).');

        return self::SUCCESS;
    }
}
