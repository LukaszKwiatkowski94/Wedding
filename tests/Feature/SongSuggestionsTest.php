<?php

namespace Tests\Feature;

use App\Models\SongSuggestion;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SongSuggestionsTest extends TestCase
{
    use RefreshDatabase;

    private function configureWedding(string $closeAt = '2027-06-20 21:00:00'): WeddingSettings
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
            'songs_close_at' => $closeAt,
            'games_close_at' => '2027-06-19 21:00:00',
        ]);
    }

    public function test_submissions_are_saved_before_the_deadline_and_duplicates_are_preserved(): void
    {
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-20 20:59:59 UTC'));
        $data = ['title' => 'Piosenka', 'artist' => 'Wykonawca'];

        $this->post('/songs', $data)->assertRedirect('/#piosenki')->assertSessionHas('song_status', 'saved');
        $this->post('/songs', $data)->assertRedirect('/#piosenki');
        $this->assertDatabaseCount('song_suggestions', 2);
    }

    public function test_deadline_is_exclusive_and_the_latest_settings_are_used(): void
    {
        $settings = $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-20 21:00:00 UTC'));
        $data = ['title' => 'Piosenka', 'artist' => 'Wykonawca'];

        $this->post('/songs', $data)->assertRedirect('/#piosenki')->assertSessionHas('song_status', 'closed');
        $settings->update(['songs_close_at' => '2027-06-20 22:00:00']);
        $this->post('/songs', $data)->assertSessionHas('song_status', 'saved');
        $this->travelTo(now()->parse('2027-06-20 22:00:01 UTC'));
        $this->post('/songs', $data)->assertSessionHas('song_status', 'closed');
        $this->assertDatabaseCount('song_suggestions', 1);
    }

    public function test_fields_are_validated_without_requiring_optional_values(): void
    {
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-20 20:00:00 UTC'));
        $this->post('/songs', ['title' => '', 'artist' => str_repeat('a', 256), 'guest_name' => str_repeat('a', 101), 'comment' => str_repeat('a', 2001)])
            ->assertRedirect('/#piosenki')->assertSessionHasErrorsIn('songs', ['title', 'artist', 'guest_name', 'comment']);
        $this->assertDatabaseCount('song_suggestions', 0);
    }

    public function test_unconfigured_and_partially_configured_installations_do_not_accept_songs(): void
    {
        $this->post('/songs', [])->assertRedirect('/setup');
        User::factory()->create();
        $this->post('/songs', [])->assertStatus(503);
        $this->assertDatabaseCount('song_suggestions', 0);
    }

    public function test_public_page_changes_state_at_the_deadline_and_displays_confirmation(): void
    {
        $this->withoutVite();
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-20 20:59:59 UTC'));
        $this->get('/')->assertSee('Zgłoszenia otwarte')->assertSee('Wyślij propozycję');
        $this->post('/songs', ['title' => 'Tytuł', 'artist' => 'Wykonawca'])->assertRedirect('/#piosenki');
        $this->get('/')->assertSee('Twoja propozycja trafiła do nas.');
        $this->travelTo(now()->parse('2027-06-20 21:00:00 UTC'));
        $this->get('/')->assertSee('Zgłoszenia zamknięte')->assertDontSee('Wyślij propozycję');
    }

    public function test_form_and_admin_escape_guest_content(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-20 20:00:00 UTC'));
        $this->post('/songs', ['title' => '<script>alert(1)</script>', 'artist' => ''])->assertSessionHasErrorsIn('songs', 'artist');
        $this->get('/')->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
        SongSuggestion::create(['title' => '<script>alert(1)</script>', 'artist' => 'Wykonawca']);
        $this->actingAs(User::findOrFail($settings->admin_user_id))->get('/admin/songs')
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)->assertDontSee('<script>alert(1)</script>', false);
    }

    public function test_submissions_are_rate_limited(): void
    {
        $this->configureWedding();
        $this->travelTo(now()->parse('2027-06-20 20:00:00 UTC'));
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/songs', ['title' => 'Piosenka', 'artist' => 'Wykonawca'])->assertRedirect();
        }
        $this->post('/songs', ['title' => 'Piosenka', 'artist' => 'Wykonawca'])->assertStatus(429);
        $this->assertDatabaseCount('song_suggestions', 5);
    }

    public function test_only_the_configured_admin_can_view_or_export_suggestions(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding();
        $this->get('/admin/songs')->assertRedirect('/login');
        $this->get('/admin/songs/export')->assertRedirect('/login');
        $other = User::factory()->create();
        $this->actingAs($other)->get('/admin/songs')->assertForbidden();
        $this->actingAs($other)->get('/admin/songs/export')->assertForbidden();
        $admin = User::findOrFail($settings->admin_user_id);
        $this->actingAs($admin)->get('/admin/songs')->assertOk()->assertSee('Nie ma jeszcze żadnych propozycji.');
        $this->actingAs($admin)->get('/admin/songs/export')->assertOk();
    }

    public function test_csv_exports_every_row_and_neutralizes_formulas(): void
    {
        $settings = $this->configureWedding();
        $admin = User::findOrFail($settings->admin_user_id);
        for ($number = 0; $number < 26; $number++) {
            SongSuggestion::create(['title' => 'Tytuł '.$number, 'artist' => 'Wykonawca']);
        }
        SongSuggestion::create(['title' => ' =2+2', 'artist' => '+test', 'guest_name' => '@gość', 'comment' => "-tekst, \"cytat\"\nnowa linia"]);

        $this->withoutVite();
        $this->actingAs($admin)->get('/admin/songs')->assertOk()->assertSee('Strona 1 z 2')->assertDontSee('Tytuł 0');
        $response = $this->actingAs($admin)->get('/admin/songs/export')->assertOk();
        $csv = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $csv);
        $this->assertStringContainsString('Tytuł 0', $csv);
        $this->assertStringContainsString("' =2+2", $csv);
        $this->assertStringContainsString("'+test", $csv);
        $this->assertStringContainsString("'@gość", $csv);
        $this->assertStringContainsString("'-tekst, \"\"cytat\"\"\nnowa linia", $csv);
        $this->assertDatabaseHas('song_suggestions', ['title' => ' =2+2', 'artist' => '+test']);
    }
}
