<?php

namespace App\Http\Middleware;

use App\Support\StaffPresence;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordStaffPresence
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user && ! $request->routeIs('logout')) {
            StaffPresence::closeAbandoned();
            StaffPresence::touch($user);
        }

        return $next($request);
    }
}
