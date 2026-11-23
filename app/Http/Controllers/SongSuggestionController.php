<?php

namespace App\Http\Controllers;

use App\Models\SongSuggestion;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SongSuggestionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'guest_name' => ['nullable', 'string', 'max:100'],
            'comment' => ['nullable', 'string', 'max:2000'],
        ], [
            'required' => 'To pole jest wymagane.',
            'string' => 'Podaj tekst.',
            'max' => 'Przekroczono dopuszczalną liczbę znaków (:max).',
        ]);

        $result = DB::transaction(function () use ($validator) {
            $settings = WeddingSettings::query()->lockForUpdate()->find(WeddingSettings::ID);
            if (! $settings) {
                abort_if(User::query()->exists(), 503);

                return 'not_ready';
            }
            abort_unless($settings->admin_user_id && User::query()->whereKey($settings->admin_user_id)->exists(), 503);

            if (now()->greaterThanOrEqualTo($settings->songs_close_at)) {
                return 'closed';
            }
            if ($validator->fails()) {
                return 'invalid';
            }

            SongSuggestion::create($validator->validated());

            return 'saved';
        });

        if ($result === 'not_ready') {
            return redirect()->route('setup.create');
        }
        if ($result === 'invalid') {
            return redirect(route('home').'#piosenki')->withErrors($validator, 'songs')->withInput();
        }

        return redirect(route('home').'#piosenki')->with('song_status', $result);
    }
}
