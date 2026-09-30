<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetOutflow;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
        foreach (['unit_price', 'acquisition_value'] as $f) {
            if (is_string($request->input($f))) {
                $request->merge([$f => str_replace('.', '', $request->input($f))]);
            }
        }
        if (!$request->filled('quantity') && !$request->filled('unit_price') && $request->filled('acquisition_value')) {
            $request->merge(['quantity' => 1, 'unit_price' => $request->input('acquisition_value')]); // ponytail: legacy single-field compat, drop when callers migrated
        }
        $data = $request->validate([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'acquisition_date' => 'required|date',
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255|unique:assets,asset_code',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:1000000',
            'unit_price' => 'required|numeric|min:0|max:9999999999999',
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
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'acquisition_value' => $data['quantity'] * $data['unit_price'],
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
        foreach (['unit_price', 'acquisition_value'] as $f) {
            if (is_string($request->input($f))) {
                $request->merge([$f => str_replace('.', '', $request->input($f))]);
            }
        }
        if (!$request->filled('quantity') && !$request->filled('unit_price') && $request->filled('acquisition_value')) {
            $request->merge(['quantity' => 1, 'unit_price' => $request->input('acquisition_value')]); // ponytail: legacy single-field compat, drop when callers migrated
        }
        $data = $request->validate([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'acquisition_date' => 'required|date',
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255|unique:assets,asset_code,'.$asset->id,
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:1000000',
            'unit_price' => 'required|numeric|min:0|max:9999999999999',
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
                'quantity' => $data['quantity'],
                'unit_price' => $data['unit_price'],
                'acquisition_value' => $data['quantity'] * $data['unit_price'],
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

    private function xlsxDownload(Spreadsheet $s, string $name): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($s) {
            (new Xlsx($s))->save('php://output');
        }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    private static function assetHeaders(): array
    {
        return ['tahun_perolehan', 'tanggal_perolehan', 'kib', 'kode_barang', 'nama_barang', 'jumlah', 'harga_satuan', 'sumber_dana', 'kode_ruangan'];
    }

    private static function assetRow(Asset $a): array
    {
        $d = $a->acquisition_date ? date('Y-m-d', strtotime((string) $a->acquisition_date)) : '';
        return [
            $d !== '' ? (int) substr($d, 0, 4) : '',
            $d,
            $a->kib_type, $a->asset_code, $a->name,
            $a->quantity ?? 1, (float) ($a->unit_price ?? $a->acquisition_value),
            $a->funding_source ?? '', $a->location?->code ?? '',
        ];
    }

    private static function assetDropdown($sh, string $col, string $list, int $last = 1001): void
    {
        for ($r = 2; $r <= $last; $r++) {
            $v = $sh->getCell("$col$r")->getDataValidation();
            $v->setType(DataValidation::TYPE_LIST);
            $v->setErrorStyle(DataValidation::STYLE_STOP);
            $v->setAllowBlank(false);
            $v->setShowInputMessage(true);
            $v->setShowErrorMessage(true);
            $v->setShowDropDown(false);
            $v->setErrorTitle('Pilihan salah');
            $v->setError('Pilih dari daftar, jangan ketik manual.');
            $v->setPromptTitle('Pilih dari daftar');
            $v->setPrompt('Klik panah dropdown, jangan ketik manual.');
            $v->setFormula1('"' . $list . '"');
        }
    }

    public function exportMasuk()
    {
        $rows = Asset::with('location')->orderBy('asset_code')->get()->map(fn ($a) => self::assetRow($a))->toArray();
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray([self::assetHeaders()], null, 'A1');
        if ($rows) $sh->fromArray($rows, null, 'A2');
        $sh->getStyle('A1:I1')->getFont()->setBold(true);
        foreach (range('A', 'I') as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        return $this->xlsxDownload($s, 'aset-masuk-' . date('Ymd-His') . '.xlsx');
    }

    public function templateMasuk()
    {
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray([
            self::assetHeaders(),
            [2026, '2026-03-15', 'B', 'AST-2026-KIBB-0001', 'Laptop', 2, 5000000, 'BOS', 'R-A-01'],
            [2026, '2026-05-01', 'C', 'AST-2026-KIBC-0001', 'Ruang Lab', 1, 150000000, 'DAK', ''],
        ], null, 'A1');
        self::assetDropdown($sh, 'C', 'A,B,C,D,E');
        self::assetDropdown($sh, 'H', 'BOS,DAK,HIBAH,Komite');
        $sh->getStyle('A1:I1')->getFont()->setBold(true);
        foreach (range('A', 'I') as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        return $this->xlsxDownload($s, 'template-aset-masuk.xlsx');
    }

    public function importPreviewMasuk(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $rows = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet()->toArray(null, true, false);
        array_shift($rows);
        $rows = array_values(array_filter($rows, fn ($r) => trim((string) ($r[3] ?? '') . ($r[4] ?? '')) !== ''));
        $token = Str::random(32);
        Cache::put("imp-ast-$token", $rows, 600);
        return response()->json(['token' => $token, 'total' => count($rows)]);
    }

    public function importChunkMasuk(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => 'required|string', 'offset' => 'required|integer|min:0', 'limit' => 'required|integer|min:1|max:500']);
        $rows = Cache::get("imp-ast-{$data['token']}", []);
        $slice = array_slice($rows, $data['offset'], $data['limit']);
        $ok = 0; $errors = [];
        $yearNow = (int) date('Y');
        foreach ($slice as $i => $r) {
            $no = $data['offset'] + $i + 2;
            $year = (int) trim((string) ($r[0] ?? ''));
            $date = trim((string) ($r[1] ?? ''));
            $kib = strtoupper(trim((string) ($r[2] ?? '')));
            $code = trim((string) ($r[3] ?? ''));
            $name = trim((string) ($r[4] ?? ''));
            $qty = self::numCell($r[5] ?? '');
            $price = self::numCell($r[6] ?? '');
            $dana = trim((string) ($r[7] ?? ''));
            $room = trim((string) ($r[8] ?? ''));
            if ($code === '' || $name === '') { $errors[] = "Baris $no: kode_barang+nama_barang wajib."; continue; }
            if (! in_array($kib, ['A', 'B', 'C', 'D', 'E'], true)) { $errors[] = "Baris $no: kib harus A/B/C/D/E."; continue; }
            if (! in_array($dana, ['BOS', 'DAK', 'HIBAH', 'Komite'], true)) { $errors[] = "Baris $no: sumber_dana BOS/DAK/HIBAH/Komite."; continue; }
            $ts = self::parseImportDate($date);
            if ($ts === null) { $errors[] = "Baris $no: tanggal YYYY-MM-DD."; continue; }
            if ($year < 1990 || $year > $yearNow + 1 || $year !== (int) date('Y', $ts)) { $errors[] = "Baris $no: tahun beda tanggal."; continue; }
            if (! is_finite($qty) || $qty < 1 || $qty > 1000000 || floor($qty) != $qty) { $errors[] = "Baris $no: jumlah 1-1000000."; continue; }
            if (! is_finite($price) || $price < 0 || $price > 9999999999999) { $errors[] = "Baris $no: harga 0-9999999999999."; continue; }
            $locId = null;
            if ($room !== '') {
                $loc = Location::where('code', $room)->first();
                if (! $loc) { $errors[] = "Baris $no: ruangan $room tak ada."; continue; }
                $locId = $loc->id;
            }
            Asset::updateOrCreate(['asset_code' => $code], [
                'name' => $name, 'kib_type' => $kib, 'location_id' => $locId,
                'acquisition_date' => date('Y-m-d', $ts),
                'quantity' => (int) $qty, 'unit_price' => $price,
                'acquisition_value' => $qty * $price, 'funding_source' => $dana,
            ]);
            $ok++;
        }
        return response()->json(['ok' => $ok, 'errors' => $errors]);
    }

    private static function parseImportDate($v): ?int
    {
        if (is_numeric($v)) {
            try { return ExcelDate::excelToTimestamp((float) $v); } catch (\Throwable) { return null; }
        }
        $ts = strtotime(trim((string) $v));
        return $ts === false ? null : $ts;
    }

    private static function numCell($v): float
    {
        if (is_numeric($v)) return (float) $v;
        $s = str_replace([' ', '.'], '', trim((string) $v));
        $s = str_replace(',', '.', $s);
        return is_numeric($s) ? (float) $s : NAN;
    }

    public function indexKeluar(): View
    {
        return view('transactions.asset_keluar_index', ['outflows' => AssetOutflow::with('asset')->latest()->get()]);
    }

    public function createKeluar(): View
    {
        $assets = Asset::orderBy('name')->get();
        return view('transactions.asset_keluar_form', [
            'assets' => $assets,
            'stockMap' => self::availableMap($assets),
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
        $assets = Asset::orderBy('name')->get();
        return view('transactions.asset_keluar_form', [
            'assets' => $assets,
            'stockMap' => self::availableMap($assets, $keluar),
            'outflows' => AssetOutflow::with('asset')->latest()->get(),
            'outflow' => $keluar,
        ]);
    }

    public function updateKeluar(Request $request, AssetOutflow $keluar): RedirectResponse
    {
        $keluar->update(self::validateKeluar($request, $keluar));

        return redirect()->route('transactions.asset.keluar.create')->with('ok', 'Aset keluar update.');
    }

    public function destroyKeluar(AssetOutflow $keluar): RedirectResponse
    {
        $keluar->delete();

        return back()->with('ok', 'Aset keluar hapus.');
    }

    private static function validateKeluar(Request $request, ?AssetOutflow $ignore = null): array
    {
        $data = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'outflow_date' => 'required|date',
            'location_type' => 'required|in:Sekolah,Luar Sekolah',
            'quantity' => 'nullable|integer|min:1|max:1000000',
            'borrower_name' => 'required_if:location_type,Luar Sekolah|nullable|string|max:255',
            'loan_date' => 'required_if:location_type,Luar Sekolah|nullable|date',
            'return_date' => 'nullable|date|after_or_equal:loan_date',
            'notes' => 'nullable|string',
        ]);
        if ($data['location_type'] === 'Sekolah') {
            $data['quantity'] = null;
            $data['borrower_name'] = null;
            $data['loan_date'] = null;
            $data['return_date'] = null;
        } else {
            // ponytail: legacy tanpa qty dianggap 1, wajibkan required_if saat semua data lama sudah punya qty.
            $data['quantity'] = $data['quantity'] ?? 1;
            $avail = self::availableMap(Asset::where('id', $data['asset_id'])->get(), $ignore)[$data['asset_id']] ?? 0;
            if ($data['quantity'] > $avail) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => "Stok tidak cukup. Sisa: {$avail}."]);
            }
        }

        return $data;
    }

    /** Sisa = quantity Masuk − pinjam aktif (Luar Sekolah, belum kembali). */
    private static function availableMap($assets, ?AssetOutflow $ignore = null): array
    {
        $q = AssetOutflow::where('location_type', 'Luar Sekolah')->whereNull('return_date');
        if ($ignore) $q->where('id', '!=', $ignore->id);
        $borrowed = $q->selectRaw('asset_id, SUM(COALESCE(quantity,1)) as total')->groupBy('asset_id')->pluck('total', 'asset_id');
        $map = [];
        foreach ($assets as $a) {
            $map[$a->id] = max(0, ($a->quantity ?? 1) - (int) ($borrowed[$a->id] ?? 0));
        }
        return $map;
    }

    public function placeholder(string $label): View
    {
        return view('transactions.placeholder', ['label' => $label]);
    }
}
