<?php

namespace App\Http\Middleware;

use App\Models\LoginDevice;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware: TrackLoginDevice
 *
 * Updates or creates a device record for the current session.
 */
class TrackLoginDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (Auth::check()) {
            $sessionId = $request->session()->getId();
            $user      = Auth::user();

            LoginDevice::updateOrCreate(
                ['session_id' => $sessionId],
                [
                    'user_id'        => $user->id,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => $request->userAgent(),
                    'device_name'    => LoginDevice::parseDeviceName($request->userAgent() ?? ''),
                    'last_active_at' => now(),
                ]
            );
        }

        return $response;
    }
}
