<?php

use App\Http\Controllers\AssetTransactionController;
use App\Models\Asset;
use App\Models\BhpItem;
use App\Models\AssetOutflow;
use App\Models\Location;
use App\Http\Controllers\BhpTransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SchoolProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WebSettingController;
use Illuminate\Support\Facades\Route;

Route::get('/login', [AuthController::class, 'showLogin'])->name('login')->middleware('guest');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

Route::get('/', function () {
    if (auth()->check()) return redirect()->route('dashboard');
    $assets = Asset::with('location')->orderByDesc('procurement_year')->orderBy('name')->get();
    $bhpItems = BhpItem::orderBy('name')->get();
    $locations = Location::orderBy('name')->get(['id', 'name']);
    $years = Asset::query()->distinct()->orderByDesc('procurement_year')->pluck('procurement_year')->filter()->values();
    if ($years->isEmpty()) $years = collect([now()->year]);
    $perKib = Asset::selectRaw("kib_type, COUNT(*) as `rows`, COALESCE(SUM(quantity),0) as `units`")->groupBy('kib_type')->get()->keyBy('kib_type');
    return view('welcome', [
        'assets' => $assets, 'bhpItems' => $bhpItems, 'locations' => $locations, 'years' => $years, 'perKib' => $perKib,
        'totalUnits' => (int) Asset::sum('quantity'), 'totalTypes' => Asset::count(),
        'totalBhp' => (int) BhpItem::sum('current_stock'), 'totalRooms' => Location::count(),
        'borrowed' => (int) AssetOutflow::where('location_type', 'Luar Sekolah')->whereNull('return_date')->count(),
    ]);
})->name('home');
Route::get('/preview', function () {
    $assets = Asset::with('location')->orderByDesc('procurement_year')->orderBy('name')->get();
    $bhpItems = BhpItem::orderBy('name')->get();
    $locations = Location::orderBy('name')->get(['id', 'name']);
    $years = Asset::query()->distinct()->orderByDesc('procurement_year')->pluck('procurement_year')->filter()->values();
    if ($years->isEmpty()) $years = collect([now()->year]);
    $perKib = Asset::selectRaw("kib_type, COUNT(*) as `rows`, COALESCE(SUM(quantity),0) as `units`")->groupBy('kib_type')->get()->keyBy('kib_type');
    return view('welcome', [
        'assets' => $assets, 'bhpItems' => $bhpItems, 'locations' => $locations, 'years' => $years, 'perKib' => $perKib,
        'totalUnits' => (int) Asset::sum('quantity'), 'totalTypes' => Asset::count(),
        'totalBhp' => (int) BhpItem::sum('current_stock'), 'totalRooms' => Location::count(),
        'borrowed' => (int) AssetOutflow::where('location_type', 'Luar Sekolah')->whereNull('return_date')->count(),
    ]);
})->name('preview');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/transactions/asset/masuk', [AssetTransactionController::class, 'indexMasuk'])->name('transactions.asset.masuk');
    Route::get('/transactions/asset/masuk/create', [AssetTransactionController::class, 'createMasuk'])->name('transactions.asset.masuk.create');
    Route::post('/transactions/asset/masuk', [AssetTransactionController::class, 'storeMasuk'])->name('transactions.asset.masuk.store');
    Route::get('/transactions/asset/masuk/{asset}/edit', [AssetTransactionController::class, 'editMasuk'])->name('transactions.asset.masuk.edit');
    Route::put('/transactions/asset/masuk/{asset}', [AssetTransactionController::class, 'updateMasuk'])->name('transactions.asset.masuk.update');
    Route::delete('/transactions/asset/masuk/{asset}', [AssetTransactionController::class, 'destroyMasuk'])->name('transactions.asset.masuk.destroy');
    Route::get('/transactions/asset/masuk-export', [AssetTransactionController::class, 'exportMasuk'])->name('transactions.asset.masuk.export');
    Route::get('/transactions/asset/masuk-template', [AssetTransactionController::class, 'templateMasuk'])->name('transactions.asset.masuk.template');
    Route::post('/transactions/asset/masuk-import-preview', [AssetTransactionController::class, 'importPreviewMasuk'])->name('transactions.asset.masuk.import-preview');
    Route::post('/transactions/asset/masuk-import-chunk', [AssetTransactionController::class, 'importChunkMasuk'])->name('transactions.asset.masuk.import-chunk');
    Route::get('/transactions/asset/keluar', [AssetTransactionController::class, 'indexKeluar'])->name('transactions.asset.keluar');
    Route::get('/transactions/asset/keluar/create', [AssetTransactionController::class, 'createKeluar'])->name('transactions.asset.keluar.create');
    Route::post('/transactions/asset/keluar', [AssetTransactionController::class, 'storeKeluar'])->name('transactions.asset.keluar.store');
    Route::get('/transactions/asset/keluar/{keluar}/edit', [AssetTransactionController::class, 'editKeluar'])->name('transactions.asset.keluar.edit');
    Route::put('/transactions/asset/keluar/{keluar}', [AssetTransactionController::class, 'updateKeluar'])->name('transactions.asset.keluar.update');
    Route::delete('/transactions/asset/keluar/{keluar}', [AssetTransactionController::class, 'destroyKeluar'])->name('transactions.asset.keluar.destroy');
    Route::get('/transactions/bhp/masuk', [BhpTransactionController::class, 'indexMasuk'])->name('transactions.bhp.masuk');
    Route::get('/transactions/bhp/masuk/create', [BhpTransactionController::class, 'createMasuk'])->name('transactions.bhp.masuk.create');
    Route::post('/transactions/bhp/masuk', [BhpTransactionController::class, 'storeMasuk'])->name('transactions.bhp.masuk.store');
    Route::get('/transactions/bhp/masuk/{masuk}/edit', [BhpTransactionController::class, 'editMasuk'])->name('transactions.bhp.masuk.edit');
    Route::put('/transactions/bhp/masuk/{masuk}', [BhpTransactionController::class, 'updateMasuk'])->name('transactions.bhp.masuk.update');
    Route::delete('/transactions/bhp/masuk/{masuk}', [BhpTransactionController::class, 'destroyMasuk'])->name('transactions.bhp.masuk.destroy');
    Route::get('/transactions/bhp/keluar', [BhpTransactionController::class, 'indexKeluar'])->name('transactions.bhp.keluar');
    Route::get('/transactions/bhp/keluar/create', [BhpTransactionController::class, 'createKeluar'])->name('transactions.bhp.keluar.create');
    Route::post('/transactions/bhp/keluar', [BhpTransactionController::class, 'storeKeluar'])->name('transactions.bhp.keluar.store');
    Route::get('/transactions/bhp/keluar/{keluar}/edit', [BhpTransactionController::class, 'editKeluar'])->name('transactions.bhp.keluar.edit');
    Route::put('/transactions/bhp/keluar/{keluar}', [BhpTransactionController::class, 'updateKeluar'])->name('transactions.bhp.keluar.update');
    Route::delete('/transactions/bhp/keluar/{keluar}', [BhpTransactionController::class, 'destroyKeluar'])->name('transactions.bhp.keluar.destroy');
    Route::get('/buildings/export', [BuildingController::class, 'export'])->name('buildings.export');
    Route::get('/buildings/template', [BuildingController::class, 'template'])->name('buildings.template');
    Route::post('/buildings/import-preview', [BuildingController::class, 'importPreview'])->name('buildings.import-preview');
    Route::post('/buildings/import-chunk', [BuildingController::class, 'importChunk'])->name('buildings.import-chunk');
    Route::get('/locations/export', [LocationController::class, 'export'])->name('locations.export');
    Route::get('/locations/template', [LocationController::class, 'template'])->name('locations.template');
    Route::post('/locations/import-preview', [LocationController::class, 'importPreview'])->name('locations.import-preview');
    Route::post('/locations/import-chunk', [LocationController::class, 'importChunk'])->name('locations.import-chunk');
    Route::resource('locations', LocationController::class)->except('show');
    Route::resource('buildings', BuildingController::class)->except('show');
    Route::resource('users', UserController::class)->except('show')->middleware('role:superadmin');
    Route::middleware('role:superadmin')->prefix('web-settings')->name('web-settings.')->group(function () {
        Route::get('/', [WebSettingController::class, 'index'])->name('index');
        Route::post('/token', [WebSettingController::class, 'updateToken'])->name('token');
        Route::post('/pull', [WebSettingController::class, 'pull'])->name('pull');
        Route::post('/symlink', [WebSettingController::class, 'symlink'])->name('symlink');
        Route::post('/migrate', [WebSettingController::class, 'migrate'])->name('migrate');
        Route::post('/clear', [WebSettingController::class, 'clear'])->name('clear');
        Route::get('/export', [WebSettingController::class, 'export'])->name('export');
        Route::post('/import', [WebSettingController::class, 'import'])->name('import');
        Route::post('/clear-log', [WebSettingController::class, 'clearLog'])->name('clear-log');
    });
    Route::get('/school-profile', [SchoolProfileController::class, 'edit'])->name('school-profile.edit')->middleware('role:superadmin|pengelola_aset');
    Route::put('/school-profile', [SchoolProfileController::class, 'update'])->name('school-profile.update')->middleware('role:superadmin|pengelola_aset');
    Route::get('/laporan/aset/data', [ReportController::class, 'assetData'])->name('reports.asset.data');
    Route::get('/laporan/aset/rekap', [ReportController::class, 'assetRekap'])->name('reports.asset.rekap');
    Route::get('/laporan/aset/kib/{kib}', [ReportController::class, 'assetKib'])->name('reports.asset.kib')->where('kib', '[A-Ea-e]');
    Route::get('/laporan/aset/mutasi', [ReportController::class, 'assetMutasi'])->name('reports.asset.mutasi');
    Route::get('/laporan/bhp', [ReportController::class, 'bhp'])->name('reports.bhp.index');
});

