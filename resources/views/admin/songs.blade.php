<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Propozycje piosenek</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell songs-admin">
        <div class="admin-header">
            <div>
                <h1>Propozycje piosenek</h1>
                <a class="admin-home" href="{{ route('settings.edit') }}">Wróć do ustawień</a>
            </div>
            <a class="admin-action" href="{{ route('admin.songs.export') }}">Pobierz CSV</a>
        </div>
        <p class="admin-count">Zgłoszenia: {{ $suggestions->total() }}</p>
        @forelse ($suggestions as $suggestion)
        <article class="song-entry">
            <div class="song-entry-heading">
                <h2>{{ $suggestion->title }}</h2>
                <time datetime="{{ $suggestion->created_at->toIso8601String() }}">{{ $suggestion->created_at->setTimezone(config('wedding.timezone'))->format('d.m.Y H:i') }}</time>
            </div>
            <p class="song-artist">{{ $suggestion->artist }}</p>
            @if ($suggestion->guest_name)<p class="song-byline">Od: {{ $suggestion->guest_name }}</p>@endif
            @if ($suggestion->comment)<p class="song-comment">{{ $suggestion->comment }}</p>@endif
        </article>
        @empty
        <p class="song-empty">Nie ma jeszcze żadnych propozycji.</p>
        @endforelse
        @if ($suggestions->hasPages())
        <nav class="pagination" aria-label="Strony zgłoszeń">
            @if ($suggestions->previousPageUrl())<a href="{{ $suggestions->previousPageUrl() }}">Poprzednia</a>@endif
            <span>Strona {{ $suggestions->currentPage() }} z {{ $suggestions->lastPage() }}</span>
            @if ($suggestions->nextPageUrl())<a href="{{ $suggestions->nextPageUrl() }}">Następna</a>@endif
        </nav>
        @endif
    </main>
</body>

</html>