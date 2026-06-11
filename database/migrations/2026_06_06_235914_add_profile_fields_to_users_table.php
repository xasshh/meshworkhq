<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('professional_title')->nullable()->after('name');
            $table->string('phone', 50)->nullable()->after('email_verified_at');
            $table->text('bio')->nullable()->after('phone');
            $table->string('portfolio_url')->nullable()->after('bio');
            $table->json('skill_tags')->nullable()->after('portfolio_url');
            $table->string('company_name')->nullable()->after('skill_tags');
            $table->string('company_size', 50)->nullable()->after('company_name');
            $table->string('company_role')->nullable()->after('company_size');
            $table->text('company_description')->nullable()->after('company_role');
            $table->text('company_services')->nullable()->after('company_description');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'professional_title', 'phone', 'bio', 'portfolio_url', 'skill_tags',
                'company_name', 'company_size', 'company_role', 'company_description', 'company_services',
            ]);
        });
    }
};
