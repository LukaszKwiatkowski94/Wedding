<?php

namespace App\Http\Requests;

use Carbon\CarbonImmutable;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WeddingData
{
    public const DATES = ['ceremony_at', 'reception_at', 'songs_close_at', 'games_close_at'];

    public static function validate(Request $request, array $extraRules = []): array
    {
        $dateRules = ['required', 'date_format:Y-m-d\TH:i', function (string $attribute, mixed $value, Closure $fail): void {
            try {
                $date = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $value, config('wedding.timezone'));
                if (! $date || $date->format('Y-m-d\TH:i') !== $value) {
                    $fail('Podaj prawidłową datę i godzinę w strefie Europe/Warsaw.');
                }
            } catch (\Throwable) {
                $fail('Podaj prawidłową datę i godzinę w strefie Europe/Warsaw.');
            }
        }];

        $rules = [
            'first_name' => ['required', 'string', 'max:100'],
            'second_name' => ['required', 'string', 'max:100'],
            'ceremony_at' => $dateRules,
            'ceremony_venue' => ['required', 'string', 'max:255'],
            'ceremony_city' => ['required', 'string', 'max:255'],
            'ceremony_address' => ['nullable', 'string', 'max:255'],
            'ceremony_map_url' => ['nullable', 'url:http,https', 'max:2048'],
            'reception_at' => $dateRules,
            'reception_venue' => ['required', 'string', 'max:255'],
            'reception_city' => ['required', 'string', 'max:255'],
            'reception_address' => ['nullable', 'string', 'max:255'],
            'reception_map_url' => ['nullable', 'url:http,https', 'max:2048'],
            'songs_close_at' => $dateRules,
            'games_close_at' => $dateRules,
        ];

        $validated = Validator::make($request->all(), array_merge($rules, $extraRules))->validate();

        foreach (self::DATES as $field) {
            $validated[$field] = CarbonImmutable::createFromFormat('!Y-m-d\TH:i', $validated[$field], config('wedding.timezone'))->utc();
        }

        return $validated;
    }
}
