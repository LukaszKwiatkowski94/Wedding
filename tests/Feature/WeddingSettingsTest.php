<?php

namespace Tests\Feature;

use App\Models\WeddingSettings;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeddingSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_initial_settings_key_cannot_be_created_twice(): void
    {
        $settings = [
            'id' => WeddingSettings::ID,
            'first_name' => 'Diana',
            'second_name' => 'Łukasz',
            'ceremony_at' => '2027-06-26 14:30:00',
            'ceremony_venue' => 'Kościół',
            'ceremony_city' => 'Białystok',
            'reception_at' => '2027-06-26 16:00:00',
            'reception_venue' => 'Sala',
            'reception_city' => 'Porosły',
            'songs_close_at' => '2027-06-20 20:00:00',
            'games_close_at' => '2027-06-19 20:00:00',
        ];

        WeddingSettings::create($settings);

        $this->expectException(QueryException::class);
        WeddingSettings::create($settings);
    }
}
