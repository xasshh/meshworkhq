<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * The only way to grant or remove staff access.
 *
 * Kept off the web entirely: there is no screen, no invite, and no self
 * service, so an admin can only be created by someone who already has server
 * access.
 */
class GrantAdmin extends Command
{
    protected $signature = 'admin:grant {email} {--revoke : Remove admin access instead of granting it}';

    protected $description = 'Grant or revoke admin access for an account';

    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->components->error("No account found for {$this->argument('email')}.");

            return self::FAILURE;
        }

        $revoking = (bool) $this->option('revoke');

        // is_admin is not fillable, so this is a deliberate force fill.
        $user->forceFill(['is_admin' => ! $revoking])->save();

        $revoking
            ? $this->components->warn("Removed admin access from {$user->name}.")
            : $this->components->info("{$user->name} is now an admin. Sign in and open /admin/verifications.");

        return self::SUCCESS;
    }
}
