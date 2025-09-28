<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

final class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated and has a language preference
        if ($request->user() && $request->user()->language) {
            App::setLocale($request->user()->language);
        } 
        // Check session for language preference
        elseif (Session::has('locale')) {
            App::setLocale(Session::get('locale'));
        }
        // Default to Dutch for Habesha users
        else {
            App::setLocale('nl');
        }

        return $next($request);
    }
}