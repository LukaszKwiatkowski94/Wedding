<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Logowanie</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell narrow">
        <h1>Logowanie</h1>
        <form method="post" action="{{ route('login.store') }}">@csrf
            <label>Adres e-mail<input type="email" name="email" value="{{ old('email') }}" autocomplete="username" required>@error('email')<span class="field-error">{{ $message }}</span>@enderror</label>
            <label>Hasło<input type="password" name="password" autocomplete="current-password" required>@error('password')<span class="field-error">{{ $message }}</span>@enderror</label>
            <button type="submit">Zaloguj</button>
        </form>
    </main>
</body>

</html>