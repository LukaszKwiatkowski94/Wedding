<?php

namespace App\Http\Controllers;

use App\Models\WeddingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class SessionController extends Controller
{
    public function create()
    {
        abort_unless(WeddingSettings::query()->whereKey(WeddingSettings::ID)->whereNotNull('admin_user_id')->exists(), 404);

        return view('login');
    }

    public function store(Request $request)
    {
        $settings = WeddingSettings::query()->find(WeddingSettings::ID);
        abort_unless($settings?->admin_user_id, 404);
        $credentials = $request->validate(['email' => ['required', 'email'], 'password' => ['required', 'string']]);

        if (! Auth::attempt($credentials + ['id' => $settings->admin_user_id])) {
            throw ValidationException::withMessages(['email' => 'Nieprawidłowy adres e-mail lub hasło.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('settings.edit'));
    }

    public function destroy(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
