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
        Schema::table('price_negotiations', function (Blueprint $table) {
            $table->text('reason')->nullable()->after('proposed_amount');
            $table->text('admin_response')->nullable()->after('message');
            $table->decimal('counter_amount', 10, 2)->nullable()->after('admin_response');
            $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null')->after('counter_amount');
            $table->timestamp('processed_at')->nullable()->after('processed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('price_negotiations', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['reason', 'admin_response', 'counter_amount', 'processed_by', 'processed_at']);
        });
    }
};