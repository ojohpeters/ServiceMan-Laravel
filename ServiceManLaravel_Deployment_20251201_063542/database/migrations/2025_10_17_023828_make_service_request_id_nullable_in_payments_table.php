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
        Schema::table('payments', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['service_request_id']);
            
            // Modify the column to be nullable
            $table->foreignId('service_request_id')->nullable()->change();
            
            // Re-add the foreign key constraint with nullable support
            $table->foreign('service_request_id')
                  ->references('id')
                  ->on('service_requests')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['service_request_id']);
            
            // Modify the column back to not nullable
            $table->foreignId('service_request_id')->nullable(false)->change();
            
            // Re-add the foreign key constraint
            $table->foreign('service_request_id')
                  ->references('id')
                  ->on('service_requests')
                  ->onDelete('cascade');
        });
    }
};
