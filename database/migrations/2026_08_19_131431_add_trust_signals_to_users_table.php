<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Track record is public by default because it is the signal that makes a
     * cold marketplace legible, but either side can switch their own count off.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('show_hire_count')->default(true)->after('skill_tags');
            $table->boolean('show_engagement_count')->default(true)->after('show_hire_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['show_hire_count', 'show_engagement_count']);
        });
    }
};
