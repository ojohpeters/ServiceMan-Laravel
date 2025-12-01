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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_request_id')->constrained()->onDelete('cascade');
            $table->enum('payment_type', ['INITIAL_BOOKING', 'FINAL_PAYMENT']);
            $table->decimal('amount', 10, 2);
            $table->string('paystack_reference')->unique();
            $table->string('paystack_access_code');
            $table->enum('status', ['PENDING', 'SUCCESSFUL', 'FAILED']);
            $table->json('metadata')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index('paystack_reference');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};