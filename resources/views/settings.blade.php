<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ustawienia wesela</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell">
        <div class="admin-header">
            <div>
                <h1>Ustawienia wesela</h1>
                <a class="admin-home" href="{{ route('home') }}">Zobacz stronę wesela</a>
                <a class="admin-home" href="{{ route('admin.songs.index') }}">Propozycje piosenek</a>
                <a class="admin-home" href="{{ route('admin.games.index') }}">Propozycje zabaw</a>
            </div>
            <form action="{{ route('logout') }}" method="post">@csrf<button class="secondary" type="submit">Wyloguj</button></form>
        </div>
        @if (session('status'))<p class="success" role="status">{{ session('status') }}</p>@endif
        @if ($errors->any())<div class="error-summary" role="alert">Sprawdź zaznaczone pola i spróbuj ponownie.</div>@endif
        <form method="post" action="{{ route('settings.update') }}">@csrf @method('PUT') @include('partials.wedding-fields')<button type="submit">Zapisz ustawienia</button></form>
    </main>
</body>

</html>