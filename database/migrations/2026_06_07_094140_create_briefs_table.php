<?php

use App\Enums\BriefStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('briefs', function (Blueprint $table) {
            $table->id();
            $table->string('ulid', 26)->unique();
            $table->foreignId('client_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->unsignedInteger('budget_min')->nullable();
            $table->unsignedInteger('budget_max')->nullable();
            $table->json('skill_tags')->nullable();
            $table->string('location')->nullable();
            $table->boolean('is_remote')->default(false);
            $table->string('language', 10)->default('en');
            $table->string('status', 30)->default(BriefStatus::Draft->value);
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('hired_professional_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedSmallInteger('alert_wave')->default(0);
            $table->unsignedInteger('total_alerts_sent')->default(0);
            $table->unsignedInteger('total_unlocks')->default(0);
            $table->timestamps();

            $table->index(['client_id', 'status']);
            $table->index(['status', 'expires_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('briefs');
    }
};
