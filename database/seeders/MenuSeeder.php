<?php

namespace Database\Seeders;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        if (MenuCategory::query()->exists()) {
            return;
        }

        $categories = [
            'Przystawki' => [
                ['name' => 'Szwedzki stół', 'description' => 'Zestaw zimnych przekąsek i sałat'],
                ['name' => 'Rosół z kury', 'description' => 'Makaron, warzywa i szczypiorek'],
            ],
            'Zupy' => [
                ['name' => 'Żurek', 'description' => 'Serwowany z kiełbasą i jajkiem'],
            ],
            'Dania główne' => [
                ['name' => 'Schab z piekarnika', 'description' => 'Ziemniaki i sos własny'],
                ['name' => 'Ryba pieczona', 'description' => 'Warzywa sezonowe i cytryna'],
            ],
            'Desery' => [
                ['name' => 'Szarlotka', 'description' => 'Podawana z lodami waniliowymi'],
                ['name' => 'Mini bezy', 'description' => 'Z kremem i świeżymi owocami'],
            ],
            'Tort weselny' => [
                ['name' => 'Tort weselny', 'description' => 'Dwukolorowy tort z owocami sezonowymi'],
            ],
            'Napoje' => [
                ['name' => 'Woda i soki', 'description' => 'Woda niegazowana i gazowana oraz wybór soków'],
                ['name' => 'Herbata i kawa', 'description' => 'Serwowane po daniach'],
            ],
        ];

        $categoryPosition = 1;

        foreach ($categories as $categoryName => $items) {
            $category = MenuCategory::create([
                'name' => $categoryName,
                'position' => $categoryPosition++,
            ]);

            foreach ($items as $itemPosition => $item) {
                MenuItem::create([
                    'menu_category_id' => $category->id,
                    'name' => $item['name'],
                    'description' => $item['description'],
                    'position' => $itemPosition + 1,
                ]);
            }
        }
    }
}
