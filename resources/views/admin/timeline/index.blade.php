<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Historia pary</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell">
        <div class="admin-header">
            <div>
                <h1>Historia pary</h1>
                <a class="admin-home" href="{{ route('settings.edit') }}">Wróć do ustawień</a>
            </div>
            <a class="admin-action" href="{{ route('admin.timeline.create') }}">Dodaj wydarzenie</a>
        </div>
        @if (session('status'))<p class="success" role="status">{{ session('status') }}</p>@endif
        <p class="admin-count">Wydarzenia: {{ $events->total() }}. Ślub jest dodawany na osi automatycznie z ustawień.</p>
        @forelse ($events as $event)
        <article class="song-entry">
            <div class="song-entry-heading">
                <h2>{{ $event->title }}</h2>
                <time datetime="{{ $event->event_date->format('Y-m-d') }}">{{ $event->event_date->locale('pl')->translatedFormat('j F Y') }}</time>
            </div>
            @if ($event->description)<p class="song-comment">{{ $event->description }}</p>@endif
            <p class="timeline-actions"><a href="{{ route('admin.timeline.edit', $event->id) }}">Edytuj</a><a href="{{ route('admin.timeline.delete', $event->id) }}">Usuń</a></p>
        </article>
        @empty
        <p class="song-empty">Nie ma jeszcze wydarzeń. Na stronie widoczny jest tylko ślub.</p>
        @endforelse
        @if ($events->hasPages())
        <nav class="pagination" aria-label="Strony wydarzeń">
            @if ($events->previousPageUrl())<a href="{{ $events->previousPageUrl() }}">Poprzednia</a>@endif
            <span>Strona {{ $events->currentPage() }} z {{ $events->lastPage() }}</span>
            @if ($events->nextPageUrl())<a href="{{ $events->nextPageUrl() }}">Następna</a>@endif
        </nav>
        @endif
    </main>
</body>

</html>