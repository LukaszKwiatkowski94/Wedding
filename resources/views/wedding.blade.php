<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $settings->first_name }} i {{ $settings->second_name }}</title>@vite('resources/css/app.css')
</head>

<body>
    <nav class="site-nav" aria-label="Nawigacja główna">
        <a class="site-brand" href="#">{{ $settings->first_name }} <span>&amp;</span> {{ $settings->second_name }}</a>
        <a class="song-nav {{ $songsOpen ? 'is-open' : 'is-closed' }}" href="#piosenki">
            <span>Zaproponuj piosenkę</span><small>{{ $songsOpen ? 'Zgłoszenia otwarte' : 'Zgłoszenia zamknięte' }}</small>
        </a>
    </nav>
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
        <section class="song-section" id="piosenki" aria-labelledby="songs-title">
            <div class="song-inner">
                <p class="section-label">Zaproponuj piosenkę</p>
                <h2 id="songs-title">Jaką piosenkę chcesz usłyszeć na naszym weselu?</h2>
                @if (session('song_status') === 'saved')
                <p class="song-notice" role="status">Dziękujemy! Twoja propozycja trafiła do nas.</p>
                @endif
                @if ($songsOpen)
                <p class="song-intro">Czekamy na propozycje do {{ $settings->songs_close_at->setTimezone(config('wedding.timezone'))->locale('pl')->translatedFormat('j F Y, H:i') }}.</p>
                @if (session('song_status') === 'closed')<p class="song-notice" role="status">Zgłoszenia zostały ponownie otwarte.</p>@endif
                @if ($errors->songs->any())<p class="song-error-summary" role="alert">Sprawdź zaznaczone pola i spróbuj ponownie.</p>@endif
                <form class="song-form" method="post" action="{{ route('songs.store') }}">
                    @csrf
                    <div class="song-form-grid">
                        <label for="song-title">Tytuł piosenki<input id="song-title" name="title" value="{{ old('title') }}" maxlength="255" required @if ($errors->songs->has('title')) aria-invalid="true" aria-describedby="song-title-error" @endif>@if ($errors->songs->has('title'))<span class="field-error" id="song-title-error">{{ $errors->songs->first('title') }}</span>@endif</label>
                        <label for="song-artist">Wykonawca<input id="song-artist" name="artist" value="{{ old('artist') }}" maxlength="255" required @if ($errors->songs->has('artist')) aria-invalid="true" aria-describedby="song-artist-error" @endif>@if ($errors->songs->has('artist'))<span class="field-error" id="song-artist-error">{{ $errors->songs->first('artist') }}</span>@endif</label>
                    </div>
                    <label for="song-guest">Imię <span>(opcjonalnie)</span><input id="song-guest" name="guest_name" value="{{ old('guest_name') }}" maxlength="100" @if ($errors->songs->has('guest_name')) aria-invalid="true" aria-describedby="song-guest-error" @endif>@if ($errors->songs->has('guest_name'))<span class="field-error" id="song-guest-error">{{ $errors->songs->first('guest_name') }}</span>@endif</label>
                    <label for="song-comment">Dedykacja lub komentarz <span>(opcjonalnie)</span><textarea id="song-comment" name="comment" rows="4" maxlength="2000" @if ($errors->songs->has('comment')) aria-invalid="true" aria-describedby="song-comment-error" @endif>{{ old('comment') }}</textarea>@if ($errors->songs->has('comment'))<span class="field-error" id="song-comment-error">{{ $errors->songs->first('comment') }}</span>@endif</label>
                    <button type="submit">Wyślij propozycję</button>
                </form>
                @else
                <div class="song-closed" role="status">
                    <p>Przyjmowanie propozycji piosenek zostało zakończone.</p>
                    <p>Dziękujemy za wszystkie zgłoszenia!</p>
                </div>
                @endif
            </div>
        </section>
    </main>
    <footer>{{ $settings->first_name }} i {{ $settings->second_name }}</footer>
</body>

</html>