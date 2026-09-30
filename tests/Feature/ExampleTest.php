<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_homepage_returns_welcome(): void
    {
        $this->get('/')->assertStatus(200);
    }

    public function test_homepage_redirects_login_user_to_dashboard(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/')->assertRedirect('/dashboard');
    }

    public function test_login_redirects_to_dashboard(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertRedirect('/dashboard');
    }

    public function test_preview_always_shows_welcome(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/preview')->assertStatus(200);
    }

    public function test_dashboard_redirect_guest_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_asset_masuk_requires_login(): void
    {
        $this->get('/transactions/asset/masuk')->assertRedirect('/login');
    }

    public function test_asset_masuk_store_creates_asset(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->post('/transactions/asset/masuk', [
            'procurement_year' => 2026,
            'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0001',
            'name' => 'Laptop Test',
            'acquisition_value' => 5000000,
            'funding_source' => 'BOS',
        ])->assertRedirect('/transactions/asset/masuk');
        $this->assertDatabaseHas('assets', ['asset_code' => 'AST-2026-KIBB-0001', 'funding_source' => 'BOS']);
    }

    public function test_building_and_room_crud_linked(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->post('/buildings', ['code' => 'GDG-A', 'name' => 'Gedung A'])->assertRedirect('/buildings');
        $building = \App\Models\Building::where('code', 'GDG-A')->firstOrFail();
        $this->actingAs($user)->post('/locations', [
            'code' => 'R-A-01', 'name' => 'Ruang 01', 'building_id' => $building->id,
        ])->assertRedirect('/locations');
        $this->assertDatabaseHas('locations', ['code' => 'R-A-01', 'building_id' => $building->id]);
        $this->actingAs($user)->get('/locations/create')->assertSee('Gedung A');
    }
}

