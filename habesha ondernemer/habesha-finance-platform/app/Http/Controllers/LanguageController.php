<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

final class LanguageController extends Controller
{
    public function switch(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'language' => 'required|in:en,nl,am',
        ]);

        $language = $validated['language'];
        
        // Set the application locale
        App::setLocale($language);
        
        // Store in session for persistence
        Session::put('locale', $language);
        
        // Update user preference if authenticated
        if ($request->user()) {
            $request->user()->update(['language' => $language]);
        }

        return redirect()->back()->with('success', 'Taal succesvol gewijzigd naar ' . $this->getLanguageName($language));
    }

    private function getLanguageName(string $code): string
    {
        return match ($code) {
            'en' => 'English',
            'nl' => 'Nederlands',
            'am' => 'አማርኛ',
            default => 'English',
        };
    }
}