<?php

namespace App\Http\Controllers;

use App\Models\AbsenceOption;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminController extends Controller
{
    public function index(Request $request): View
    {
        return view('admin.index', [
            'currentUser' => User::query()->with('manager')->find($request->session()->get('current_user_id')),
            'users' => User::query()->with(['department', 'manager'])->orderBy('name')->get(),
            'absenceOptions' => AbsenceOption::query()->orderBy('sort_order')->orderBy('label')->get(),
            'applicationName' => Setting::valueFor('app_name', config('app.name')),
        ]);
    }

    public function updateApplicationName(Request $request): RedirectResponse
    {
        $request->merge([
            'app_name' => trim((string) $request->input('app_name')),
        ]);

        $data = $request->validate([
            'app_name' => ['required', 'string', 'max:80'],
        ]);

        Setting::query()->updateOrCreate(
            ['key' => 'app_name'],
            ['value' => trim($data['app_name'])]
        );

        return redirect()
            ->route('admin.index')
            ->with('status', 'Application name updated.');
    }

    public function impersonate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'user_id' => ['required', Rule::exists('users', 'id')],
        ]);

        $request->session()->put('current_user_id', (int) $data['user_id']);

        $name = User::query()->whereKey($data['user_id'])->value('name');

        return redirect()
            ->route('admin.index')
            ->with('status', sprintf('You are now impersonating %s.', $name));
    }

    public function storeAbsenceOption(Request $request): RedirectResponse
    {
        $request->merge([
            'code' => Str::upper(trim((string) $request->input('code'))),
        ]);

        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', 'alpha_dash', Rule::unique('absence_options', 'code')],
            'label' => ['required', 'string', 'max:100'],
            'color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        AbsenceOption::query()->create([
            'code' => $data['code'],
            'label' => $data['label'],
            'color' => $data['color'],
            'sort_order' => (int) AbsenceOption::query()->max('sort_order') + 1,
        ]);

        return redirect()
            ->route('admin.index')
            ->with('status', sprintf('Absence option %s was added.', $data['label']));
    }
}
