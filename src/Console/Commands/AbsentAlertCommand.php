<?php

namespace ME\Hr\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use ME\Hr\Services\AbsentAlert;

/** Mails the employees absent N working days in a row. Scheduled every morning — see HrServiceProvider. */
class AbsentAlertCommand extends Command
{
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

    /**
     * The host applies the SMTP saved in General Settings only for web requests — its general()
     * helper returns null in console — so apply the same settings here (as acc-sfl:day-end-report does).
     */
    private function useHostMailSettings(): void
    {
        if (! class_exists(\App\Models\General::class)) {
            return;
        }

        $general = \App\Models\General::query()->first();
        if (! $general?->mail_host) {
            return;
        }

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.transport' => 'smtp',
            'mail.mailers.smtp.host' => $general->mail_host,
            'mail.mailers.smtp.port' => $general->mail_port,
            'mail.mailers.smtp.encryption' => $general->mail_encryption,
            'mail.mailers.smtp.username' => $general->mail_username,
            'mail.mailers.smtp.password' => $general->mail_password,
        ]);
        if ($general->mail_from_address) {
            config(['mail.from.address' => $general->mail_from_address, 'mail.from.name' => $general->mail_from_name ?: config('app.name')]);
        }
        app('mail.manager')->purge('smtp');
    }
}
