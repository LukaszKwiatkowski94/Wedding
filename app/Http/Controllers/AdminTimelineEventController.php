<?php

namespace App\Http\Controllers;

use App\Models\TimelineEvent;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;

class AdminTimelineEventController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        $settings = WeddingSettings::query()->findOrFail(WeddingSettings::ID);
        abort_unless($request->user()->id === $settings->admin_user_id, 403);
    }

    private function find(int $id): TimelineEvent
    {
        return TimelineEvent::query()->findOrFail($id);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'event_date' => ['required', 'date_format:Y-m-d'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], [
            'required' => 'To pole jest wymagane.',
            'date_format' => 'Podaj datę w formacie RRRR-MM-DD.',
            'string' => 'Podaj tekst.',
            'max' => 'Przekroczono dopuszczalną liczbę znaków (:max).',
        ]);

        $data['description'] = $data['description'] ?: null;

        return $data;
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $events = TimelineEvent::query()->orderBy('event_date')->orderBy('id')->paginate(25);

        return view('admin.timeline.index', compact('events'));
    }

    public function create(Request $request)
    {
        $this->authorizeAdmin($request);

        return view('admin.timeline.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin($request);
        TimelineEvent::create($this->validated($request));

        return redirect()->route('admin.timeline.index')->with('status', 'Wydarzenie zostało dodane.');
    }

    public function edit(Request $request, int $event)
    {
        $this->authorizeAdmin($request);

        return view('admin.timeline.form', ['event' => $this->find($event)]);
    }

    public function update(Request $request, int $event)
    {
        $this->authorizeAdmin($request);
        $this->find($event)->update($this->validated($request));

        return redirect()->route('admin.timeline.index')->with('status', 'Wydarzenie zostało zapisane.');
    }

    public function confirmDelete(Request $request, int $event)
    {
        $this->authorizeAdmin($request);

        return view('admin.timeline.delete', ['event' => $this->find($event)]);
    }

    public function destroy(Request $request, int $event)
    {
        $this->authorizeAdmin($request);
        $this->find($event)->delete();

        return redirect()->route('admin.timeline.index')->with('status', 'Wydarzenie zostało usunięte.');
    }
}
