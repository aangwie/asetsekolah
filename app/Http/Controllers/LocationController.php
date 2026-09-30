<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocationController extends Controller
{
    public function index(): View
    {
        return view('locations.index', ['locations' => Location::with(['building', 'pic'])->orderBy('code')->get()]);
    }

    public function create(): View
    {
        return view('locations.form', [
            'location' => new Location,
            'buildings' => Building::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:locations,code',
            'name' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
            'pic_user_id' => 'nullable|exists:users,id',
        ]);
        Location::create($data);
        return redirect()->route('locations.index')->with('ok', 'Ruangan simpan.');
    }

    public function edit(Location $location): View
    {
        return view('locations.form', [
            'location' => $location,
            'buildings' => Building::orderBy('name')->get(),
            'users' => User::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Location $location): RedirectResponse
    {
        $data = $request->validate([
            'code' => 'required|string|max:50|unique:locations,code,'.$location->id,
            'name' => 'required|string|max:255',
            'building_id' => 'required|exists:buildings,id',
            'pic_user_id' => 'nullable|exists:users,id',
        ]);
        $location->update($data);
        return redirect()->route('locations.index')->with('ok', 'Ruangan update.');
    }

    public function destroy(Location $location): RedirectResponse
    {
        $location->delete();
        return back()->with('ok', 'Ruangan hapus.');
    }
}
