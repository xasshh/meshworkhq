<?php

use App\Enums\UnlockStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('unlocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brief_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('alert_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('credits_spent')->default(1);
            $table->string('status', 20)->default(UnlockStatus::Active->value);
            $table->float('ai_fit_score')->nullable();
            $table->timestamp('unlocked_at');
            $table->timestamp('pitch_sent_at')->nullable();
            $table->timestamps();

            $table->unique(['brief_id', 'professional_id']);
            $table->index(['professional_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unlocks');
    }
};
