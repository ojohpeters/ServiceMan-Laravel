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
        $connection = Schema::getConnection();
        $databaseName = $connection->getDatabaseName();
        
        // Helper function to safely add index
        $addIndexIfNotExists = function($table, $columns, $indexName) use ($connection, $databaseName) {
            $exists = $connection->selectOne(
                "SELECT COUNT(*) as count FROM information_schema.statistics 
                 WHERE table_schema = ? AND table_name = ? AND index_name = ?",
                [$databaseName, $table, $indexName]
            );
            
            if ($exists->count == 0) {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    if (is_array($columns)) {
                        $t->index($columns, $indexName);
                    } else {
                        $t->index($columns, $indexName);
                    }
                });
            }
        };
        
        // Service Requests indexes
        $addIndexIfNotExists('service_requests', ['status', 'created_at'], 'idx_status_created');
        $addIndexIfNotExists('service_requests', ['client_id', 'status'], 'idx_client_status');
        $addIndexIfNotExists('service_requests', ['serviceman_id', 'status'], 'idx_serviceman_status');
        $addIndexIfNotExists('service_requests', ['category_id', 'status'], 'idx_category_status');
        $addIndexIfNotExists('service_requests', ['is_emergency', 'status'], 'idx_emergency_status');
        $addIndexIfNotExists('service_requests', 'booking_date', 'service_requests_booking_date_index');

        // Users indexes
        $addIndexIfNotExists('users', ['user_type', 'is_approved'], 'idx_type_approved');
        $addIndexIfNotExists('users', ['user_type', 'is_email_verified'], 'idx_type_verified');
        $addIndexIfNotExists('users', 'email', 'users_email_index');
        $addIndexIfNotExists('users', 'username', 'users_username_index');

        // Serviceman Profiles indexes
        $addIndexIfNotExists('serviceman_profiles', ['category_id', 'is_available'], 'idx_category_available');
        $addIndexIfNotExists('serviceman_profiles', ['rating', 'total_jobs_completed'], 'idx_rating_jobs');
        $addIndexIfNotExists('serviceman_profiles', 'is_available', 'serviceman_profiles_is_available_index');

        // Payments indexes
        $addIndexIfNotExists('payments', ['status', 'paid_at'], 'idx_status_paid');
        $addIndexIfNotExists('payments', ['service_request_id', 'status'], 'idx_request_status');
        $addIndexIfNotExists('payments', 'paid_at', 'payments_paid_at_index');

        // Notifications indexes
        $addIndexIfNotExists('notifications', ['user_id', 'is_read'], 'idx_user_read');
        $addIndexIfNotExists('notifications', ['user_id', 'created_at'], 'idx_user_created');
        $addIndexIfNotExists('notifications', 'type', 'notifications_type_index');

        // Ratings indexes
        if (Schema::hasTable('ratings')) {
            $addIndexIfNotExists('ratings', ['serviceman_id', 'created_at'], 'idx_serviceman_created');
            $addIndexIfNotExists('ratings', 'service_request_id', 'ratings_service_request_id_index');
        }

        // Serviceman Availabilities indexes
        if (Schema::hasTable('serviceman_availabilities')) {
            $addIndexIfNotExists('serviceman_availabilities', ['serviceman_id', 'date'], 'idx_serviceman_date');
            $addIndexIfNotExists('serviceman_availabilities', ['date', 'is_available'], 'idx_date_available');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_requests', function (Blueprint $table) {
            $table->dropIndex('idx_status_created');
            $table->dropIndex('idx_client_status');
            $table->dropIndex('idx_serviceman_status');
            $table->dropIndex('idx_category_status');
            $table->dropIndex('idx_emergency_status');
            $table->dropIndex('service_requests_booking_date_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_type_approved');
            $table->dropIndex('idx_type_verified');
            $table->dropIndex('users_email_index');
            $table->dropIndex('users_username_index');
        });

        Schema::table('serviceman_profiles', function (Blueprint $table) {
            $table->dropIndex('idx_category_available');
            $table->dropIndex('idx_rating_jobs');
            $table->dropIndex('serviceman_profiles_is_available_index');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex('idx_status_paid');
            $table->dropIndex('idx_request_status');
            $table->dropIndex('payments_paid_at_index');
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex('idx_user_read');
            $table->dropIndex('idx_user_created');
            $table->dropIndex('notifications_type_index');
        });

        if (Schema::hasTable('ratings')) {
            Schema::table('ratings', function (Blueprint $table) {
                $table->dropIndex('idx_serviceman_created');
                $table->dropIndex('ratings_service_request_id_index');
            });
        }

        if (Schema::hasTable('serviceman_availabilities')) {
            Schema::table('serviceman_availabilities', function (Blueprint $table) {
                $table->dropIndex('idx_serviceman_date');
                $table->dropIndex('idx_date_available');
            });
        }
    }
};

