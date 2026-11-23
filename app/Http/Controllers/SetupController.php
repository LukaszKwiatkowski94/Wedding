<?php

namespace App\Http\Controllers;

use App\Http\Requests\WeddingData;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SetupController extends Controller
{
    private function ensureAvailable(): void
    {
        abort_if(WeddingSettings::query()->exists() || User::query()->exists(), 404);
        abort_unless(is_string(config('wedding.setup_secret')) && config('wedding.setup_secret') !== '', 503);
    }

    public function create()
    {
        $this->ensureAvailable();

        return view('setup');
    }

    public function store(Request $request)
    {
        $this->ensureAvailable();

        if (! is_string($request->input('setup_secret')) || ! hash_equals(config('wedding.setup_secret'), $request->input('setup_secret'))) {
            throw ValidationException::withMessages(['setup_secret' => 'Nieprawidłowy sekret instalacyjny.']);
        }

        $data = WeddingData::validate($request, [
            'admin_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
        ]);

        try {
            $user = DB::transaction(function () use ($data) {
                $settings = WeddingSettings::create(array_merge(Arr::except($data, ['admin_name', 'email', 'password']), ['id' => WeddingSettings::ID]));
                $user = User::create(['name' => $data['admin_name'], 'email' => $data['email'], 'password' => $data['password']]);
                $settings->update(['admin_user_id' => $user->id]);

                return $user;
            });
        } catch (QueryException $exception) {
            if (WeddingSettings::query()->exists() || User::query()->exists()) {
                abort(404);
            }

            throw $exception;
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('settings.edit');
    }
}
