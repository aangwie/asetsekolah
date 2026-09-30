<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetOutflow;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AssetTransactionController extends Controller
{
    public function indexMasuk(): View
    {
        $assets = Asset::with('location')->latest()->get();
        return view('transactions.asset_masuk_index', compact('assets'));
    }

    public function createMasuk(): View
    {
        return view('transactions.asset_masuk_form', [
            'locations' => Location::with('building')->orderBy('name')->get(),
            'yearNow' => (int) date('Y'),
            'assets' => Asset::with('location')->latest()->get(),
        ]);
    }

    public function storeMasuk(Request $request): RedirectResponse
    {
        if (is_string($request->input('acquisition_value'))) {
            $request->merge(['acquisition_value' => str_replace('.', '', $request->input('acquisition_value'))]);
        }
        $data = $request->validate([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'acquisition_date' => 'required|date',
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255|unique:assets,asset_code',
            'name' => 'required|string|max:255',
            'acquisition_value' => 'required|numeric|min:0',
            'funding_source' => 'required|in:BOS,DAK,HIBAH,Komite',
            'location_id' => 'nullable|exists:locations,id',
            'proof' => 'nullable|file|max:500|mimes:jpg,jpeg,png,webp,pdf',
        ]);

        if ((int) date('Y', strtotime($data['acquisition_date'])) !== (int) $data['procurement_year']) {
            return back()->withErrors(['acquisition_date' => 'Tahun tanggal beda dengan Tahun Perolehan.'])->withInput();
        }

        $proofPath = $request->hasFile('proof') ? self::storeProof($request->file('proof')) : null;

        try {
            DB::transaction(fn () => Asset::create([
                'asset_code' => $data['asset_code'],
                'name' => $data['name'],
                'kib_type' => $data['kib_type'],
                'location_id' => $data['location_id'] ?? null,
                'acquisition_date' => $data['acquisition_date'],
                'acquisition_value' => $data['acquisition_value'],
                'funding_source' => $data['funding_source'],
                'proof_path' => $proofPath,
            ]));
        } catch (\Throwable $e) {
            if ($proofPath) Storage::disk('public')->delete($proofPath);
            throw $e;
        }

        return redirect()->route('transactions.asset.masuk.create')->with('ok', 'Aset masuk simpan.');
    }

    private static function storeProof(UploadedFile $file): string
    {
        if ($file->getMimeType() === 'application/pdf' || strtolower($file->getClientOriginalExtension()) === 'pdf') {
            return $file->store('proofs', 'public');
        }

        $img = imagecreatefromstring(file_get_contents($file->getRealPath()));
        $path = 'proofs/'.Str::uuid()->toString().'.webp';
        $tmp = tempnam(sys_get_temp_dir(), 'webp');
        imagewebp($img, $tmp, 80);
        imagedestroy($img);
        Storage::disk('public')->put($path, file_get_contents($tmp));
        unlink($tmp);

        return $path;
    }

    public function editMasuk(Asset $asset): View
    {
        return view('transactions.asset_masuk_form', [
            'locations' => Location::with('building')->orderBy('name')->get(),
            'yearNow' => (int) date('Y'),
            'assets' => Asset::with('location')->latest()->get(),
            'asset' => $asset,
        ]);
    }

    public function updateMasuk(Request $request, Asset $asset): RedirectResponse
    {
        if (is_string($request->input('acquisition_value'))) {
            $request->merge(['acquisition_value' => str_replace('.', '', $request->input('acquisition_value'))]);
        }
        $data = $request->validate([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'acquisition_date' => 'required|date',
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255|unique:assets,asset_code,'.$asset->id,
            'name' => 'required|string|max:255',
            'acquisition_value' => 'required|numeric|min:0',
            'funding_source' => 'required|in:BOS,DAK,HIBAH,Komite',
            'location_id' => 'nullable|exists:locations,id',
            'proof' => 'nullable|file|max:500|mimes:jpg,jpeg,png,webp,pdf',
        ]);

        if ((int) date('Y', strtotime($data['acquisition_date'])) !== (int) $data['procurement_year']) {
            return back()->withErrors(['acquisition_date' => 'Tahun tanggal beda dengan Tahun Perolehan.'])->withInput();
        }

        $newProof = $request->hasFile('proof') ? self::storeProof($request->file('proof')) : null;
        $oldProof = $asset->proof_path;

        try {
            DB::transaction(fn () => $asset->update([
                'asset_code' => $data['asset_code'],
                'name' => $data['name'],
                'kib_type' => $data['kib_type'],
                'location_id' => $data['location_id'] ?? null,
                'acquisition_date' => $data['acquisition_date'],
                'acquisition_value' => $data['acquisition_value'],
                'funding_source' => $data['funding_source'],
                'proof_path' => $newProof ?? $oldProof,
            ]));
        } catch (\Throwable $e) {
            if ($newProof) Storage::disk('public')->delete($newProof);
            throw $e;
        }

        if ($newProof && $oldProof) Storage::disk('public')->delete($oldProof);

        return redirect()->route('transactions.asset.masuk.create')->with('ok', 'Aset masuk update.');
    }

    public function destroyMasuk(Asset $asset): RedirectResponse
    {
        $proof = $asset->proof_path;
        $asset->delete();
        if ($proof) Storage::disk('public')->delete($proof);

        return back()->with('ok', 'Aset hapus.');
    }

    public function indexKeluar(): View
    {
        return view('transactions.asset_keluar_index', ['outflows' => AssetOutflow::with('asset')->latest()->get()]);
    }

    public function createKeluar(): View
    {
        return view('transactions.asset_keluar_form', [
            'assets' => Asset::orderBy('name')->get(),
            'outflows' => AssetOutflow::with('asset')->latest()->get(),
        ]);
    }

    public function storeKeluar(Request $request): RedirectResponse
    {
        $data = self::validateKeluar($request);
        AssetOutflow::create($data);

        return redirect()->route('transactions.asset.keluar.create')->with('ok', 'Aset keluar simpan.');
    }

    public function editKeluar(AssetOutflow $keluar): View
    {
        return view('transactions.asset_keluar_form', [
            'assets' => Asset::orderBy('name')->get(),
            'outflows' => AssetOutflow::with('asset')->latest()->get(),
            'outflow' => $keluar,
        ]);
    }

    public function updateKeluar(Request $request, AssetOutflow $keluar): RedirectResponse
    {
        $keluar->update(self::validateKeluar($request));

        return redirect()->route('transactions.asset.keluar.create')->with('ok', 'Aset keluar update.');
    }

    public function destroyKeluar(AssetOutflow $keluar): RedirectResponse
    {
        $keluar->delete();

        return back()->with('ok', 'Aset keluar hapus.');
    }

    private static function validateKeluar(Request $request): array
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'outflow_date' => 'required|date',
            'location_type' => 'required|in:Sekolah,Luar Sekolah',
            'borrower_name' => 'required_if:location_type,Luar Sekolah|nullable|string|max:255',
            'loan_date' => 'required_if:location_type,Luar Sekolah|nullable|date',
            'return_date' => 'nullable|date|after_or_equal:loan_date',
            'notes' => 'nullable|string',
        ]);
        if ($data['location_type'] === 'Sekolah') {
            $data['borrower_name'] = null;
            $data['loan_date'] = null;
            $data['return_date'] = null;
        }

        return $data;
    }

    public function placeholder(string $label): View
    {
        return view('transactions.placeholder', ['label' => $label]);
    }
}
