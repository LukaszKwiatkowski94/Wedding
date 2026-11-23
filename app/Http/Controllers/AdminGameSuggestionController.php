<?php

namespace App\Http\Controllers;

use App\Models\GameSuggestion;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;

class AdminGameSuggestionController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        $settings = WeddingSettings::query()->findOrFail(WeddingSettings::ID);
        abort_unless($request->user()->id === $settings->admin_user_id, 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $suggestions = GameSuggestion::query()->orderByDesc('created_at')->orderByDesc('id')->paginate(25);

        return view('admin.games', compact('suggestions'));
    }

    public function publish(Request $request, int $game)
    {
        $this->authorizeAdmin($request);
        GameSuggestion::query()->findOrFail($game);
        GameSuggestion::query()->whereKey($game)->whereNull('published_at')->update(['published_at' => now()]);

        return redirect()->route('admin.games.index', ['page' => $request->input('page', 1)])->with('status', 'Zabawa jest opublikowana.');
    }

    public function unpublish(Request $request, int $game)
    {
        $this->authorizeAdmin($request);
        GameSuggestion::query()->findOrFail($game);
        GameSuggestion::query()->whereKey($game)->whereNotNull('published_at')->update(['published_at' => null]);

        return redirect()->route('admin.games.index', ['page' => $request->input('page', 1)])->with('status', 'Publikacja zabawy została cofnięta.');
    }
}
