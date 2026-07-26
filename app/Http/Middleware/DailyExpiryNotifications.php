<?php

namespace App\Http\Middleware;

use App\Services\ExpiryMonitor;
use Closure;
use Illuminate\Http\Request;

/**
 * Once per day per logged-in user, generate the day's document/certificate expiry
 * notifications. Throttled via the session so it runs on the first request of the
 * day (typically right after login) — no cron scheduler needed on localhost.
 */
class DailyExpiryNotifications
{
    public function __construct(protected ExpiryMonitor $monitor) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user) {
            $today = now()->toDateString();
            if ($request->session()->get('expiry_notif_date') !== $today) {
                try {
                    $this->monitor->runForUser($user);
                } catch (\Throwable $e) {
                    \Log::warning('[ExpiryMonitor] '.$e->getMessage());
                }
                $request->session()->put('expiry_notif_date', $today);
            }
        }
        return $next($request);
    }
}
