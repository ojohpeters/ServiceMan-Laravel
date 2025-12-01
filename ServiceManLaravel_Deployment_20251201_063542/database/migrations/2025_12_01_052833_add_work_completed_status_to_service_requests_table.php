<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to add WORK_COMPLETED status
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM(
            'PENDING_ADMIN_ASSIGNMENT',
            'ASSIGNED_TO_SERVICEMAN',
            'SERVICEMAN_INSPECTED',
            'AWAITING_CLIENT_APPROVAL',
            'NEGOTIATING',
            'AWAITING_PAYMENT',
            'PAYMENT_CONFIRMED',
            'IN_PROGRESS',
            'WORK_COMPLETED',
            'COMPLETED',
            'CANCELLED'
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove WORK_COMPLETED from the enum (revert to original)
        DB::statement("ALTER TABLE service_requests MODIFY COLUMN status ENUM(
            'PENDING_ADMIN_ASSIGNMENT',
            'ASSIGNED_TO_SERVICEMAN',
            'SERVICEMAN_INSPECTED',
            'AWAITING_CLIENT_APPROVAL',
            'NEGOTIATING',
            'AWAITING_PAYMENT',
            'PAYMENT_CONFIRMED',
            'IN_PROGRESS',
            'COMPLETED',
            'CANCELLED'
        )");
    }
};
