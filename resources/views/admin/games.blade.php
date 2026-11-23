<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Propozycje zabaw</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell">
        <div class="admin-header">
            <div>
                <h1>Propozycje zabaw</h1>
                <a class="admin-home" href="{{ route('settings.edit') }}">Wróć do ustawień</a>
            </div>
        </div>
        @if (session('status'))<p class="success" role="status">{{ session('status') }}</p>@endif
        <p class="admin-count">Zgłoszenia: {{ $suggestions->total() }}</p>
        @forelse ($suggestions as $suggestion)
        <article class="song-entry">
            <div class="song-entry-heading">
                <h2>{{ $suggestion->name }}</h2>
                <time datetime="{{ $suggestion->created_at->toIso8601String() }}">{{ $suggestion->created_at->setTimezone(config('wedding.timezone'))->format('d.m.Y H:i') }}</time>
            </div>
            <p class="game-status">{{ $suggestion->published_at ? 'Opublikowana' : 'Nieopublikowana' }}</p>
            @if ($suggestion->guest_name)<p class="song-byline">Od: {{ $suggestion->guest_name }}</p>@endif
            <p class="song-comment">{{ $suggestion->description }}</p>
            <form class="game-moderation" method="post" action="{{ $suggestion->published_at ? route('admin.games.unpublish', $suggestion->id) : route('admin.games.publish', $suggestion->id) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="page" value="{{ $suggestions->currentPage() }}">
                <button class="{{ $suggestion->published_at ? 'secondary' : '' }}" type="submit">{{ $suggestion->published_at ? 'Cofnij publikację' : 'Opublikuj' }}</button>
            </form>
        </article>
        @empty
        <p class="song-empty">Nie ma jeszcze żadnych propozycji zabaw.</p>
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