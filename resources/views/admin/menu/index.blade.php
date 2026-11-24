<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menu weselne</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell">
        <div class="admin-header">
            <div>
                <h1>Menu weselne</h1>
                <a class="admin-home" href="{{ route('settings.edit') }}">Wróć do ustawień</a>
            </div>
        </div>
        @if (session('status'))<p class="success" role="status">{{ session('status') }}</p>@endif

        <section class="menu-admin-section">
            <h2>Dodaj kategorię</h2>
            <form method="post" action="{{ route('admin.menu.categories.store') }}">
                @csrf
                <label>Nazwa kategorii<input name="name" value="{{ old('name') }}" maxlength="255" required>@error('name')<span class="field-error">{{ $message }}</span>@enderror</label>
                <button type="submit">Dodaj kategorię</button>
            </form>
        </section>

        @forelse ($categories as $category)
        <section class="menu-admin-category">
            <form class="menu-category-edit" method="post" action="{{ route('admin.menu.categories.update', $category->id) }}">
                @csrf @method('PUT')
                <label>Nazwa kategorii<input name="name" value="{{ $category->name }}" maxlength="255" required></label>
                <button type="submit" class="secondary">Zapisz nazwę</button>
            </form>
            <div class="menu-category-actions">
                <form method="post" action="{{ route('admin.menu.categories.move', [$category->id, 'up']) }}">@csrf @method('PATCH')<button type="submit" class="secondary">W górę</button></form>
                <form method="post" action="{{ route('admin.menu.categories.move', [$category->id, 'down']) }}">@csrf @method('PATCH')<button type="submit" class="secondary">W dół</button></form>
                <form method="post" action="{{ route('admin.menu.categories.destroy', $category->id) }}" onsubmit="return confirm('Usunąć kategorię wraz ze wszystkimi pozycjami?')">@csrf @method('DELETE')<button type="submit" class="secondary">Usuń kategorię</button></form>
            </div>

            @foreach ($category->items as $item)
            <article class="menu-admin-item">
                <form method="post" action="{{ route('admin.menu.items.update', $item->id) }}">
                    @csrf @method('PUT')
                    <input type="hidden" name="menu_category_id" value="{{ $category->id }}">
                    <label>Nazwa dania<input name="name" value="{{ $item->name }}" maxlength="255" required></label>
                    <label>Opis <span>(opcjonalnie)</span><textarea name="description" rows="3" maxlength="2000">{{ $item->description }}</textarea></label>
                    <div class="menu-item-actions">
                        <button type="submit">Zapisz</button>
                        @if (! $loop->first)<button type="submit" class="secondary" form="move-item-up-{{ $item->id }}">W górę</button>@endif
                        @if (! $loop->last)<button type="submit" class="secondary" form="move-item-down-{{ $item->id }}">W dół</button>@endif
                    </div>
                </form>
                @if (! $loop->first)<form id="move-item-up-{{ $item->id }}" method="post" action="{{ route('admin.menu.items.move', [$item->id, 'up']) }}">@csrf @method('PATCH')</form>@endif
                @if (! $loop->last)<form id="move-item-down-{{ $item->id }}" method="post" action="{{ route('admin.menu.items.move', [$item->id, 'down']) }}">@csrf @method('PATCH')</form>@endif
                <form method="post" action="{{ route('admin.menu.items.destroy', $item->id) }}" onsubmit="return confirm('Usunąć tę pozycję?')">@csrf @method('DELETE')<button type="submit" class="secondary">Usuń pozycję</button></form>
            </article>
            @endforeach

            <form class="menu-item-add" method="post" action="{{ route('admin.menu.items.store') }}">
                @csrf
                <input type="hidden" name="menu_category_id" value="{{ $category->id }}">
                <label>Nazwa dania<input name="name" maxlength="255" required></label>
                <label>Opis <span>(opcjonalnie)</span><textarea name="description" rows="3" maxlength="2000"></textarea></label>
                <button type="submit">Dodaj pozycję</button>
            </form>
        </section>
        @empty
        <p class="song-empty">Nie ma jeszcze kategorii menu.</p>
        @endforelse
    </main>
</body>

</html>