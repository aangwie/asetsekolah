<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetLoan;
use App\Models\BhpItem;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard', [
            'totalAssets' => Asset::count(),
            'totalValue' => Asset::sum('acquisition_value'),
            'borrowed' => Asset::where('status', 'dipinjam')->count(),
            'lowStock' => BhpItem::whereColumn('current_stock', '<=', 'minimum_stock')->count(),
            'openLoans' => AssetLoan::whereIn('status', ['pending', 'approved', 'borrowed', 'overdue'])->latest()->limit(5)->get(),
            'lowItems' => BhpItem::whereColumn('current_stock', '<=', 'minimum_stock')->orderBy('current_stock')->limit(5)->get(),
        ]);
    }
}
