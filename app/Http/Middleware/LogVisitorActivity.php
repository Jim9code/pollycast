<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogVisitorActivity
{
    /**
     * Log visitor activity to both Render server stdout/stderr logs and the visitor_logs table.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $path = $request->path();

        // Skip health checks and the log viewer page itself
        if ($path === 'up' || str_starts_with($path, 'activity-logs')) {
            return $response;
        }

        try {
            $fullPath = $request->getRequestUri();
            $method = $request->method();
            $ip = $request->header('x-forwarded-for')
                ? trim(explode(',', $request->header('x-forwarded-for'))[0])
                : $request->ip();
            $userAgent = (string) $request->userAgent();
            $referer = (string) $request->header('referer', '');
            $timestamp = now()->format('Y-m-d H:i:s');

            // Create a human-readable description of what the visitor did
            $actionLabel = match (true) {
                $path === '/' => 'Viewed Question 1: Polling Unit #' . $request->query('polling_unit_uniqueid', '8 (Default)'),
                $path === 'lga-results' => 'Viewed Question 2: Summed LGA Results (LGA ID #' . $request->query('lga_id', '22') . ')',
                $path === 'polling-unit/create' => 'Opened Question 3: Add New Polling Unit Form',
                $path === 'polling-unit/store' && $method === 'POST' => 'Submitted Question 3: Created New Polling Unit (' . $request->input('polling_unit_name', 'N/A') . ')',
                str_starts_with($path, 'api/lgas/') => 'Used Chained Combo Box (Loaded Wards)',
                str_starts_with($path, 'api/wards/') => 'Used Chained Combo Box (Loaded Polling Units)',
                default => "{$method} {$fullPath}",
            };

            // 1. Output directly to Render Server Console Logs (stderr)
            error_log("[VISITOR-LOG] {$timestamp} | {$actionLabel} | IP: {$ip} | UA: {$userAgent}");

            // 2. Store in database table for the /activity-logs dashboard
            if (Schema::hasTable('visitor_logs')) {
                DB::table('visitor_logs')->insert([
                    'method' => $method,
                    'path' => substr($fullPath, 0, 255),
                    'action_label' => substr($actionLabel, 0, 255),
                    'ip_address' => substr((string) $ip, 0, 64),
                    'user_agent' => $userAgent,
                    'referer' => substr($referer, 0, 255),
                    'visited_at' => $timestamp,
                ]);
            }
        } catch (\Throwable $e) {
            // Never break the application if logging fails
        }

        return $response;
    }
}

