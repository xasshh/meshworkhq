<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * Proves whether mail actually leaves this machine.
 *
 * The failure it exists to catch is silent: with MAIL_MAILER=log, Laravel
 * reports success for every send while writing to a file nobody reads.
 */
class SendTestMail extends Command
{
    protected $signature = 'mail:test {email : Where to send it}';

    protected $description = 'Send a test email and report what the mail config will actually do';

    public function handle(): int
    {
        $to = $this->argument('email');
        $mailer = config('mail.default');
        $from = config('mail.from.address');

        $this->components->twoColumnDetail('Mailer', $mailer);
        $this->components->twoColumnDetail('From', (string) $from);
        $this->components->twoColumnDetail('To', $to);

        if ($mailer === 'log') {
            $this->components->error('MAIL_MAILER is "log". This writes to storage/logs/laravel.log and delivers nothing.');
            $this->components->bulletList([
                'Set MAIL_MAILER=resend in .env',
                'Set RESEND_API_KEY to a key from resend.com',
                'Run php artisan config:clear, then try again',
            ]);

            return self::FAILURE;
        }

        if ($mailer === 'resend' && blank(config('services.resend.key'))) {
            $this->components->error('MAIL_MAILER is "resend" but RESEND_API_KEY is empty.');

            return self::FAILURE;
        }

        if (str_ends_with((string) $from, '@example.com')) {
            $this->components->warn('MAIL_FROM_ADDRESS is still the placeholder. Most providers reject an unverified sender domain.');
        }

        try {
            Mail::raw(
                "This is a test from Meshwork HQ.\n\nIf you are reading this in your inbox, verification links and brief alerts will arrive too.",
                fn ($message) => $message->to($to)->subject('Meshwork HQ mail test'),
            );
        } catch (\Throwable $e) {
            $this->components->error('The send threw: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->components->info("Handed to {$mailer}. Check the inbox for {$to}, including spam.");

        return self::SUCCESS;
    }
}
