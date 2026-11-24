<?php

namespace Tests\Feature;

use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\User;
use App\Models\WeddingSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuItemsTest extends TestCase
{
    use RefreshDatabase;

    private function configureWedding(): WeddingSettings
    {
        return WeddingSettings::create([
            'id' => WeddingSettings::ID,
            'admin_user_id' => User::factory()->create()->id,
            'first_name' => 'Anna',
            'second_name' => 'Jan',
            'ceremony_at' => '2027-06-26 14:30:00',
            'ceremony_venue' => 'Ceremonia',
            'ceremony_city' => 'Miasto',
            'reception_at' => '2027-06-26 16:00:00',
            'reception_venue' => 'Przyjęcie',
            'reception_city' => 'Miasto',
            'songs_close_at' => '2027-06-20 21:00:00',
            'games_close_at' => '2027-06-19 21:00:00',
        ]);
    }

    private function admin(WeddingSettings $settings): User
    {
        return User::findOrFail($settings->admin_user_id);
    }

    public function test_admin_can_create_edit_move_and_delete_categories_and_items(): void
    {
        $this->withoutVite();
        $admin = $this->admin($this->configureWedding());

        $this->actingAs($admin)->post('/admin/menu/categories', ['name' => 'Dania główne'])->assertRedirect('/admin/menu');
        $category = MenuCategory::query()->firstOrFail();
        $this->actingAs($admin)->post('/admin/menu/items', [
            'menu_category_id' => $category->id,
            'name' => 'Pierogi',
            'description' => 'Z mięsem',
        ])->assertRedirect('/admin/menu');

        $item = MenuItem::query()->firstOrFail();
        $this->actingAs($admin)->put('/admin/menu/categories/'.$category->id, ['name' => 'Obiad'])->assertRedirect('/admin/menu');
        $this->actingAs($admin)->put('/admin/menu/items/'.$item->id, [
            'menu_category_id' => $category->id,
            'name' => 'Rosół',
            'description' => '',
        ])->assertRedirect('/admin/menu');

        $this->assertDatabaseHas('menu_categories', ['id' => $category->id, 'name' => 'Obiad']);
        $this->assertDatabaseHas('menu_items', ['id' => $item->id, 'name' => 'Rosół', 'description' => null]);

        $this->actingAs($admin)->delete('/admin/menu/items/'.$item->id)->assertRedirect('/admin/menu');
        $this->actingAs($admin)->delete('/admin/menu/categories/'.$category->id)->assertRedirect('/admin/menu');
        $this->assertDatabaseCount('menu_items', 0);
        $this->assertDatabaseCount('menu_categories', 0);
    }

    public function test_category_and_item_order_can_be_changed(): void
    {
        $admin = $this->admin($this->configureWedding());
        $first = MenuCategory::create(['name' => 'Pierwsza', 'position' => 1]);
        $second = MenuCategory::create(['name' => 'Druga', 'position' => 2]);
        $itemFirst = MenuItem::create(['menu_category_id' => $first->id, 'name' => 'Danie A', 'position' => 1]);
        $itemSecond = MenuItem::create(['menu_category_id' => $first->id, 'name' => 'Danie B', 'position' => 2]);

        $this->actingAs($admin)->patch('/admin/menu/categories/'.$second->id.'/up')->assertRedirect('/admin/menu');
        $this->assertSame(1, $second->fresh()->position);
        $this->assertSame(2, $first->fresh()->position);

        $this->actingAs($admin)->patch('/admin/menu/items/'.$itemSecond->id.'/up')->assertRedirect('/admin/menu');
        $this->assertSame(1, $itemSecond->fresh()->position);
        $this->assertSame(2, $itemFirst->fresh()->position);
    }

    public function test_guest_and_other_user_cannot_manage_menu(): void
    {
        $this->configureWedding();

        $this->get('/admin/menu')->assertRedirect('/login');
        $this->post('/admin/menu/categories', ['name' => 'X'])->assertRedirect('/login');

        $other = User::factory()->create();
        $this->actingAs($other)->get('/admin/menu')->assertForbidden();
        $this->actingAs($other)->post('/admin/menu/categories', ['name' => 'X'])->assertForbidden();
    }

    public function test_validation_rejects_empty_names_and_invalid_category(): void
    {
        $admin = $this->admin($this->configureWedding());

        $this->actingAs($admin)->from('/admin/menu')->post('/admin/menu/categories', ['name' => ''])->assertRedirect('/admin/menu')->assertSessionHasErrors('name');
        $this->actingAs($admin)->post('/admin/menu/items', [
            'menu_category_id' => 999,
            'name' => '',
            'description' => str_repeat('a', 2001),
        ], ['Accept' => 'text/html'])->assertSessionHasErrors(['menu_category_id', 'name', 'description']);

        $this->assertDatabaseCount('menu_categories', 0);
        $this->assertDatabaseCount('menu_items', 0);
    }

    public function test_unknown_category_and_item_ids_return_not_found(): void
    {
        $admin = $this->admin($this->configureWedding());

        $this->actingAs($admin)->put('/admin/menu/categories/999', ['name' => 'X'])->assertNotFound();
        $this->actingAs($admin)->delete('/admin/menu/categories/999')->assertNotFound();
        $this->actingAs($admin)->put('/admin/menu/items/999', ['menu_category_id' => 1, 'name' => 'X'])->assertNotFound();
        $this->actingAs($admin)->delete('/admin/menu/items/999')->assertNotFound();
    }

    public function test_public_menu_has_empty_state_and_preserves_category_and_item_order(): void
    {
        $this->withoutVite();
        $this->configureWedding();

        $this->get('/')->assertSee('Menu pojawi się tutaj później');

        $first = MenuCategory::create(['name' => 'Pierwsze dania', 'position' => 1]);
        $second = MenuCategory::create(['name' => 'Drugie dania', 'position' => 2]);
        MenuItem::create(['menu_category_id' => $second->id, 'name' => 'Kluski', 'position' => 1]);
        MenuItem::create(['menu_category_id' => $first->id, 'name' => 'Rosół', 'description' => 'Tradycyjny', 'position' => 1]);

        $this->get('/')->assertOk()->assertSeeInOrder([
            'Pierwsze dania',
            'Rosół',
            'Drugie dania',
            'Kluski',
        ], false);
    }

    public function test_public_menu_escapes_names_and_descriptions(): void
    {
        $this->withoutVite();
        $this->configureWedding();
        $category = MenuCategory::create(['name' => '<b>Przystawki</b>', 'position' => 1]);
        MenuItem::create([
            'menu_category_id' => $category->id,
            'name' => '<script>Zupa</script>',
            'description' => '<i>Opis</i>',
            'position' => 1,
        ]);

        $this->get('/')->assertDontSee('<script>Zupa</script>', false)
            ->assertSee('&lt;script&gt;Zupa&lt;/script&gt;', false)
            ->assertSee('&lt;i&gt;Opis&lt;/i&gt;', false)
            ->assertSee('&lt;b&gt;Przystawki&lt;/b&gt;', false);
    }

    public function test_deleting_category_cascades_to_items_and_reindexes_categories(): void
    {
        $admin = $this->admin($this->configureWedding());
        $first = MenuCategory::create(['name' => 'Pierwsza', 'position' => 1]);
        $second = MenuCategory::create(['name' => 'Druga', 'position' => 2]);
        $item = MenuItem::create(['menu_category_id' => $first->id, 'name' => 'Danie', 'position' => 1]);

        $this->actingAs($admin)->delete('/admin/menu/categories/'.$first->id)->assertRedirect('/admin/menu');

        $this->assertDatabaseMissing('menu_items', ['id' => $item->id]);
        $this->assertSame(1, $second->fresh()->position);
    }
}
