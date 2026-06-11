<?php

use App\Enums\AlertStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brief_id')->constrained()->cascadeOnDelete();
            $table->foreignId('professional_id')->constrained('users')->cascadeOnDelete();
            $table->unsignedTinyInteger('wave')->default(1);
            $table->string('status', 20)->default(AlertStatus::Notified->value);
            $table->float('ai_relevance_score')->nullable();
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('viewed_at')->nullable();
            $table->timestamps();

            $table->unique(['brief_id', 'professional_id']);
            $table->index(['professional_id', 'status']);
            $table->index(['brief_id', 'wave']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
