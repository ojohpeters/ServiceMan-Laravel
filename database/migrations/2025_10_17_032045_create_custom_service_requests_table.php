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
        Schema::create('custom_service_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('serviceman_id')->constrained('users')->onDelete('cascade');
            $table->string('service_name');
            $table->text('service_description');
            $table->text('why_needed')->nullable();
            $table->text('target_market')->nullable();
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->text('admin_response')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('status');
            $table->index('serviceman_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_service_requests');
    }
};
