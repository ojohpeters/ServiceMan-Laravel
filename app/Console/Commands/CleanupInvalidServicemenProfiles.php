<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\ServicemanProfile;

class CleanupInvalidServicemenProfiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'servicemen:cleanup
                            {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove serviceman profiles for users who are not actually servicemen';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        $this->info('🔍 Scanning for invalid serviceman profiles...');
        $this->newLine();
        
        // Find serviceman profiles where the user is NOT a serviceman
        $invalidProfiles = ServicemanProfile::whereHas('user', function($query) {
            $query->where('user_type', '!=', 'SERVICEMAN');
        })->with('user')->get();
        
        // Find serviceman profiles where the user doesn't exist
        $orphanedProfiles = ServicemanProfile::whereDoesntHave('user')->get();
        
        $totalInvalid = $invalidProfiles->count() + $orphanedProfiles->count();
        
        if ($totalInvalid === 0) {
            $this->info('✅ No invalid serviceman profiles found. Database is clean!');
            return 0;
        }
        
        $this->warn("Found {$totalInvalid} invalid serviceman profile(s):");
        $this->newLine();
        
        // Display invalid profiles
        if ($invalidProfiles->count() > 0) {
            $this->line('📋 Profiles for NON-SERVICEMAN users:');
            $this->table(
                ['ID', 'User ID', 'User Type', 'Name', 'Email'],
                $invalidProfiles->map(function($profile) {
                    return [
                        $profile->id,
                        $profile->user_id,
                        $profile->user->user_type,
                        $profile->user->full_name ?? $profile->user->username,
                        $profile->user->email,
                    ];
                })
            );
            $this->newLine();
        }
        
        // Display orphaned profiles
        if ($orphanedProfiles->count() > 0) {
            $this->line('👻 Orphaned profiles (user deleted):');
            $this->table(
                ['ID', 'User ID', 'Category ID'],
                $orphanedProfiles->map(function($profile) {
                    return [
                        $profile->id,
                        $profile->user_id,
                        $profile->category_id,
                    ];
                })
            );
            $this->newLine();
        }
        
        if ($isDryRun) {
            $this->warn('🔹 DRY RUN MODE - No changes made');
            $this->info('Run without --dry-run to actually delete these profiles');
            return 0;
        }
        
        // Confirm deletion
        if (!$this->confirm("Do you want to delete these {$totalInvalid} invalid profile(s)?", true)) {
            $this->info('Operation cancelled.');
            return 0;
        }
        
        // Delete invalid profiles
        $deletedCount = 0;
        
        foreach ($invalidProfiles as $profile) {
            $this->line("Deleting profile ID {$profile->id} for {$profile->user->user_type} user: {$profile->user->email}");
            $profile->delete();
            $deletedCount++;
        }
        
        foreach ($orphanedProfiles as $profile) {
            $this->line("Deleting orphaned profile ID {$profile->id}");
            $profile->delete();
            $deletedCount++;
        }
        
        $this->newLine();
        $this->info("✅ Successfully deleted {$deletedCount} invalid serviceman profile(s)!");
        $this->newLine();
        $this->line('💡 Tip: Run "php artisan cache:clear" to clear any cached data.');
        
        return 0;
    }
}
