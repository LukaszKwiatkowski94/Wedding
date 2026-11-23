<!doctype html>
<html lang="pl">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event ? 'Edytuj wydarzenie' : 'Nowe wydarzenie' }}</title>@vite('resources/css/app.css')
</head>

<body>
    <main class="admin-shell">
        <h1>{{ $event ? 'Edytuj wydarzenie' : 'Nowe wydarzenie' }}</h1>
        <a class="admin-home" href="{{ route('admin.timeline.index') }}">Wróć do historii pary</a>
        @if ($errors->any())<div class="error-summary" role="alert">Sprawdź zaznaczone pola i spróbuj ponownie.</div>@endif
        <form method="post" action="{{ $event ? route('admin.timeline.update', $event->id) : route('admin.timeline.store') }}">
            @csrf
            @if ($event) @method('PUT') @endif
            <label for="timeline-date">Data<input id="timeline-date" name="event_date" type="date" value="{{ old('event_date', $event?->event_date?->format('Y-m-d')) }}" required @error('event_date') aria-invalid="true" aria-describedby="timeline-date-error" @enderror>@error('event_date')<span class="field-error" id="timeline-date-error">{{ $message }}</span>@enderror</label>
            <label for="timeline-title">Tytuł<input id="timeline-title" name="title" value="{{ old('title', $event?->title) }}" maxlength="255" required @error('title') aria-invalid="true" aria-describedby="timeline-title-error" @enderror>@error('title')<span class="field-error" id="timeline-title-error">{{ $message }}</span>@enderror</label>
            <label for="timeline-description">Opis <span>(opcjonalnie)</span><textarea id="timeline-description" name="description" rows="5" maxlength="2000" @error('description') aria-invalid="true" aria-describedby="timeline-description-error" @enderror>{{ old('description', $event?->description) }}</textarea>@error('description')<span class="field-error" id="timeline-description-error">{{ $message }}</span>@enderror</label>
            <button type="submit">Zapisz wydarzenie</button>
        </form>
    </main>
</body>

</html>