<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Usuń wydarzenie</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell narrow">
        <h1>Usunąć wydarzenie?</h1>
        <p class="timeline-confirm"><strong>{{ $event->title }}</strong><br>{{ $event->event_date->locale('pl')->translatedFormat('j F Y') }}</p>
        <form method="post" action="{{ route('admin.timeline.destroy', $event->id) }}">
            @csrf
            @method('DELETE')
            <button type="submit">Usuń wydarzenie</button>
        </form>
        <a class="admin-home" href="{{ route('admin.timeline.index') }}">Anuluj</a>
    </main>
</body>

</html>