<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuildingController extends Controller
{
    public function index(): View
    {
        return view('buildings.index', ['buildings' => Building::withCount('rooms')->orderBy('code')->get()]);
    }

    public function create(): View { return view('buildings.form', ['building' => new Building]); }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:buildings,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        Building::create($data);
        return redirect()->route('buildings.index')->with('ok', 'Gedung simpan.');
    }

    public function edit(Building $building): View { return view('buildings.form', compact('building')); }

    public function update(Request $request, Building $building): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:buildings,code,'.$building->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);
        $building->update($data);
        return redirect()->route('buildings.index')->with('ok', 'Gedung update.');
    }

    public function destroy(Building $building): RedirectResponse
    {
        $building->delete();
        return back()->with('ok', 'Gedung hapus.');
    }
}
