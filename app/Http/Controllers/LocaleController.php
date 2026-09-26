<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LocaleController extends Controller
{
    public function __invoke(string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, ['en', 'hi', 'gu'], true), 404);

        session(['locale' => $locale]);

        if ($user = auth()->user()) {
            $user->update(['language_pref' => $locale]);
        }

        return back();
    }
}
