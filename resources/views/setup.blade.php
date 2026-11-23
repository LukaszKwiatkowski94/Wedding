<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Konfiguracja wesela</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell">
        <h1>Pierwsza konfiguracja</h1>
        @if ($errors->any())<div class="error-summary" role="alert">Sprawdź zaznaczone pola i spróbuj ponownie.</div>@endif
        <form method="post" action="{{ route('setup.store') }}">@csrf
            <fieldset>
                <legend>Konto administratora</legend>
                @foreach (['setup_secret' => 'Sekret instalacyjny', 'admin_name' => 'Imię administratora', 'email' => 'Adres e-mail', 'password' => 'Hasło', 'password_confirmation' => 'Powtórz hasło'] as $name => $label)
                <label>{{ $label }}<input name="{{ $name }}" type="{{ in_array($name, ['setup_secret', 'password', 'password_confirmation']) ? 'password' : ($name === 'email' ? 'email' : 'text') }}" @if (!in_array($name, ['setup_secret', 'password' , 'password_confirmation' ])) value="{{ old($name) }}" @endif required>@error($name)<span class="field-error">{{ $message }}</span>@enderror</label>
                @endforeach
            </fieldset>
            @include('partials.wedding-fields')
            <button type="submit">Zakończ konfigurację</button>
        </form>
    </main>
</body>

</html>