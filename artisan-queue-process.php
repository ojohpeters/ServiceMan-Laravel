<?php
/**
 * Queue Processor Script for cPanel Cron
 * 
 * This script processes queued jobs (emails) and can be run via cPanel cron.
 * Set it to run every 1-5 minutes in cPanel Cron Jobs.
 * 
 * Usage in cPanel Cron:
 * /usr/bin/php /home/username/path/to/artisan-queue-process.php
 */

// Get the directory where this script is located
$basePath = __DIR__;

// Change to the Laravel directory
chdir($basePath);

// Bootstrap Laravel
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Process queue jobs (stop when empty to avoid long-running processes)
$exitCode = Artisan::call('queue:work', [
    '--stop-when-empty' => true,
    '--tries' => 3,
    '--timeout' => 300,
]);

// Log the result
$output = Artisan::output();
$pendingJobs = \DB::table('jobs')->count();

\Log::info('Queue processed via cron', [
    'exit_code' => $exitCode,
    'pending_jobs_remaining' => $pendingJobs,
    'output' => trim($output)
]);

// Store last processed time in cache
\Cache::put('queue_last_processed', now()->toDateTimeString(), 3600);

exit($exitCode);

