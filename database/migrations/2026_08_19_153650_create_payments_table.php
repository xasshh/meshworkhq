<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A pending record is written before the user ever reaches the gateway, so
     * every attempt is accounted for even if they abandon checkout. The
     * reference is unique and is the same string the credit ledger records,
     * which is what makes a replayed webhook harmless.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('credit_bundle_id')->nullable()->constrained()->nullOnDelete();

            $table->string('reference')->unique();
            $table->string('gateway')->default('paystack');
            $table->string('gateway_reference')->nullable();

            // Naira is charged in kobo. Snapshotted so a later price change
            // never rewrites what somebody actually paid.
            $table->unsignedBigInteger('amount_kobo');
            $table->unsignedInteger('credits');

            $table->string('status')->default('pending')->index();
            $table->timestamp('paid_at')->nullable();
            $table->string('failure_reason')->nullable();

            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
