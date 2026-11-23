<?php

namespace App\Http\Controllers;

use App\Http\Requests\WeddingData;
use App\Models\GameSuggestion;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

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
        $gamesOpen = now()->lessThan($settings->games_close_at);
        $games = GameSuggestion::query()->whereNotNull('published_at')->orderBy('published_at')->orderBy('id')->get();
        $timeline = $this->timeline($settings);

        return view('wedding', compact('settings', 'songsOpen', 'gamesOpen', 'games', 'timeline'));
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

    private function timeline(WeddingSettings $settings): Collection
    {
        $events = TimelineEvent::query()->get()->map(fn (TimelineEvent $event) => [
            'date' => $event->event_date->format('Y-m-d'),
            'label' => $event->event_date->locale('pl')->translatedFormat('j F Y'),
            'title' => $event->title,
            'description' => $event->description,
            'is_wedding' => false,
            'order' => $event->id,
        ]);

        $ceremony = $settings->ceremony_at->setTimezone(config('wedding.timezone'));

        return $events->push([
            'date' => $ceremony->format('Y-m-d'),
            'label' => $ceremony->locale('pl')->translatedFormat('j F Y'),
            'title' => 'Ślub',
            'description' => null,
            'is_wedding' => true,
            'order' => PHP_INT_MAX,
        ])->sortBy([['date', 'asc'], ['is_wedding', 'asc'], ['order', 'asc']])->values();
    }
}
