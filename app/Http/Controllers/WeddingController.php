<?php

namespace App\Http\Controllers;

use App\Http\Requests\WeddingData;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;

class WeddingController extends Controller
{
    public function show()
    {
        $settings = WeddingSettings::query()->find(WeddingSettings::ID);
        if (! $settings) {
            abort_if(User::query()->exists(), 503);

            return redirect()->route('setup.create');
        }
        abort_unless($settings->admin_user_id && User::query()->whereKey($settings->admin_user_id)->exists(), 503);

        $songsOpen = now()->lessThan($settings->songs_close_at);

        return view('wedding', compact('settings', 'songsOpen'));
    }

    public function edit(Request $request)
    {
        $settings = WeddingSettings::query()->findOrFail(WeddingSettings::ID);
        abort_unless($request->user()->id === $settings->admin_user_id, 403);

        return view('settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $settings = WeddingSettings::query()->findOrFail(WeddingSettings::ID);
        abort_unless($request->user()->id === $settings->admin_user_id, 403);
        $settings->update(WeddingData::validate($request));

        return redirect()->route('settings.edit')->with('status', 'Ustawienia zostały zapisane.');
    }
}
