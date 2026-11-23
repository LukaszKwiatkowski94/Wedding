<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings->first_name }} i {{ $settings->second_name }}</title>@vite('resources/css/app.css')
</head>

<body>
    <header class="hero">
        <div class="hero-content">
            <p class="eyebrow">Z radością zapraszamy</p>
            <h1>{{ $settings->first_name }} <span>&amp;</span> {{ $settings->second_name }}</h1>
            <p class="hero-note">Będzie nam ogromnie miło świętować ten dzień razem z Wami.</p>
            <p class="hero-date">{{ $settings->ceremony_at->setTimezone(config('wedding.timezone'))->locale('pl')->translatedFormat('j F Y') }}</p><a class="hero-link" href="#szczegoly">Poznaj szczegóły</a>
        </div>
    </header>
    <main>
        <section class="details" id="szczegoly">
            <div class="details-inner">
                <p class="section-label">Gdzie i kiedy</p>
                <h2>Miejsce naszego święta</h2>
                <div class="details-grid">
                    @foreach (['ceremony' => 'Ceremonia ślubna', 'reception' => 'Przyjęcie weselne'] as $prefix => $label)
                    <article class="detail-item">
                        <h3>{{ $label }}</h3>
                        <p class="venue">{{ $settings->{$prefix.'_venue'} }}</p>
                        <p>{{ $settings->{$prefix.'_city'} }}@if ($settings->{$prefix.'_address'}), {{ $settings->{$prefix.'_address'} }}@endif</p>
                        <p>{{ $settings->{$prefix.'_at'}->setTimezone(config('wedding.timezone'))->locale('pl')->translatedFormat('j F Y, H:i') }}</p>@if ($settings->{$prefix.'_map_url'})<a href="{{ $settings->{$prefix.'_map_url'} }}" target="_blank" rel="noopener noreferrer">Otwórz mapę</a>@endif
                    </article>
                    @endforeach
                </div>
            </div>
        </section>
    </main>
    <footer>{{ $settings->first_name }} i {{ $settings->second_name }}</footer>
</body>

</html>