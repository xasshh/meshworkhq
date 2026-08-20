<?php

use App\Enums\VerificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Identity verification for clients.
     *
     * Deliberately absent: any column holding a full NIN. A National
     * Identification Number is regulated personal data, and holding one buys
     * breach liability with no product benefit. The NIN path calls a licensed
     * provider and keeps only the outcome plus the last four digits, which is
     * all a support conversation ever needs.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('verification_status')->default(VerificationStatus::Unverified->value)->index();

            // 'nin' for individuals, 'cac' for registered companies.
            $table->string('verification_type')->nullable();

            // Last four digits of a NIN, or a CAC registration number, which is
            // public register data.
            $table->string('verification_reference')->nullable();

            // CAC certificate, on the private disk. Never the public one.
            $table->string('verification_document_path')->nullable();

            // Name the provider or certificate returned, for a mismatch check.
            $table->string('verification_legal_name')->nullable();

            $table->timestamp('verification_submitted_at')->nullable();
            $table->timestamp('verification_reviewed_at')->nullable();
            $table->text('verification_notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'verification_status',
                'verification_type',
                'verification_reference',
                'verification_document_path',
                'verification_legal_name',
                'verification_submitted_at',
                'verification_reviewed_at',
                'verification_notes',
            ]);
        });
    }
};
