<?php

namespace App\Http\Controllers;

use App\Models\SongSuggestion;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;

class AdminSongSuggestionController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        $settings = WeddingSettings::query()->findOrFail(WeddingSettings::ID);
        abort_unless($request->user()->id === $settings->admin_user_id, 403);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $suggestions = SongSuggestion::query()->orderByDesc('created_at')->orderByDesc('id')->paginate(25);

        return view('admin.songs', compact('suggestions'));
    }

    public function export(Request $request)
    {
        $this->authorizeAdmin($request);

        return response()->streamDownload(function () {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, ['Tytuł', 'Wykonawca', 'Imię', 'Dedykacja lub komentarz', 'Data zgłoszenia (Europe/Warsaw)'], ',', '"', '');

            foreach (SongSuggestion::query()->orderByDesc('created_at')->orderByDesc('id')->cursor() as $suggestion) {
                fputcsv($output, [
                    $this->csvValue($suggestion->title),
                    $this->csvValue($suggestion->artist),
                    $this->csvValue($suggestion->guest_name),
                    $this->csvValue($suggestion->comment),
                    $suggestion->created_at->setTimezone(config('wedding.timezone'))->format('Y-m-d H:i:s'),
                ], ',', '"', '');
            }
            fclose($output);
        }, 'propozycje-piosenek.csv', ['Content-Type' => 'text/csv; charset=UTF-8', 'Cache-Control' => 'private, no-store']);
    }

    private function csvValue(?string $value): string
    {
        $value ??= '';

        return preg_match('/^[\s\p{C}\x{FEFF}]*[=+@-]|^[\t\r\n]/u', $value) ? "'".$value : $value;
    }
}
