<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Email verification was switched on after these accounts already existed.
     * Without this they would all be bounced to the verification notice on
     * their next visit, for a requirement that did not exist when they signed
     * up. Only accounts created before this deploy are grandfathered in; every
     * new registration verifies normally.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Not reversible: we cannot tell which rows this touched afterwards.
    }
};
