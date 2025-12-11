<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class LogController extends Controller
{
    /**
     * Show logs dashboard
     */
    public function index(Request $request)
    {
        // Only allow admins
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $logFile = storage_path('logs/laravel.log');
        $logContent = '';
        $lines = 100; // Default to last 100 lines
        
        if ($request->filled('lines')) {
            $lines = min((int)$request->lines, 1000); // Max 1000 lines
        }

        if (File::exists($logFile)) {
            $file = new \SplFileObject($logFile);
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();
            
            $startLine = max(0, $totalLines - $lines);
            $file->seek($startLine);
            
            while (!$file->eof()) {
                $logContent .= $file->current();
                $file->next();
            }
        }

        // Parse log entries
        $entries = $this->parseLogEntries($logContent);
        
        // Filter by level if specified
        if ($request->filled('level')) {
            $entries = array_filter($entries, function($entry) use ($request) {
                return isset($entry['level']) && strtolower($entry['level']) === strtolower($request->level);
            });
        }

        // Filter by search term
        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $entries = array_filter($entries, function($entry) use ($search) {
                return strpos(strtolower($entry['message']), $search) !== false ||
                       (isset($entry['context']) && strpos(strtolower(json_encode($entry['context'])), $search) !== false);
            });
        }

        return view('admin.logs', [
            'entries' => array_reverse($entries),
            'totalLines' => isset($totalLines) ? $totalLines : 0,
            'lines' => $lines,
            'filters' => [
                'level' => $request->level,
                'search' => $request->search,
            ]
        ]);
    }

    /**
     * Clear log file
     */
    public function clear()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
            Log::info('Log file cleared by admin: ' . auth()->user()->email);
        }

        return back()->with('success', 'Log file cleared successfully!');
    }

    /**
     * Download log file
     */
    public function download()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403);
        }

        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            return response()->download($logFile, 'laravel-log-' . date('Y-m-d') . '.log');
        }

        return back()->with('error', 'Log file not found!');
    }

    /**
     * Parse log entries from raw log content
     */
    private function parseLogEntries($content)
    {
        $entries = [];
        $lines = explode("\n", $content);
        $currentEntry = null;

        foreach ($lines as $line) {
            // Match log entry start: [2025-12-03 10:00:00] local.ERROR: ...
            if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\w+)\.(\w+): (.+)/', $line, $matches)) {
                // Save previous entry
                if ($currentEntry) {
                    $entries[] = $currentEntry;
                }
                
                // Start new entry
                $currentEntry = [
                    'date' => $matches[1],
                    'environment' => $matches[2],
                    'level' => $matches[3],
                    'message' => $matches[4],
                    'context' => null,
                    'stack' => null,
                ];
            } elseif ($currentEntry && !empty(trim($line))) {
                // Check if it's JSON context
                if (preg_match('/^\{".*\}$/', trim($line))) {
                    $currentEntry['context'] = json_decode(trim($line), true);
                } elseif (strpos($line, '#') === 0 || strpos($line, 'at ') !== false) {
                    // Stack trace
                    if (!$currentEntry['stack']) {
                        $currentEntry['stack'] = '';
                    }
                    $currentEntry['stack'] .= $line . "\n";
                } else {
                    // Continuation of message
                    $currentEntry['message'] .= "\n" . $line;
                }
            }
        }

        // Add last entry
        if ($currentEntry) {
            $entries[] = $currentEntry;
        }

        return $entries;
    }
}

