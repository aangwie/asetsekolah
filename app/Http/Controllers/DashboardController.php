<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetOutflow;
use App\Models\BhpItem;
use App\Models\Building;
use App\Models\Location;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'totalAssets' => Asset::count(),
            'totalValue' => (float) Asset::sum('acquisition_value'),
            'totalBuildings' => Building::count(),
            'totalRooms' => Location::count(),
            'borrowed' => AssetOutflow::where('location_type', 'Luar Sekolah')->whereNull('return_date')->count(),
            'lowStock' => BhpItem::whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'activeOutflows' => AssetOutflow::with('asset')->where('location_type', 'Luar Sekolah')->whereNull('return_date')->latest()->limit(5)->get(),
            'lowItems' => BhpItem::whereColumn('current_stock', '<=', 'minimum_stock')->orderBy('current_stock')->limit(5)->get(),
        ]);
    }
}
