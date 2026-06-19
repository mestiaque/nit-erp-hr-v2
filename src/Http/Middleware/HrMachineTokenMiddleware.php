<?php

namespace ME\Hr\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HrMachineTokenMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredToken = config('hr.machine_api_token');

        if (!$configuredToken) {
            return response()->json(['status' => 'error', 'message' => 'Machine API not configured.'], 503);
        }

        $token = $request->bearerToken() ?? $request->input('api_token') ?? $request->header('X-Machine-Token');

        if (!$token || !hash_equals($configuredToken, $token)) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
