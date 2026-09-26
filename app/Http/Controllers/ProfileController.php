<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(): View
    {
        return view('profile.edit', ['user' => auth()->user()]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160', Rule::unique('users', 'email')->ignore($user->id)],
            'language_pref' => ['required', Rule::in(['en', 'hi', 'gu'])],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $data['name'];
        $user->email = $data['email'] ?: null;
        $user->language_pref = $data['language_pref'];

        if (! empty($data['password'])) {
            $user->password = $data['password'];
        }

        $user->save();
        session(['locale' => $user->language_pref]);

        return back()->with('status', __('ui.common.saved'));
    }
}
