<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AssetTransactionController extends Controller
{
    public function indexMasuk(): View
    {
        $assets = Asset::with('location')->latest()->paginate(15);
        return view('transactions.asset_masuk_index', compact('assets'));
    }

    public function createMasuk(): View
    {
        return view('transactions.asset_masuk_form', [
            'locations' => Location::with('building')->orderBy('name')->get(),
            'yearNow' => (int) date('Y'),
        ]);
    }

    public function storeMasuk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255|unique:assets,asset_code',
            'name' => 'required|string|max:255',
            'acquisition_value' => 'required|numeric|min:0',
            'funding_source' => 'required|in:BOS,DAK,HIBAH,Komite',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        DB::transaction(fn () => Asset::create([
            'asset_code' => $data['asset_code'],
            'name' => $data['name'],
            'kib_type' => $data['kib_type'],
            'location_id' => $data['location_id'] ?? null,
            // ponytail: year-only input stored as Jan 1; upgrade to full datepicker when needed.
            'acquisition_date' => $data['procurement_year'].'-01-01',
            'acquisition_value' => $data['acquisition_value'],
            'funding_source' => $data['funding_source'],
        ]));

        return redirect()->route('transactions.asset.masuk')->with('ok', 'Aset masuk simpan.');
    }

    public function placeholder(string $label): View
    {
        return view('transactions.placeholder', ['label' => $label]);
    }
}
