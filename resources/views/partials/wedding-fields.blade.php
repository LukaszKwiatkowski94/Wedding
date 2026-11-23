@php
$groups = [
'Para młoda' => ['first_name' => ['Pierwsze imię', 'text'], 'second_name' => ['Drugie imię', 'text']],
'Ceremonia' => ['ceremony_at' => ['Data i godzina', 'datetime-local'], 'ceremony_venue' => ['Nazwa miejsca', 'text'], 'ceremony_city' => ['Miejscowość', 'text'], 'ceremony_address' => ['Adres (opcjonalnie)', 'text'], 'ceremony_map_url' => ['Link do mapy (opcjonalnie)', 'url']],
'Przyjęcie' => ['reception_at' => ['Data i godzina', 'datetime-local'], 'reception_venue' => ['Nazwa miejsca', 'text'], 'reception_city' => ['Miejscowość', 'text'], 'reception_address' => ['Adres (opcjonalnie)', 'text'], 'reception_map_url' => ['Link do mapy (opcjonalnie)', 'url']],
'Terminy zgłoszeń' => ['songs_close_at' => ['Zamknięcie propozycji piosenek', 'datetime-local'], 'games_close_at' => ['Zamknięcie propozycji zabaw', 'datetime-local']],
];
@endphp
@foreach ($groups as $group => $fields)
<fieldset>
    <legend>{{ $group }}</legend>
    <div class="fields-grid">
        @foreach ($fields as $name => [$label, $type])
        @php
        $value = isset($settings) ? $settings->{$name} : null;
        if ($value && $type === 'datetime-local') $value = $value->setTimezone(config('wedding.timezone'))->format('Y-m-d\TH:i');
        $optional = str_contains($name, 'address') || str_contains($name, 'map_url');
        @endphp
        <label>{{ $label }}<input name="{{ $name }}" type="{{ $type }}" value="{{ old($name, $value) }}" @required(!$optional)>@error($name)<span class="field-error">{{ $message }}</span>@enderror</label>
        @endforeach
    </div>@if ($group === 'Terminy zgłoszeń')<p class="hint">Wszystkie daty i godziny dotyczą czasu polskiego (Europe/Warsaw).</p>@endif
</fieldset>
@endforeach