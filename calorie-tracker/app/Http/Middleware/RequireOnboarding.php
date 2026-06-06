<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RequireOnboarding
{
    public function handle(Request $request, Closure $next): mixed
    {
        if (
            auth()->check() &&
            auth()->user()->onboarded_at === null &&
            ! $request->routeIs('onboarding') &&
            ! $request->routeIs('profile.*') &&
            ! $request->routeIs('logout') &&
            ! $request->routeIs('verification.*') &&
            ! $request->routeIs('password.*') &&
            ! $request->routeIs('auth.*') &&
            ! $request->is('livewire/*')
        ) {
            return redirect()->route('onboarding');
        }

        return $next($request);
    }
}
