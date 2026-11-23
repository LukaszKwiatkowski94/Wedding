<?php

namespace App\Http\Controllers;

use App\Models\GameSuggestion;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class GameSuggestionController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:5000'],
            'guest_name' => ['nullable', 'string', 'max:100'],
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

            if (now()->greaterThanOrEqualTo($settings->games_close_at)) {
                return 'closed';
            }
            if ($validator->fails()) {
                return 'invalid';
            }

            GameSuggestion::create($validator->validated());

            return 'saved';
        });

        if ($result === 'not_ready') {
            return redirect()->route('setup.create');
        }
        if ($result === 'invalid') {
            return redirect(route('home', absolute: false).'#zabawy')->withErrors($validator, 'games')->withInput();
        }

        return redirect(route('home', absolute: false).'#zabawy')->with('game_status', $result);
    }
}
