<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SetupFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        config(['wedding.setup_secret' => 'a-very-long-installation-secret']);
    }

    private function details(): array
    {
        return [
            'first_name' => 'Diana',
            'second_name' => 'Łukasz',
            'ceremony_at' => '2027-06-26T16:30',
            'ceremony_venue' => 'Kościół',
            'ceremony_city' => 'Białystok',
            'reception_at' => '2027-06-26T18:00',
            'reception_venue' => 'Restauracja',
            'reception_city' => 'Porosły',
            'songs_close_at' => '2027-06-20T23:00',
            'games_close_at' => '2027-01-19T23:00',
        ];
    }

    private function setupData(): array
    {
        return $this->details() + [
            'setup_secret' => 'a-very-long-installation-secret',
            'admin_name' => 'Administrator',
            'email' => 'admin@example.com',
            'password' => 'long-enough-password',
            'password_confirmation' => 'long-enough-password',
        ];
    }

    public function test_setup_requires_a_configured_secret_and_rejects_an_incorrect_one_without_flashing_it(): void
    {
        config(['wedding.setup_secret' => null]);
        $this->get('/setup')->assertStatus(503);
        $this->post('/setup', $this->setupData())->assertStatus(503);
        config(['wedding.setup_secret' => 'correct-secret']);
        $this->from('/setup')->post('/setup', $this->setupData())
            ->assertRedirect('/setup')->assertSessionHasErrors('setup_secret')
            ->assertSessionMissing('_old_input.setup_secret');
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('wedding_settings', 0);
    }

    public function test_setup_validates_fields_and_rejects_invalid_urls_and_nonexistent_local_times(): void
    {
        $this->from('/setup')->post('/setup', array_merge($this->setupData(), [
            'first_name' => '',
            'ceremony_map_url' => 'javascript:alert(1)',
            'ceremony_at' => '2027-03-28T02:30',
        ]))->assertSessionHasErrors(['first_name', 'ceremony_map_url', 'ceremony_at'])
            ->assertSessionMissing('_old_input.setup_secret')
            ->assertSessionMissing('_old_input.password');
        $this->assertDatabaseCount('users', 0);
    }

    public function test_setup_creates_and_authenticates_one_admin_and_cannot_be_repeated(): void
    {
        $this->get('/setup')->assertOk();
        $this->post('/setup', $this->setupData())->assertRedirect('/admin/settings');
        $user = User::firstOrFail();
        $settings = WeddingSettings::findOrFail(WeddingSettings::ID);
        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('long-enough-password', $user->password));
        $this->assertSame($user->id, $settings->admin_user_id);
        $this->assertSame('2027-06-26 14:30:00', $settings->ceremony_at->format('Y-m-d H:i:s'));
        $this->assertSame('2027-01-19 22:00:00', $settings->games_close_at->format('Y-m-d H:i:s'));
        $this->get('/setup')->assertNotFound();
        $this->post('/setup', $this->setupData())->assertNotFound();
        $this->assertDatabaseCount('users', 1);
        $this->assertDatabaseCount('wedding_settings', 1);
        $this->get('/')->assertOk()->assertSee('Diana')->assertSee('Białystok')->assertSee('16:30');
        $this->get('/admin/settings')->assertOk()->assertSee('2027-06-26T16:30');
    }

    public function test_admin_can_log_in_edit_and_log_out_but_other_users_cannot_edit(): void
    {
        $this->post('/setup', $this->setupData());
        $this->post('/logout')->assertRedirect('/login');
        $this->get('/admin/settings')->assertRedirect('/login');
        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'wrong'])->assertSessionHasErrors('email');
        $this->post('/login', ['email' => 'admin@example.com', 'password' => 'long-enough-password'])->assertRedirect('/admin/settings');
        $this->put('/admin/settings', array_merge($this->details(), ['ceremony_city' => 'Warszawa']))->assertRedirect('/admin/settings');
        $this->get('/')->assertSee('Warszawa');
        $this->delete('/admin/settings')->assertStatus(405);
        $this->post('/logout');
        $this->assertGuest();
        $other = User::factory()->create();
        $this->actingAs($other)->get('/admin/settings')->assertForbidden();
        $this->actingAs($other)->put('/admin/settings', $this->details())->assertForbidden();
    }

    public function test_an_existing_user_without_settings_blocks_setup_and_public_page(): void
    {
        User::factory()->create();
        $this->get('/setup')->assertNotFound();
        $this->post('/setup', $this->setupData())->assertNotFound();
        $this->get('/')->assertStatus(503);
    }

    public function test_setup_attempts_are_rate_limited(): void
    {
        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->post('/setup', ['setup_secret' => 'incorrect'])->assertRedirect();
        }
        $this->post('/setup', ['setup_secret' => 'incorrect'])->assertStatus(429);
    }
}
