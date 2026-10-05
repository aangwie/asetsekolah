<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetOutflow;
use App\Models\BhpItem;
use App\Models\Building;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
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
            'borrowed' => (int) AssetOutflow::where('location_type', 'Luar Sekolah')->whereRaw('(COALESCE(quantity,1) - COALESCE(returned_quantity,0)) > 0')->sum(DB::raw('COALESCE(quantity,1) - COALESCE(returned_quantity,0)')),
            'lowStock' => BhpItem::whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'activeOutflows' => AssetOutflow::with('asset')->where('location_type', 'Luar Sekolah')->whereRaw('(COALESCE(quantity,1) - COALESCE(returned_quantity,0)) > 0')->latest()->get(),
            'lowItems' => BhpItem::whereColumn('current_stock', '<=', 'minimum_stock')->orderBy('current_stock')->limit(5)->get(),
        ]);
    }
}
