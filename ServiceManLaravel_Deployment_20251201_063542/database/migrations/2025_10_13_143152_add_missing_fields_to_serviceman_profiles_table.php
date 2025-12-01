<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('serviceman_profiles', function (Blueprint $table) {
            $table->string('experience_years')->nullable()->after('years_of_experience');
            $table->text('skills')->nullable()->after('bio');
            $table->decimal('hourly_rate', 10, 2)->nullable()->after('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('serviceman_profiles', function (Blueprint $table) {
            $table->dropColumn(['experience_years', 'skills', 'hourly_rate']);
        });
    }
};
