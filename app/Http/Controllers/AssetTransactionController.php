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
            'detail' => null,
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
        $data = $request->validate(array_merge([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'acquisition_date' => 'required|date',
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:1000000',
            'unit_price' => 'required|numeric|min:0|max:9999999999999',
            'funding_source' => 'required|in:BOS,DAK,HIBAH,Komite,APBN,APBD,Lainnya',
            'location_id' => 'nullable|exists:locations,id',
            'proof' => 'nullable|file|max:500|mimes:jpg,jpeg,png,webp,pdf',
        ], self::kibDetailRules()));

        if ((int) date('Y', strtotime($data['acquisition_date'])) !== (int) $data['procurement_year']) {
            return back()->withErrors(['acquisition_date' => 'Tahun tanggal beda dengan Tahun Perolehan.'])->withInput();
        }

        $proofPath = $request->hasFile('proof') ? self::storeProof($request->file('proof')) : null;

        try {
            DB::transaction(function () use ($data, $proofPath) {
                $asset = Asset::create([
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
                ]);
                self::saveKibDetail($asset, $data);
            });
        } catch (\Throwable $e) {
            if ($proofPath) Storage::disk('public')->delete($proofPath);
            throw $e;
        }

        return redirect()->route('transactions.asset.masuk.create')->with('ok', 'Aset masuk simpan.');
    }

    private static function kibDetailRules(): array
    {
        $yearMax = (int) date('Y') + 1;

        return [
            'surface_area' => 'nullable|numeric|min:0|max:9999999999',
            'certificate_number' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'specification' => 'nullable|string|max:255',
            'floor_area' => 'nullable|numeric|min:0|max:9999999999',
            'building_condition' => 'nullable|in:Bertingkat,Tidak Bertingkat',
            'length' => 'nullable|numeric|min:0|max:9999999999',
            'width' => 'nullable|numeric|min:0|max:9999999999',
            'book_title' => 'nullable|string|max:255',
            'book_author' => 'nullable|string|max:255',
            'publication_year' => "nullable|integer|min:1900|max:{$yearMax}",
        ];
    }

    private static function saveKibDetail(Asset $asset, array $data): void
    {
        foreach (['A' => 'kibA', 'B' => 'kibB', 'C' => 'kibC', 'D' => 'kibD', 'E' => 'kibE'] as $t => $rel) {
            if ($t !== $data['kib_type']) $asset->{$rel}()->delete();
        }
        switch ($data['kib_type']) {
            case 'A':
                \App\Models\KibALand::updateOrCreate(['asset_id' => $asset->id], [
                    'surface_area' => $data['surface_area'] ?? null,
                    'certificate_number' => $data['certificate_number'] ?? null,
                    'address' => '-', 'land_use' => '-', // ponytail: legacy wajib, isi default; tambah input saat dibutuhkan
                ]);
                break;
            case 'B':
                \App\Models\KibBEquipment::updateOrCreate(['asset_id' => $asset->id], [
                    'brand' => $data['brand'] ?? null,
                    'size_material' => $data['specification'] ?? null,
                ]);
                break;
            case 'C':
                \App\Models\KibCBuilding::updateOrCreate(['asset_id' => $asset->id], [
                    'floor_area' => $data['floor_area'] ?? null,
                    'building_condition' => $data['building_condition'] ?? 'Tidak Bertingkat',
                    'address' => '-', // ponytail: legacy wajib, isi default; tambah input saat dibutuhkan
                ]);
                break;
            case 'D':
                \App\Models\KibDNetwork::updateOrCreate(['asset_id' => $asset->id], [
                    'length' => $data['length'] ?? null, 'width' => $data['width'] ?? null,
                    'construction_type' => '-', 'address' => '-', // ponytail: legacy wajib, isi default
                ]);
                break;
            case 'E':
                \App\Models\KibEOther::updateOrCreate(['asset_id' => $asset->id], [
                    'book_title' => $data['book_title'] ?? null,
                    'book_author' => $data['book_author'] ?? null,
                    'publication_year' => $data['publication_year'] ?? null,
                    'book_title_author' => trim(($data['book_title'] ?? '').' — '.($data['book_author'] ?? ''), ' —'),
                ]);
                break;
        }
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
            'detail' => $asset->detail(),
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
        $data = $request->validate(array_merge([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'acquisition_date' => 'required|date',
            'kib_type' => 'required|in:A,B,C,D,E',
            'asset_code' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1|max:1000000',
            'unit_price' => 'required|numeric|min:0|max:9999999999999',
            'funding_source' => 'required|in:BOS,DAK,HIBAH,Komite,APBN,APBD,Lainnya',
            'location_id' => 'nullable|exists:locations,id',
            'proof' => 'nullable|file|max:500|mimes:jpg,jpeg,png,webp,pdf',
        ], self::kibDetailRules()));

        if ((int) date('Y', strtotime($data['acquisition_date'])) !== (int) $data['procurement_year']) {
            return back()->withErrors(['acquisition_date' => 'Tahun tanggal beda dengan Tahun Perolehan.'])->withInput();
        }

        $newProof = $request->hasFile('proof') ? self::storeProof($request->file('proof')) : null;
        $oldProof = $asset->proof_path;

        try {
            DB::transaction(function () use ($asset, $data, $newProof, $oldProof) {
                $asset->update([
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
                ]);
                self::saveKibDetail($asset, $data);
            });
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

    private static function assetExtraHeaders(string $kib): array
    {
        return match (strtoupper($kib)) {
            'A' => ['luas_tanah_m2', 'nomor_sertifikat'],
            'B' => ['merk', 'spesifikasi'],
            'C' => ['luas_m2', 'jenis_konstruksi'],
            'D' => ['panjang_m', 'lebar_m'],
            'E' => ['judul_buku', 'pengarang', 'tahun_terbit'],
            default => [],
        };
    }

    private static function assetHeaders(?string $kib = null): array
    {
        $base = ['tahun_perolehan', 'tanggal_perolehan', 'kib', 'kode_barang', 'nama_barang', 'jumlah', 'harga_satuan', 'sumber_dana', 'kode_ruangan'];
        $ex = $kib ? self::assetExtraHeaders($kib) : [];
        return $ex ? array_merge($base, $ex) : $base;
    }

    private static function assetDetailCells(Asset $a, string $kib): array
    {
        return match (strtoupper($kib)) {
            'A' => [(string) ($a->kibA->surface_area ?? ''), (string) ($a->kibA->certificate_number ?? '')],
            'B' => [(string) ($a->kibB->brand ?? ''), (string) ($a->kibB->size_material ?? '')],
            'C' => [(string) ($a->kibC->floor_area ?? ''), (string) ($a->kibC->building_condition ?? '')],
            'D' => [(string) ($a->kibD->length ?? ''), (string) ($a->kibD->width ?? '')],
            'E' => [(string) ($a->kibE->book_title ?? ''), (string) ($a->kibE->book_author ?? ''), (string) ($a->kibE->publication_year ?? '')],
            default => [],
        };
    }

    private static function assetRow(Asset $a, ?string $kib = null): array
    {
        $d = $a->acquisition_date ? date('Y-m-d', strtotime((string) $a->acquisition_date)) : '';
        $base = [
            $d !== '' ? (int) substr($d, 0, 4) : '',
            $d,
            $a->kib_type, $a->asset_code, $a->name,
            $a->quantity ?? 1, (float) ($a->unit_price ?? $a->acquisition_value),
            $a->funding_source ?? '', $a->location?->code ?? '',
        ];
        $ex = $kib ? self::assetDetailCells($a, $kib) : [];
        return $ex ? array_merge($base, $ex) : $base;
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

    public function exportMasuk(Request $request)
    {
        $kib = strtoupper(trim((string) $request->query('kib', '')));
        $kib = in_array($kib, ['A', 'B', 'C', 'D', 'E'], true) ? $kib : null;
        $q = Asset::with(['location', 'kibA', 'kibB', 'kibC', 'kibD', 'kibE'])->orderBy('asset_code');
        if ($kib) $q->where('kib_type', $kib);
        $rows = $q->get()->map(fn ($a) => self::assetRow($a, $kib))->toArray();
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $headers = self::assetHeaders($kib);
        $sh->fromArray([$headers], null, 'A1');
        if ($rows) $sh->fromArray($rows, null, 'A2');
        $last = chr(ord('A') + count($headers) - 1);
        $sh->getStyle("A1:{$last}1")->getFont()->setBold(true);
        foreach (range('A', $last) as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        $name = $kib ? 'aset-masuk-kib-'.$kib.'-'.date('Ymd-His').'.xlsx' : 'aset-masuk-' . date('Ymd-His') . '.xlsx';
        return $this->xlsxDownload($s, $name);
    }

    public function templateMasuk(Request $request)
    {
        $kib = strtoupper(trim((string) $request->query('kib', '')));
        $kib = in_array($kib, ['A', 'B', 'C', 'D', 'E'], true) ? $kib : null;
        $headers = self::assetHeaders($kib);
        $samples = match ($kib) {
            'A' => [[2026, '2026-03-15', 'A', 'AST-2026-KIBA-0001', 'Tanah Sekolah', 1, 200000000, 'APBN', '', 500, 'SHM-001']],
            'B' => [[2026, '2026-03-15', 'B', 'AST-2026-KIBB-0001', 'Laptop', 2, 5000000, 'BOS', 'R-A-01', 'Lenovo', 'ThinkPad i5 8GB']],
            'C' => [[2026, '2026-05-01', 'C', 'AST-2026-KIBC-0001', 'Ruang Lab', 1, 150000000, 'DAK', '', 120, 'Bertingkat']],
            'D' => [[2026, '2026-05-01', 'D', 'AST-2026-KIBD-0001', 'Jalan Paving', 1, 50000000, 'APBD', '', 100, 3]],
            'E' => [[2026, '2026-03-15', 'E', 'AST-2026-KIBE-0001', 'Buku Paket IPA', 10, 85000, 'BOS', '', 'Ilmu Pengetahuan Alam', 'Kemendikbud', 2023]],
            default => [
                [2026, '2026-03-15', 'B', 'AST-2026-KIBB-0001', 'Laptop', 2, 5000000, 'BOS', 'R-A-01'],
                [2026, '2026-05-01', 'C', 'AST-2026-KIBC-0001', 'Ruang Lab', 1, 150000000, 'DAK', ''],
            ],
        };
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray(array_merge([$headers], $samples), null, 'A1');
        self::assetDropdown($sh, 'C', $kib ?? 'A,B,C,D,E');
        self::assetDropdown($sh, 'H', 'BOS,DAK,HIBAH,Komite,APBN,APBD,Lainnya');
        if ($kib === 'C') self::assetDropdown($sh, 'K', 'Bertingkat,Tidak Bertingkat');
        $last = chr(ord('A') + count($headers) - 1);
        $sh->getStyle("A1:{$last}1")->getFont()->setBold(true);
        foreach (range('A', $last) as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        $name = $kib ? "template-aset-masuk-kib-{$kib}.xlsx" : 'template-aset-masuk.xlsx';
        return $this->xlsxDownload($s, $name);
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
            if (! in_array($dana, ['BOS', 'DAK', 'HIBAH', 'Komite', 'APBN', 'APBD', 'Lainnya'], true)) { $errors[] = "Baris $no: sumber_dana BOS/DAK/HIBAH/Komite/APBN/APBD/Lainnya."; continue; }
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
            [$detail, $detailErr] = self::importKibDetail($kib, $r, $no, $yearNow + 1);
            if ($detailErr !== null) { $errors[] = $detailErr; continue; }
            try {
                $asset = Asset::create([
                    'asset_code' => $code,
                    'name' => $name, 'kib_type' => $kib, 'location_id' => $locId,
                    'acquisition_date' => date('Y-m-d', $ts),
                    'quantity' => (int) $qty, 'unit_price' => $price,
                    'acquisition_value' => $qty * $price, 'funding_source' => $dana,
                ]);
                self::saveKibDetail($asset, array_merge(['kib_type' => $kib], $detail));
            } catch (\Throwable $e) {
                $errors[] = "Baris $no: gagal simpan ({$e->getMessage()}).";
                continue;
            }
            $ok++;
        }
        return response()->json(['ok' => $ok, 'errors' => $errors]);
    }

    private static function importKibDetail(string $kib, array $r, int $no, int $yearMax): array
    {
        $s = fn ($i) => trim((string) ($r[$i] ?? ''));
        $num = fn ($i) => self::numCell($r[$i] ?? '');
        switch ($kib) {
            case 'A':
                $area = $s(9) === '' ? null : $num(9);
                if ($area !== null && (! is_finite($area) || $area < 0 || $area > 9999999999)) return [[], "Baris $no: luas_tanah_m2 0-9999999999."];
                return [['surface_area' => $area, 'certificate_number' => $s(10) === '' ? null : $s(10)], null];
            case 'B':
                return [['brand' => $s(9) === '' ? null : $s(9), 'specification' => $s(10) === '' ? null : $s(10)], null];
            case 'C':
                $area = $s(9) === '' ? null : $num(9);
                if ($area !== null && (! is_finite($area) || $area < 0 || $area > 9999999999)) return [[], "Baris $no: luas_m2 0-9999999999."];
                $cond = $s(10);
                if ($cond !== '' && ! in_array($cond, ['Bertingkat', 'Tidak Bertingkat'], true)) return [[], 'Baris $no: jenis_konstruksi Bertingkat/Tidak Bertingkat.'];
                return [['floor_area' => $area, 'building_condition' => $cond === '' ? null : $cond], null];
            case 'D':
                $len = $s(9) === '' ? null : $num(9);
                $wid = $s(10) === '' ? null : $num(10);
                foreach (['panjang_m' => $len, 'lebar_m' => $wid] as $f => $v) {
                    if ($v !== null && (! is_finite($v) || $v < 0 || $v > 9999999999)) return [[], "Baris $no: $f 0-9999999999."];
                }
                return [['length' => $len, 'width' => $wid], null];
            case 'E':
                $yt = $s(11);
                $yp = $yt === '' ? null : (int) $yt;
                if ($yt !== '' && (! ctype_digit($yt) || $yp < 1900 || $yp > $yearMax)) return [[], "Baris $no: tahun_terbit 1900-$yearMax."];
                return [['book_title' => $s(9) === '' ? null : $s(9), 'book_author' => $s(10) === '' ? null : $s(10), 'publication_year' => $yp], null];
            default:
                return [[], null];
        }
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
            'returned_quantity' => 'nullable|integer|min:0|max:1000000',
            'has_return' => 'nullable|boolean',
            'borrower_name' => 'required_if:location_type,Luar Sekolah|nullable|string|max:255',
            'loan_date' => 'required_if:location_type,Luar Sekolah|nullable|date',
            'return_date' => 'nullable|date|after_or_equal:loan_date',
            'notes' => 'nullable|string',
        ]);
        if ($data['location_type'] === 'Sekolah') {
            $data['quantity'] = null;
            $data['returned_quantity'] = null;
            $data['borrower_name'] = null;
            $data['loan_date'] = null;
            $data['return_date'] = null;
        } else {
            // ponytail: legacy tanpa qty dianggap 1, wajibkan required_if saat semua data lama sudah punya qty.
            $data['quantity'] = $data['quantity'] ?? 1;
            $hasReturn = $request->boolean('has_return') || ! empty($data['return_date']) || ((int) ($data['returned_quantity'] ?? 0)) > 0;
            $ret = $hasReturn ? (int) ($data['returned_quantity'] ?? 0) : 0;
            if ($hasReturn && $ret === 0) $ret = $data['quantity']; // centang kembali tanpa isi = kembali semua
            if ($ret > $data['quantity']) {
                throw \Illuminate\Validation\ValidationException::withMessages(['returned_quantity' => "Jumlah kembali maks {$data['quantity']}."]);
            }
            if ($ret > 0 && empty($data['return_date'])) {
                throw \Illuminate\Validation\ValidationException::withMessages(['return_date' => 'Isi tanggal kembali jika ada jumlah dikembalikan.']);
            }
            if ($ret === 0) {
                $data['returned_quantity'] = null;
                $data['return_date'] = null;
            } else {
                $data['returned_quantity'] = $ret;
            }
            $avail = self::availableMap(Asset::where('id', $data['asset_id'])->get(), $ignore)[$data['asset_id']] ?? 0;
            if (($data['quantity'] - (int) ($data['returned_quantity'] ?? 0)) > $avail) {
                throw \Illuminate\Validation\ValidationException::withMessages(['quantity' => "Stok tidak cukup. Sisa: {$avail} (termasuk akumulasi retur)."]);
            }
        }

        unset($data['has_return']);

        return $data;
    }

    /** Sisa = quantity Masuk − sisa pinjam (dipinjam − dikembalikan). */
    private static function availableMap($assets, ?AssetOutflow $ignore = null): array
    {
        $q = AssetOutflow::where('location_type', 'Luar Sekolah')
            ->whereRaw('(COALESCE(quantity,1) - COALESCE(returned_quantity,0)) > 0');
        if ($ignore) $q->where('id', '!=', $ignore->id);
        $borrowed = $q->selectRaw('asset_id, SUM(COALESCE(quantity,1) - COALESCE(returned_quantity,0)) as total')->groupBy('asset_id')->pluck('total', 'asset_id');
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
