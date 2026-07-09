<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UnitScope
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If not authenticated, we don't enforce unit scope (for public routes)
        if (!$user) {
            return $next($request);
        }

        $unitId = $request->header('X-Unit-Id');

        // If Superadmin and wants all units, let it pass
        if ($user->isSuperadmin() && $unitId === 'all') {
            return $next($request);
        }

        // If unitId is provided, verify access
        if ($unitId && $unitId !== 'all') {
            if (!$user->hasUnitAccess((int)$unitId)) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke unit ini.'], 403);
            }
        } else {
            // If no valid unit_id header, fallback to the first unit they have access to
            $firstUnit = $user->units()->first();
            if ($firstUnit) {
                $unitId = $firstUnit->id;
                $request->headers->set('X-Unit-Id', $unitId);
            } else if (!$user->isSuperadmin()) {
                // If not superadmin and has no units, deny access
                return response()->json(['message' => 'Anda tidak terdaftar di unit manapun.'], 403);
            }
        }

        return $next($request);
    }
}
