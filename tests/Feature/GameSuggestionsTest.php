<?php

namespace Tests\Feature;

use App\Models\GameSuggestion;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GameSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private function configureWedding(string $closeAt = '2027-06-19 21:00:00'): WeddingSettings
    {
        return WeddingSettings::create([
            'id' => WeddingSettings::ID,
            'admin_user_id' => User::factory()->create()->id,
            'first_name' => 'Anna',
            'second_name' => 'Jan',
            'ceremony_at' => '2027-06-26 14:30:00',
            'ceremony_venue' => 'Ceremonia',
            'ceremony_city' => 'Miasto',
            'reception_at' => '2027-06-26 16:00:00',
            'reception_venue' => 'Przyjęcie',
            'reception_city' => 'Miasto',
            'songs_close_at' => '2027-06-20 21:00:00',
            'games_close_at' => $closeAt,
        ]);
    }

    public function test_submissions_are_unpublished_and_the_deadline_is_exclusive(): void
    {
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-19 20:59:59 UTC'));
        $data = ['name' => 'Zabawa', 'description' => 'Zasady'];
        $this->post('/games', $data)->assertRedirect('/#zabawy')->assertSessionHas('game_status', 'saved');
        $this->assertDatabaseHas('game_suggestions', ['name' => 'Zabawa', 'published_at' => null]);

        $this->travelTo(now()->parse('2027-06-19 21:00:00 UTC'));
        $this->post('/games', $data)->assertRedirect('/#zabawy')->assertSessionHas('game_status', 'closed');
        $this->assertDatabaseCount('game_suggestions', 1);
    }

    public function test_only_admin_can_publish_and_unpublish_and_public_list_hides_the_guest_name(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding();
        $game = GameSuggestion::create(['name' => 'Tajemnicza zabawa', 'description' => 'Zasady', 'guest_name' => 'Prywatna osoba']);
        $this->get('/')->assertDontSee('Tajemnicza zabawa')->assertSee('Wkrótce podamy');
        $this->patch('/admin/games/'.$game->id.'/publish')->assertRedirect('/login');
        $other = User::factory()->create();
        $this->actingAs($other)->get('/admin/games')->assertForbidden();
        $this->actingAs($other)->patch('/admin/games/'.$game->id.'/publish')->assertForbidden();
        $this->actingAs($other)->patch('/admin/games/'.$game->id.'/unpublish')->assertForbidden();
        $admin = User::findOrFail($settings->admin_user_id);
        $this->actingAs($admin)->patch('/admin/games/'.$game->id.'/publish')->assertRedirect();
        $firstPublication = $game->fresh()->published_at;
        $this->assertNotNull($firstPublication);
        $this->actingAs($admin)->patch('/admin/games/'.$game->id.'/publish')->assertRedirect();
        $this->assertTrue($firstPublication->equalTo($game->fresh()->published_at));
        $this->get('/')->assertSee('Lista zabaw')->assertSee('Tajemnicza zabawa')->assertSee('Zasady')->assertDontSee('Prywatna osoba');
        $this->actingAs($admin)->patch('/admin/games/'.$game->id.'/unpublish')->assertRedirect();
        $this->actingAs($admin)->patch('/admin/games/'.$game->id.'/unpublish')->assertRedirect();
        $this->assertNull($game->fresh()->published_at);
        $this->get('/')->assertDontSee('Tajemnicza zabawa')->assertSee('Wkrótce podamy');
    }

    public function test_validation_and_optional_name(): void
    {
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-19 20:00:00 UTC'));
        $this->post('/games', ['name' => '', 'description' => str_repeat('a', 5001), 'guest_name' => str_repeat('a', 101)])
            ->assertRedirect('/#zabawy')->assertSessionHasErrorsIn('games', ['name', 'description', 'guest_name']);
        $this->post('/games', ['name' => str_repeat('a', 256), 'description' => ''])
            ->assertSessionHasErrorsIn('games', ['name', 'description']);
        $this->post('/games', ['name' => 'Zabawa', 'description' => 'Zasady'])->assertSessionHas('game_status', 'saved');
        $this->assertDatabaseHas('game_suggestions', ['name' => 'Zabawa', 'guest_name' => null]);
        $this->assertDatabaseCount('game_suggestions', 1);
    }

    public function test_changing_the_deadline_takes_effect_and_the_list_remains_visible_after_closing(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-19 21:00:01 UTC'));
        $game = GameSuggestion::create(['name' => 'Zatwierdzona', 'description' => 'Zasady']);
        GameSuggestion::query()->whereKey($game->id)->update(['published_at' => now()]);
        $this->get('/')->assertSee('Przyjmowanie propozycji zabaw zostało zakończone')->assertSee('Zatwierdzona')->assertDontSee('Prześlij pomysł');
        $this->post('/games', ['name' => 'Nowa', 'description' => 'Zasady'])->assertSessionHas('game_status', 'closed');
        $settings->update(['games_close_at' => '2027-06-19 22:00:00']);
        $this->get('/')->assertSee('Prześlij pomysł');
        $this->post('/games', ['name' => 'Nowa', 'description' => 'Zasady'])->assertSessionHas('game_status', 'saved');
        $this->get('/')->assertSee('Twoja propozycja trafiła do nas.')->assertDontSee('Nowa');
    }

    public function test_an_unconfigured_or_incomplete_wedding_cannot_receive_suggestions(): void
    {
        $this->post('/games', [])->assertRedirect('/setup');
        User::factory()->create();
        $this->post('/games', [])->assertStatus(503);
        $this->assertDatabaseCount('game_suggestions', 0);
    }

    public function test_game_limit_is_independent_of_song_limit(): void
    {
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-19 20:00:00 UTC'));
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/games', ['name' => 'Zabawa', 'description' => 'Zasady'])->assertRedirect();
        }
        $this->post('/games', ['name' => 'Zabawa', 'description' => 'Zasady'])->assertStatus(429);
        $this->post('/songs', ['title' => 'Piosenka', 'artist' => 'Wykonawca'])->assertRedirect();
        $this->assertDatabaseCount('game_suggestions', 5);
        $this->assertDatabaseCount('song_suggestions', 1);
    }

    public function test_game_content_is_escaped_in_the_form_admin_and_public_page(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-19 20:00:00 UTC'));
        $payload = '<script>alert(1)</script>';
        $this->post('/games', ['name' => $payload, 'description' => ''])->assertSessionHasErrorsIn('games', 'description');
        $this->get('/')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee($payload, false);
        $game = GameSuggestion::create(['name' => $payload, 'description' => $payload]);
        $admin = User::findOrFail($settings->admin_user_id);
        $this->actingAs($admin)->get('/admin/games')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee($payload, false);
        $this->actingAs($admin)->patch('/admin/games/'.$game->id.'/publish');
        $this->get('/')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee($payload, false);
    }

    public function test_admin_list_is_paginated_and_missing_games_are_not_found(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding();
        for ($number = 0; $number < 26; $number++) {
            GameSuggestion::create(['name' => 'Zabawa '.$number, 'description' => 'Zasady']);
        }
        $this->get('/admin/games')->assertRedirect('/login');
        $admin = User::findOrFail($settings->admin_user_id);
        $this->actingAs($admin)->get('/admin/games')->assertOk()->assertSee('Strona 1 z 2')->assertDontSee('Zabawa 0');
        $this->actingAs($admin)->patch('/admin/games/99999/publish')->assertNotFound();
    }
}
