<?php

namespace Tests\Feature;

use App\Models\TimelineEvent;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TimelineEventsTest extends TestCase
{
    use RefreshDatabase;

    private function configureWedding(string $ceremonyAt = '2027-06-26 14:30:00'): WeddingSettings
    {
        return WeddingSettings::create([
            'id' => WeddingSettings::ID,
            'admin_user_id' => User::factory()->create()->id,
            'first_name' => 'Anna',
            'second_name' => 'Jan',
            'ceremony_at' => $ceremonyAt,
            'ceremony_venue' => 'Ceremonia',
            'ceremony_city' => 'Miasto',
            'reception_at' => '2027-06-26 16:00:00',
            'reception_venue' => 'Przyjęcie',
            'reception_city' => 'Miasto',
            'songs_close_at' => '2027-06-20 21:00:00',
            'games_close_at' => '2027-06-19 21:00:00',
        ]);
    }

    private function admin(WeddingSettings $settings): User
    {
        return User::findOrFail($settings->admin_user_id);
    }

    public function test_admin_can_create_edit_and_delete_events_with_confirmation(): void
    {
        $this->withoutVite();
        $admin = $this->admin($this->configureWedding());

        $this->actingAs($admin)->post('/admin/timeline', [
            'event_date' => '2027-03-01',
            'title' => 'Pierwsza randka',
            'description' => '',
        ])->assertRedirect('/admin/timeline')->assertSessionHas('status');
        $this->assertDatabaseHas('timeline_events', ['title' => 'Pierwsza randka', 'description' => null]);

        $event = TimelineEvent::query()->firstOrFail();
        $this->actingAs($admin)->put('/admin/timeline/'.$event->id, [
            'event_date' => '2027-03-02',
            'title' => 'Druga randka',
            'description' => 'Kawiarnia',
        ])->assertRedirect('/admin/timeline');
        $this->assertDatabaseHas('timeline_events', ['id' => $event->id, 'title' => 'Druga randka', 'description' => 'Kawiarnia']);

        $this->actingAs($admin)->get('/admin/timeline/'.$event->id.'/delete')->assertOk()->assertSee('Druga randka');
        $this->assertDatabaseHas('timeline_events', ['id' => $event->id]);

        $this->actingAs($admin)->delete('/admin/timeline/'.$event->id)->assertRedirect('/admin/timeline');
        $this->assertDatabaseMissing('timeline_events', ['id' => $event->id]);
    }

    public function test_guests_and_other_users_cannot_manage_the_timeline(): void
    {
        $this->withoutVite();
        $this->configureWedding();
        $event = TimelineEvent::create(['event_date' => '2027-03-01', 'title' => 'Tajne wydarzenie']);

        $this->get('/admin/timeline')->assertRedirect('/login');
        $this->post('/admin/timeline', ['event_date' => '2027-03-01', 'title' => 'X'])->assertRedirect('/login');
        $this->delete('/admin/timeline/'.$event->id)->assertRedirect('/login');

        $other = User::factory()->create();
        $this->actingAs($other)->get('/admin/timeline')->assertForbidden();
        $this->actingAs($other)->get('/admin/timeline/create')->assertForbidden();
        $this->actingAs($other)->post('/admin/timeline', ['event_date' => '2027-03-01', 'title' => 'X'])->assertForbidden();
        $this->actingAs($other)->get('/admin/timeline/'.$event->id.'/edit')->assertForbidden();
        $this->actingAs($other)->put('/admin/timeline/'.$event->id, ['event_date' => '2027-03-02', 'title' => 'Zmiana'])->assertForbidden();
        $this->actingAs($other)->get('/admin/timeline/'.$event->id.'/delete')->assertForbidden();
        $this->actingAs($other)->delete('/admin/timeline/'.$event->id)->assertForbidden();

        $this->assertDatabaseHas('timeline_events', ['id' => $event->id, 'title' => 'Tajne wydarzenie']);
    }

    public function test_unknown_event_ids_return_not_found_for_admin(): void
    {
        $this->withoutVite();
        $admin = $this->admin($this->configureWedding());

        $this->actingAs($admin)->get('/admin/timeline/999/edit')->assertNotFound();
        $this->actingAs($admin)->put('/admin/timeline/999', ['event_date' => '2027-03-02', 'title' => 'X'])->assertNotFound();
        $this->actingAs($admin)->delete('/admin/timeline/999')->assertNotFound();
    }

    public function test_invalid_input_is_rejected(): void
    {
        $admin = $this->admin($this->configureWedding());

        $this->actingAs($admin)->from('/admin/timeline/create')->post('/admin/timeline', [
            'event_date' => 'nie-data',
            'title' => '',
            'description' => str_repeat('a', 2001),
        ])->assertRedirect('/admin/timeline/create')->assertSessionHasErrors(['event_date', 'title', 'description']);

        $this->assertDatabaseCount('timeline_events', 0);
    }

    public function test_public_page_orders_events_and_places_wedding_last_on_its_day(): void
    {
        $this->withoutVite();
        $this->configureWedding();
        TimelineEvent::create(['event_date' => '2027-06-26', 'title' => 'Wpis dzienny pierwszy']);
        TimelineEvent::create(['event_date' => '2027-01-01', 'title' => 'Wpis wczesny']);
        TimelineEvent::create(['event_date' => '2027-06-26', 'title' => 'Wpis dzienny drugi']);

        $this->get('/')->assertOk()->assertSeeInOrder([
            '<h3>Wpis wczesny</h3>',
            '<h3>Wpis dzienny pierwszy</h3>',
            '<h3>Wpis dzienny drugi</h3>',
            '<h3>Ślub</h3>',
        ], false);
    }

    public function test_public_page_escapes_titles_and_descriptions(): void
    {
        $this->withoutVite();
        $this->configureWedding();
        TimelineEvent::create([
            'event_date' => '2027-03-01',
            'title' => '<script>alert(1)</script>',
            'description' => '<b>Opis</b>',
        ]);

        $this->get('/')->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false)
            ->assertSee('&lt;b&gt;Opis&lt;/b&gt;', false);
    }

    public function test_wedding_date_uses_warsaw_time_and_follows_settings(): void
    {
        $this->withoutVite();
        $settings = $this->configureWedding('2027-06-25 22:30:00');

        $this->get('/')->assertSee('datetime="2027-06-26"', false);

        $settings->update(['ceremony_at' => '2027-06-27 12:00:00']);

        $this->get('/')->assertSee('datetime="2027-06-27"', false)->assertDontSee('datetime="2027-06-26"', false);
    }

    public function test_admin_list_is_paginated(): void
    {
        $this->withoutVite();
        $admin = $this->admin($this->configureWedding());
        for ($i = 1; $i <= 26; $i++) {
            TimelineEvent::create(['event_date' => '2027-01-01', 'title' => 'Wydarzenie '.$i]);
        }

        $this->actingAs($admin)->get('/admin/timeline')->assertOk()->assertSee('Strona 1 z 2');
        $this->actingAs($admin)->get('/admin/timeline?page=2')->assertOk()->assertSee('Strona 2 z 2');
    }
}
