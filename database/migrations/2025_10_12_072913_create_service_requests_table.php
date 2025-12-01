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
        Schema::create('service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('serviceman_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('backup_serviceman_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->date('booking_date');
            $table->boolean('is_emergency')->default(false);
            $table->boolean('auto_flagged_emergency')->default(false);
            $table->enum('status', [
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
            ]);
            $table->decimal('initial_booking_fee', 10, 2);
            $table->decimal('serviceman_estimated_cost', 10, 2)->nullable();
            $table->decimal('admin_markup_percentage', 5, 2)->default(10.00);
            $table->decimal('final_cost', 10, 2)->nullable();
            $table->text('client_address');
            $table->text('service_description');
            $table->timestamp('inspection_completed_at')->nullable();
            $table->timestamp('work_completed_at')->nullable();
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('status');
            $table->index('client_id');
            $table->index('serviceman_id');
            $table->index('booking_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};