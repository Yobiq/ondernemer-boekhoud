<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Filament\Facades\Filament;

class RedirectToCorrectPanel
{
    /**
     * Handle an incoming request.
     * Redirects authenticated users to the correct panel based on their roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check for authenticated users
        if (!auth()->check()) {
            return $next($request);
        }
        
        $user = auth()->user();
        $currentPath = $request->path();
        
        // Skip redirect for login pages and API routes
        if (str_contains($currentPath, 'login') || str_contains($currentPath, 'api')) {
            return $next($request);
        }
        
        try {
            // Check if user is trying to access client panel but shouldn't
            if (str_starts_with($currentPath, 'klanten')) {
                $clientPanel = Filament::getPanel('client');
                if (!$user->canAccessPanel($clientPanel)) {
                    // User doesn't have access to client panel, redirect to admin
                    return redirect('/admin');
                }
            }
            
            // Check if user is trying to access admin panel but shouldn't
            if (str_starts_with($currentPath, 'admin')) {
                $adminPanel = Filament::getPanel('admin');
                if (!$user->canAccessPanel($adminPanel)) {
                    // User doesn't have access to admin panel, redirect to client
                    return redirect('/klanten');
                }
            }
        } catch (\Exception $e) {
            // If there's an error getting panels, just continue
            // This prevents issues during initial setup
        }
        
        return $next($request);
    }
}

