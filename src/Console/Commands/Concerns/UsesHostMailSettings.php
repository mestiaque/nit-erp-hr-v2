<?php

namespace ME\Hr\Console\Commands\Concerns;

trait UsesHostMailSettings
{
    /**
     * The host applies the SMTP saved in General Settings only for web requests — its general()
     * helper returns null in console — so apply the same settings here (as acc-sfl:day-end-report does).
     */
    protected function useHostMailSettings(): void
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
