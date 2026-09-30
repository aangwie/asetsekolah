<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;
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
            'acquisition_date' => '2026-03-15',
            'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0001',
            'name' => 'Laptop Test',
            'acquisition_value' => 5000000,
            'funding_source' => 'BOS',
        ])->assertRedirect('/transactions/asset/masuk/create');
        $this->assertDatabaseHas('assets', ['asset_code' => 'AST-2026-KIBB-0001', 'funding_source' => 'BOS', 'acquisition_date' => '2026-03-15', 'procurement_year' => 2026]);
    }

    public function test_asset_masuk_rejects_year_date_mismatch(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->post('/transactions/asset/masuk', [
            'procurement_year' => 2025,
            'acquisition_date' => '2026-03-15',
            'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0002',
            'name' => 'Laptop Beda Tahun',
            'acquisition_value' => 5000000,
            'funding_source' => 'BOS',
        ])->assertSessionHasErrors('acquisition_date');
        $this->assertDatabaseMissing('assets', ['asset_code' => 'AST-2026-KIBB-0002']);
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

    public function test_asset_masuk_proof_image_converted_to_webp(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $img = imagecreatetruecolor(10, 10);
        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);
        $tmp = tempnam(sys_get_temp_dir(), 't').'.png';
        file_put_contents($tmp, $png);
        $file = new \Illuminate\Http\UploadedFile($tmp, 'nota.png', 'image/png', null, true);
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->post('/transactions/asset/masuk', [
            'procurement_year' => 2026, 'acquisition_date' => '2026-04-01', 'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0003', 'name' => 'Meja', 'acquisition_value' => 100000,
            'funding_source' => 'BOS', 'proof' => $file,
        ])->assertRedirect('/transactions/asset/masuk/create');
        $asset = \App\Models\Asset::where('asset_code', 'AST-2026-KIBB-0003')->firstOrFail();
        $this->assertStringEndsWith('.webp', $asset->proof_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($asset->proof_path);
    }

    public function test_asset_masuk_proof_rejects_oversize(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        $file = \Illuminate\Http\UploadedFile::fake()->create('bast.pdf', 600, 'application/pdf');
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->post('/transactions/asset/masuk', [
            'procurement_year' => 2026, 'acquisition_date' => '2026-04-01', 'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0004', 'name' => 'Kursi', 'acquisition_value' => 100000,
            'funding_source' => 'BOS', 'proof' => $file,
        ])->assertSessionHasErrors('proof');
        $this->assertDatabaseMissing('assets', ['asset_code' => 'AST-2026-KIBB-0004']);
    }

    public function test_asset_masuk_form_shows_table_below(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/transactions/asset/masuk/create')
            ->assertOk()->assertSee('Hasil Input', false)->assertSee('assetMasukTable', false);
    }

    public function test_asset_masuk_rupiah_dots_stripped_and_lightbox_present(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/transactions/asset/masuk/create')->assertOk()
            ->assertSee('proofLightbox', false)->assertSee('acquisition_value', false)->assertSee('openProof', false);
        $this->actingAs($user)->post('/transactions/asset/masuk', [
            'procurement_year' => 2026, 'acquisition_date' => '2026-05-01', 'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0005', 'name' => 'Proyektor', 'acquisition_value' => '5.000.000',
            'funding_source' => 'BOS',
        ])->assertRedirect('/transactions/asset/masuk/create');
        $this->assertDatabaseHas('assets', ['asset_code' => 'AST-2026-KIBB-0005', 'acquisition_value' => 5000000]);
    }
    public function test_asset_masuk_edit_update_and_destroy(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->post('/transactions/asset/masuk', [
            'procurement_year' => 2026, 'acquisition_date' => '2026-05-01', 'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0091', 'name' => 'Lama', 'acquisition_value' => 1000000,
            'funding_source' => 'BOS',
        ])->assertRedirect('/transactions/asset/masuk/create');
        $asset = \App\Models\Asset::where('asset_code', 'AST-2026-KIBB-0091')->firstOrFail();
        $this->actingAs($user)->get('/transactions/asset/masuk/'.$asset->id.'/edit')->assertOk()->assertSee('Ubah Aset Masuk', false);
        $this->actingAs($user)->put('/transactions/asset/masuk/'.$asset->id, [
            'procurement_year' => 2026, 'acquisition_date' => '2026-05-02', 'kib_type' => 'B',
            'asset_code' => 'AST-2026-KIBB-0091', 'name' => 'Baru', 'acquisition_value' => '2.000.000',
            'funding_source' => 'DAK',
        ])->assertRedirect('/transactions/asset/masuk/create');
        $this->assertDatabaseHas('assets', ['asset_code' => 'AST-2026-KIBB-0091', 'name' => 'Baru', 'acquisition_value' => 2000000]);
        $this->actingAs($user)->delete('/transactions/asset/masuk/'.$asset->id)->assertRedirect();
        $this->assertDatabaseMissing('assets', ['asset_code' => 'AST-2026-KIBB-0091']);
    }

    public function test_asset_masuk_tables_show_edit_delete_and_sweetalert(): void
    {
        $user = \App\Models\User::factory()->create();
        $asset = \App\Models\Asset::create([
            'asset_code' => 'AST-2026-KIBB-0092', 'name' => 'Tabel', 'kib_type' => 'B',
            'acquisition_date' => '2026-05-01', 'acquisition_value' => 100000,
            'funding_source' => 'BOS',
        ]);
        foreach (['/transactions/asset/masuk/create', '/transactions/asset/masuk'] as $url) {
            $this->actingAs($user)->get($url)->assertOk()
                ->assertSee('fa-pencil', false)->assertSee('fa-trash', false)
                ->assertSee('sweetalert2', false)->assertSee('confirmDelete', false)
                ->assertSee('/transactions/asset/masuk/'.$asset->id.'/edit', false);
        }
    }
    public function test_asset_keluar_crud_sekolah_and_luar(): void
    {
        $user = \App\Models\User::factory()->create();
        $asset = \App\Models\Asset::create([
            'asset_code' => 'AST-2026-KIBB-0201', 'name' => 'Laptop Pinjam', 'kib_type' => 'B',
            'acquisition_date' => '2026-05-01', 'acquisition_value' => 8000000, 'funding_source' => 'BOS',
        ]);
        // requires login
        $this->get('/transactions/asset/keluar')->assertRedirect('/login');
        // form shows fields + toggle
        $this->actingAs($user)->get('/transactions/asset/keluar/create')->assertOk()
            ->assertSee('Nama Barang', false)->assertSee('Tanggal Keluar', false)
            ->assertSee('Luar Sekolah', false)->assertSee('location_type', false)
            ->assertSee('luarFields', false);
        // store Sekolah (tanpa PJ)
        $this->actingAs($user)->post('/transactions/asset/keluar', [
            'asset_id' => $asset->id, 'outflow_date' => '2026-09-01', 'location_type' => 'Sekolah',
        ])->assertRedirect('/transactions/asset/keluar/create');
        $this->assertDatabaseHas('asset_outflows', ['asset_id' => $asset->id, 'location_type' => 'Sekolah']);
        // store Luar wajib PJ + tgl pinjam
        $this->actingAs($user)->post('/transactions/asset/keluar', [
            'asset_id' => $asset->id, 'outflow_date' => '2026-09-02', 'location_type' => 'Luar Sekolah',
        ])->assertSessionHasErrors(['borrower_name', 'loan_date']);
        $this->actingAs($user)->post('/transactions/asset/keluar', [
            'asset_id' => $asset->id, 'outflow_date' => '2026-09-02', 'location_type' => 'Luar Sekolah',
            'borrower_name' => 'Budi', 'loan_date' => '2026-09-02',
        ])->assertRedirect('/transactions/asset/keluar/create');
        $o = \App\Models\AssetOutflow::where('borrower_name', 'Budi')->firstOrFail();
        // edit + update return_date
        $this->actingAs($user)->get('/transactions/asset/keluar/'.$o->id.'/edit')->assertOk()->assertSee('Ubah Aset Keluar', false);
        $this->actingAs($user)->put('/transactions/asset/keluar/'.$o->id, [
            'asset_id' => $asset->id, 'outflow_date' => '2026-09-02', 'location_type' => 'Luar Sekolah',
            'borrower_name' => 'Budi', 'loan_date' => '2026-09-02', 'return_date' => '2026-09-05',
        ])->assertRedirect('/transactions/asset/keluar/create');
        $this->assertDatabaseHas('asset_outflows', ['id' => $o->id, 'return_date' => '2026-09-05']);
        // index shows badge + actions
        $this->actingAs($user)->get('/transactions/asset/keluar')->assertOk()
            ->assertSee('Luar Sekolah', false)->assertSee('fa-pencil', false)->assertSee('sweetalert2', false);
        // destroy
        $this->actingAs($user)->delete('/transactions/asset/keluar/'.$o->id)->assertRedirect();
        $this->assertDatabaseMissing('asset_outflows', ['id' => $o->id]);
    }

    public function test_bhp_masuk_crud_stok_and_datatables(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->get('/transactions/bhp/masuk')->assertRedirect('/login');
        $this->actingAs($user)->get('/transactions/bhp/masuk/create')->assertOk()
            ->assertSee('Tahun Perolehan', false)->assertSee('Nama Barang', false)
            ->assertSee('ATK', false)->assertSee('Harga Satuan', false)
            ->assertSee('total_price', false)->assertSee('bhpMasukTable', false);
        // store: rupiah dots stripped
        $this->actingAs($user)->post('/transactions/bhp/masuk', [
            'procurement_year' => 2026, 'transaction_date' => '2026-06-01',
            'name' => 'Kertas A4', 'category' => 'ATK',
            'unit_price' => '5.000', 'quantity' => 10,
        ])->assertRedirect('/transactions/bhp/masuk/create');
        $item = \App\Models\BhpItem::where('name', 'Kertas A4')->firstOrFail();
        $this->assertEquals(10, (int) $item->refresh()->current_stock);
        $this->assertEquals(5000, (int) $item->unit_price);
        $tx = \App\Models\BhpTransaction::where('bhp_item_id', $item->id)->firstOrFail();
        // year-date mismatch rejected
        $this->actingAs($user)->post('/transactions/bhp/masuk', [
            'procurement_year' => 2025, 'transaction_date' => '2026-06-02',
            'name' => 'Tolak', 'category' => 'ATK', 'unit_price' => 1000, 'quantity' => 1,
        ])->assertSessionHasErrors('transaction_date');
        // update qty adjusts stock (+2)
        $this->actingAs($user)->get('/transactions/bhp/masuk/'.$tx->id.'/edit')->assertOk()->assertSee('Ubah BHP Masuk', false);
        $this->actingAs($user)->put('/transactions/bhp/masuk/'.$tx->id, [
            'procurement_year' => 2026, 'transaction_date' => '2026-06-01',
            'name' => 'Kertas A4', 'category' => 'Kebersihan',
            'unit_price' => '6.000', 'quantity' => 12,
        ])->assertRedirect('/transactions/bhp/masuk/create');
        $this->assertEquals(12, (int) $item->refresh()->current_stock);
        // index shows total 72.000 + datatables + actions
        $this->actingAs($user)->get('/transactions/bhp/masuk')->assertOk()
            ->assertSee('72.000', false)->assertSee('bhpMasukIndexTable', false)
            ->assertSee('fa-pencil', false)->assertSee('sweetalert2', false);
        // destroy rolls back stock
        $this->actingAs($user)->delete('/transactions/bhp/masuk/'.$tx->id)->assertRedirect();
        $this->assertEquals(0, (int) $item->refresh()->current_stock);
    }
    public function test_bhp_keluar_crud_stock_decrement(): void
    {
        $user = \App\Models\User::factory()->create();
        $loc = \App\Models\Location::create(['code' => 'R-BHPK-01', 'name' => 'Ruang Guru']);
        $item = \App\Models\BhpItem::create([
            'code' => 'BHP-2026-K01', 'name' => 'Sabun Cuci', 'category' => 'Kebersihan',
            'unit' => 'pcs', 'initial_stock' => 0, 'current_stock' => 10, 'unit_price' => 3000,
        ]);
        $this->get('/transactions/bhp/keluar')->assertRedirect('/login');
        $this->actingAs($user)->get('/transactions/bhp/keluar/create')->assertOk()
            ->assertSee('Nama BHP', false)->assertSee('Tanggal Pengambilan', false)
            ->assertSee('Jumlah Pengambilan', false)->assertSee('Digunakan di', false)
            ->assertSee('sisa_stock', false)->assertSee('Sabun Cuci', false)
            ->assertSee('bhpKeluarTable', false);
        $this->actingAs($user)->post('/transactions/bhp/keluar', [
            'bhp_item_id' => $item->id, 'transaction_date' => '2026-09-30',
            'quantity' => 11, 'location_id' => $loc->id,
        ])->assertSessionHasErrors('quantity');
        $this->actingAs($user)->post('/transactions/bhp/keluar', [
            'bhp_item_id' => $item->id, 'transaction_date' => '2026-09-30',
            'quantity' => 4, 'location_id' => $loc->id,
        ])->assertRedirect('/transactions/bhp/keluar/create');
        $this->assertEquals(6, (int) $item->refresh()->current_stock);
        $tx = \App\Models\BhpTransaction::where('bhp_item_id', $item->id)->where('type', 'out')->firstOrFail();
        $this->actingAs($user)->get('/transactions/bhp/keluar/'.$tx->id.'/edit')->assertOk()
            ->assertSee('Ubah BHP Keluar', false)->assertSee('(terkunci)', false);
        $this->actingAs($user)->put('/transactions/bhp/keluar/'.$tx->id, [
            'transaction_date' => '2026-09-30', 'quantity' => 11, 'location_id' => $loc->id,
        ])->assertSessionHasErrors('quantity');
        $this->actingAs($user)->put('/transactions/bhp/keluar/'.$tx->id, [
            'transaction_date' => '2026-09-30', 'quantity' => 6, 'location_id' => $loc->id,
        ])->assertRedirect('/transactions/bhp/keluar/create');
        $this->assertEquals(4, (int) $item->refresh()->current_stock);
        $this->actingAs($user)->get('/transactions/bhp/keluar')->assertOk()
            ->assertSee('Sabun Cuci', false)->assertSee('Ruang Guru', false)
            ->assertSee('bhpKeluarIndexTable', false)
            ->assertSee('fa-pencil', false)->assertSee('sweetalert2', false);
        $this->actingAs($user)->delete('/transactions/bhp/keluar/'.$tx->id)->assertRedirect();
        $this->assertEquals(10, (int) $item->refresh()->current_stock);
    }
    public function test_school_profile_menu_and_update_converts_logos_to_webp(): void
    {
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'web']);
        \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'pengelola_aset', 'guard_name' => 'web']);
        $admin = \App\Models\User::factory()->create();
        $admin->assignRole('superadmin');
        $this->get('/school-profile')->assertRedirect('/login');
        $this->actingAs($admin)->get('/dashboard')->assertOk()
            ->assertSee('Manajemen', false)->assertSee('Data Sekolah', false)->assertSee('Users', false);
        $this->actingAs($admin)->get('/school-profile')->assertOk()
            ->assertSee('Nama Sekolah', false)->assertSee('Logo Pemkab', false)->assertSee('Logo Sekolah', false);
        \Illuminate\Support\Facades\Storage::fake('public');
        $this->actingAs($admin)->put('/school-profile', [
            'school_name' => 'SMP 1', 'npsn' => '123', 'address' => 'Jl. A',
            'logo_pemkab' => \Illuminate\Http\UploadedFile::fake()->image('big.png', 100, 100)->size(600),
        ])->assertSessionHasErrors('logo_pemkab');
        $this->actingAs($admin)->put('/school-profile', [
            'school_name' => 'SMP Negeri 1', 'npsn' => '20123456', 'address' => 'Jl. Merdeka 1',
            'logo_pemkab' => \Illuminate\Http\UploadedFile::fake()->image('pemkab.png', 100, 100),
            'logo_sekolah' => \Illuminate\Http\UploadedFile::fake()->image('sekolah.jpg', 100, 100),
        ])->assertRedirect('/school-profile');
        $p = \App\Models\SchoolProfile::firstOrFail();
        $this->assertEquals('SMP Negeri 1', $p->school_name);
        $this->assertStringEndsWith('.webp', $p->logo_pemkab_path);
        $this->assertStringEndsWith('.webp', $p->logo_sekolah_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($p->logo_pemkab_path);
        \Illuminate\Support\Facades\Storage::disk('public')->assertExists($p->logo_sekolah_path);
        $old = $p->logo_pemkab_path;
        $this->actingAs($admin)->put('/school-profile', [
            'school_name' => 'SMP Negeri 1',
            'logo_pemkab' => \Illuminate\Http\UploadedFile::fake()->image('pemkab2.png', 100, 100),
        ])->assertRedirect('/school-profile');
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($old);
    }
    public function test_reports_nested_menu_and_filters(): void
    {
        $user = \App\Models\User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Laporan', false)->assertSee('Data Aset', false)->assertSee('Rekap Aset', false)
            ->assertSee('KIB A', false)->assertSee('KIB E', false)->assertSee('Mutasi Aset', false)
            ->assertSee('Laporan BHP', false);
        foreach (['/laporan/aset/data', '/laporan/aset/rekap', '/laporan/aset/kib/b', '/laporan/aset/mutasi', '/laporan/bhp'] as $url) {
            $this->actingAs($user)->get($url)->assertOk()
                ->assertSee('Filter Laporan', false)->assertSee('Mode Filter', false)
                ->assertSee('Tanggal Mulai', false)->assertSee('Tampilan', false);
        }
        \App\Models\Asset::create(['asset_code' => 'RPT-B-001', 'name' => 'Laptop RPT', 'kib_type' => 'B', 'acquisition_date' => '2026-03-15', 'acquisition_value' => 5000000]);
        \App\Models\Asset::create(['asset_code' => 'RPT-A-001', 'name' => 'Tanah RPT', 'kib_type' => 'A', 'acquisition_date' => '2025-05-10', 'acquisition_value' => 10000000]);
        $this->actingAs($user)->get('/laporan/aset/data?filter_mode=tahun&year=2026&view_type=lengkap')->assertOk()
            ->assertSee('Laptop RPT', false)->assertDontSee('Tanah RPT', false);
        $this->actingAs($user)->get('/laporan/aset/data?filter_mode=periode&start_date=2026-01-01&end_date=2026-12-31&view_type=rekap')->assertOk()
            ->assertSee('Total aset', false);
        $this->actingAs($user)->get('/laporan/aset/kib/b?filter_mode=tahun&year=2026&view_type=lengkap')->assertOk()
            ->assertSee('Laptop RPT', false)->assertDontSee('Tanah RPT', false);
        $this->actingAs($user)->get('/laporan/aset/rekap?filter_mode=tahun&year=2026&view_type=rekap')->assertOk()
            ->assertSee('Total aset', false);
        $asset = \App\Models\Asset::where('asset_code', 'RPT-B-001')->firstOrFail();
        \App\Models\AssetOutflow::create(['asset_id' => $asset->id, 'outflow_date' => '2026-06-01', 'location_type' => 'Sekolah']);
        $this->actingAs($user)->get('/laporan/aset/mutasi?filter_mode=tahun&year=2026&view_type=lengkap')->assertOk()
            ->assertSee('Laptop RPT', false);
        $this->actingAs($user)->get('/laporan/aset/mutasi?filter_mode=periode&start_date=2026-01-01&end_date=2026-12-31&view_type=rekap')->assertOk()
            ->assertSee('Total mutasi', false);
        $item = \App\Models\BhpItem::create(['code' => 'BHP-RPT-001', 'name' => 'Kertas RPT', 'category' => 'ATK', 'unit' => 'rim', 'initial_stock' => 0, 'current_stock' => 0]);
        \App\Models\BhpTransaction::create(['bhp_item_id' => $item->id, 'user_id' => $user->id, 'type' => 'in', 'quantity' => 10, 'transaction_date' => '2026-02-01']);
        $this->actingAs($user)->get('/laporan/bhp?filter_mode=tahun&year=2026&view_type=lengkap')->assertOk()
            ->assertSee('Kertas RPT', false);
        $this->actingAs($user)->get('/laporan/bhp?filter_mode=periode&start_date=2026-01-01&end_date=2026-12-31&view_type=rekap')->assertOk()
            ->assertSee('Total masuk', false);
    }

    public function test_dashboard_shows_buildings_rooms_and_active_loans(): void
    {
        $user = \App\Models\User::factory()->create();
        $b = \App\Models\Building::create(['code' => 'GDG-D', 'name' => 'Gedung D']);
        \App\Models\Location::create(['code' => 'R-D-01', 'name' => 'Ruang 01', 'building_id' => $b->id]);
        $asset = \App\Models\Asset::create([
            'asset_code' => 'AST-2026-KIBB-0301', 'name' => 'Laptop Aktif', 'kib_type' => 'B',
            'acquisition_date' => '2026-05-01', 'acquisition_value' => 8000000, 'funding_source' => 'BOS',
        ]);
        \App\Models\AssetOutflow::create([
            'asset_id' => $asset->id, 'outflow_date' => '2026-09-01', 'location_type' => 'Luar Sekolah',
            'borrower_name' => 'Ani', 'loan_date' => '2026-09-01',
        ]);
        $this->actingAs($user)->get('/dashboard')->assertOk()
            ->assertSee('Jumlah Gedung', false)->assertSee('Jumlah Ruang', false)
            ->assertSee('Belum Kembali', false)->assertSee('Ani', false);
    }

    private function makeXlsx(array $rows, string $path): void
    {
        $s = new \PhpOffice\PhpSpreadsheet\Spreadsheet;
        $s->getActiveSheet()->fromArray($rows, null, 'A1');
        (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($s))->save($path);
    }

    public function test_building_export_template_import_chunk(): void
    {
        $user = \App\Models\User::factory()->create();
        \App\Models\Building::create(['code' => 'GDG-X', 'name' => 'Gedung X']);
        $this->actingAs($user)->get('/buildings/export')->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->actingAs($user)->get('/buildings/template')->assertOk()->assertHeader('content-disposition', 'attachment; filename=template-gedung.xlsx');
        $this->actingAs($user)->get('/buildings')->assertSee('impBar', false)->assertSee('Template', false);
        $p = tempnam(sys_get_temp_dir(), 'bdg').'.xlsx';
        $this->makeXlsx([['kode', 'nama', 'keterangan'], ['GDG-I1', 'Gedung Impor 1', 'ket'], ['', '', ''], ['GDG-I2', 'Gedung Impor 2', '']], $p);
        $pre = $this->actingAs($user)->postJson('/buildings/import-preview', ['file' => new \Illuminate\Http\UploadedFile($p, 'g.xlsx', null, null, true)]);
        $pre->assertOk()->assertJson(['total' => 2]);
        $token = $pre->json('token');
        $this->actingAs($user)->postJson('/buildings/import-chunk', ['token' => $token, 'offset' => 0, 'limit' => 200])
            ->assertOk()->assertJson(['ok' => 2, 'errors' => []]);
        $this->assertDatabaseHas('buildings', ['code' => 'GDG-I1']);
        @unlink($p);
    }

    public function test_location_export_template_import_chunk(): void
    {
        $user = \App\Models\User::factory()->create();
        $b = \App\Models\Building::create(['code' => 'GDG-L', 'name' => 'Gedung L']);
        $this->actingAs($user)->get('/locations/export')->assertOk();
        $this->actingAs($user)->get('/locations/template')->assertOk();
        $this->actingAs($user)->get('/locations')->assertSee('impBar', false)->assertSee('Template', false);
        $p = tempnam(sys_get_temp_dir(), 'lok').'.xlsx';
        $this->makeXlsx([['kode', 'nama', 'kode_gedung', 'email_pic'], ['R-L-01', 'Ruang L1', 'GDG-L', ''], ['R-L-02', 'Ruang L2', 'GDG-NO', '']], $p);
        $pre = $this->actingAs($user)->postJson('/locations/import-preview', ['file' => new \Illuminate\Http\UploadedFile($p, 'r.xlsx', null, null, true)]);
        $pre->assertOk()->assertJson(['total' => 2]);
        $res = $this->actingAs($user)->postJson('/locations/import-chunk', ['token' => $pre->json('token'), 'offset' => 0, 'limit' => 200])->assertOk();
        $this->assertDatabaseHas('locations', ['code' => 'R-L-01', 'building_id' => $b->id]);
        $this->assertCount(1, $res->json('errors'));
        @unlink($p);
    }





}

