<?php

namespace App\Http\Controllers;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\WeddingSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminMenuController extends Controller
{
    private function authorizeAdmin(Request $request): void
    {
        $settings = WeddingSettings::query()->findOrFail(WeddingSettings::ID);
        abort_unless($request->user()->id === $settings->admin_user_id, 403);
    }

    private function categoryRules(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ], ['required' => 'To pole jest wymagane.', 'max' => 'Nazwa może mieć maksymalnie :max znaków.']);
    }

    private function itemRules(Request $request): array
    {
        return $request->validate([
            'menu_category_id' => ['required', 'integer', 'exists:menu_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
        ], ['required' => 'To pole jest wymagane.', 'max' => 'Przekroczono dopuszczalną liczbę znaków (:max).']);
    }

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);
        $categories = MenuCategory::query()->with(['items'])->orderBy('position')->orderBy('id')->get();

        return view('admin.menu.index', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $this->categoryRules($request);
        $data['position'] = (int) (MenuCategory::query()->max('position') ?? 0) + 1;
        MenuCategory::create($data);

        return redirect()->route('admin.menu.index')->with('status', 'Kategoria została dodana.');
    }

    public function updateCategory(Request $request, int $category)
    {
        $this->authorizeAdmin($request);
        MenuCategory::query()->findOrFail($category)->update($this->categoryRules($request));

        return redirect()->route('admin.menu.index')->with('status', 'Kategoria została zapisana.');
    }

    public function destroyCategory(Request $request, int $category)
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($category) {
            $record = MenuCategory::query()->lockForUpdate()->findOrFail($category);
            $position = $record->position;
            $record->delete();
            MenuCategory::query()->where('position', '>', $position)->lockForUpdate()->decrement('position');
        });

        return redirect()->route('admin.menu.index')->with('status', 'Kategoria została usunięta.');
    }

    public function moveCategory(Request $request, int $category, string $direction)
    {
        $this->authorizeAdmin($request);
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        DB::transaction(function () use ($category, $direction) {
            $record = MenuCategory::query()->lockForUpdate()->findOrFail($category);
            $targetPosition = $direction === 'up' ? $record->position - 1 : $record->position + 1;
            $target = MenuCategory::query()->where('position', $targetPosition)->lockForUpdate()->first();
            if (! $target) {
                return;
            }
            $target->update(['position' => $record->position]);
            $record->update(['position' => $targetPosition]);
        });

        return redirect()->route('admin.menu.index');
    }

    public function storeItem(Request $request)
    {
        $this->authorizeAdmin($request);
        $data = $this->itemRules($request);
        $data['description'] = $data['description'] ?: null;
        $data['position'] = (int) (MenuItem::query()->where('menu_category_id', $data['menu_category_id'])->max('position') ?? 0) + 1;
        MenuItem::create($data);

        return redirect()->route('admin.menu.index')->with('status', 'Pozycja została dodana.');
    }

    public function updateItem(Request $request, int $item)
    {
        $this->authorizeAdmin($request);
        $record = MenuItem::query()->findOrFail($item);
        $data = $this->itemRules($request);
        $data['description'] = $data['description'] ?: null;
        $record->update($data);

        return redirect()->route('admin.menu.index')->with('status', 'Pozycja została zapisana.');
    }

    public function destroyItem(Request $request, int $item)
    {
        $this->authorizeAdmin($request);

        DB::transaction(function () use ($item) {
            $record = MenuItem::query()->lockForUpdate()->findOrFail($item);
            $categoryId = $record->menu_category_id;
            $position = $record->position;
            $record->delete();
            MenuItem::query()->where('menu_category_id', $categoryId)->where('position', '>', $position)->lockForUpdate()->decrement('position');
        });

        return redirect()->route('admin.menu.index')->with('status', 'Pozycja została usunięta.');
    }

    public function moveItem(Request $request, int $item, string $direction)
    {
        $this->authorizeAdmin($request);
        abort_unless(in_array($direction, ['up', 'down'], true), 404);

        DB::transaction(function () use ($item, $direction) {
            $record = MenuItem::query()->lockForUpdate()->findOrFail($item);
            $targetPosition = $direction === 'up' ? $record->position - 1 : $record->position + 1;
            $target = MenuItem::query()->where('menu_category_id', $record->menu_category_id)->where('position', $targetPosition)->lockForUpdate()->first();
            if (! $target) {
                return;
            }
            $target->update(['position' => $record->position]);
            $record->update(['position' => $targetPosition]);
        });

        return redirect()->route('admin.menu.index');
    }
}
