<?php

namespace ME\Hr\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use ME\Hr\Services\AbsentAlert;

/** Mails the employees absent N working days in a row. Scheduled every morning — see HrServiceProvider. */
class AbsentAlertCommand extends Command
{
    use Concerns\UsesHostMailSettings;

    protected $signature = 'hr:absent-alert {--date= : Last day to check (default yesterday)} {--to=* : Send only to these addresses}';

    protected $description = 'Mail the employees absent several working days in a row to the Settings > Mail Notifications recipients';

    public function handle(AbsentAlert $alert): int
    {
        $this->useHostMailSettings();

        $asOf = $this->option('date') ? Carbon::parse($this->option('date')) : today()->subDay();
        $sent = $alert->send($asOf, $this->option('to') ?: null);

        $this->line($sent
            ? 'Absent alert for ' . $asOf->toDateString() . ' sent to: ' . implode(', ', $sent)
            : 'Not sent (off in Settings > Mail Notifications, nobody absent ' . $alert->days() . ' days, or no recipient picked).');

        return self::SUCCESS;
    }
}
