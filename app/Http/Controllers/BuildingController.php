<?php

namespace App\Http\Controllers;

use App\Models\Building;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    private function xlsxDownload(Spreadsheet $s, string $name): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($s) {
            (new Xlsx($s))->save('php://output');
        }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function export()
    {
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray([['kode', 'nama', 'keterangan']], null, 'A1');
        $sh->fromArray(Building::orderBy('code')->get(['code', 'name', 'description'])->toArray(), null, 'A2');
        $sh->getStyle('A1:C1')->getFont()->setBold(true);
        foreach (range('A', 'C') as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        return $this->xlsxDownload($s, 'gedung-'.date('Ymd-His').'.xlsx');
    }

    public function template()
    {
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray([
            ['kode', 'nama', 'keterangan'],
            ['GDG-A', 'Gedung A', 'Gedung utama'],
            ['GDG-B', 'Gedung B', ''],
        ], null, 'A1');
        $sh->getStyle('A1:C1')->getFont()->setBold(true);
        foreach (range('A', 'C') as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        return $this->xlsxDownload($s, 'template-gedung.xlsx');
    }

    public function importPreview(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $rows = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet()->toArray();
        array_shift($rows); // header
        $rows = array_values(array_filter($rows, fn ($r) => trim((string) ($r[0] ?? '').($r[1] ?? '')) !== ''));
        $token = \Illuminate\Support\Str::random(32);
        \Illuminate\Support\Facades\Cache::put("imp-bdg-$token", $rows, 600);
        return response()->json(['token' => $token, 'total' => count($rows)]);
    }

    public function importChunk(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => 'required|string', 'offset' => 'required|integer|min:0', 'limit' => 'required|integer|min:1|max:500']);
        $rows = \Illuminate\Support\Facades\Cache::get("imp-bdg-{$data['token']}", []);
        $slice = array_slice($rows, $data['offset'], $data['limit']);
        $ok = 0; $errors = [];
        foreach ($slice as $i => $r) {
            $no = $data['offset'] + $i + 2;
            $code = trim((string) ($r[0] ?? ''));
            $name = trim((string) ($r[1] ?? ''));
            if ($code === '' || $name === '') { $errors[] = "Baris $no: kode+nama wajib."; continue; }
            if (strlen($code) > 50 || strlen($name) > 255) { $errors[] = "Baris $no: kode/nama kepanjangan."; continue; }
            Building::updateOrCreate(['code' => $code], ['name' => $name, 'description' => trim((string) ($r[2] ?? '')) ?: null]);
            $ok++;
        }
        return response()->json(['ok' => $ok, 'errors' => $errors]);
    }
}
