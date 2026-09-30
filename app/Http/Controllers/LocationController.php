<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\Location;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    private function xlsxDownload(Spreadsheet $s, string $name): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->streamDownload(function () use ($s) {
            (new Xlsx($s))->save('php://output');
        }, $name, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    public function export()
    {
        $rows = Location::with(['building', 'pic'])->orderBy('code')->get()->map(fn ($l) => [
            $l->code, $l->name, $l->building->code ?? '', $l->pic->email ?? '',
        ])->toArray();
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray([['kode', 'nama', 'kode_gedung', 'email_pic']], null, 'A1');
        if ($rows) $sh->fromArray($rows, null, 'A2');
        $sh->getStyle('A1:D1')->getFont()->setBold(true);
        foreach (range('A', 'D') as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        return $this->xlsxDownload($s, 'ruangan-'.date('Ymd-His').'.xlsx');
    }

    public function template()
    {
        $s = new Spreadsheet;
        $sh = $s->getActiveSheet();
        $sh->fromArray([
            ['kode', 'nama', 'kode_gedung', 'email_pic'],
            ['R-A-01', 'Ruang 01', 'GDG-A', ''],
            ['LAB-01', 'Lab IPA', 'GDG-B', 'guru@sekolah.id'],
        ], null, 'A1');
        $sh->getStyle('A1:D1')->getFont()->setBold(true);
        foreach (range('A', 'D') as $c) $sh->getColumnDimension($c)->setAutoSize(true);
        return $this->xlsxDownload($s, 'template-ruangan.xlsx');
    }

    public function importPreview(Request $request): JsonResponse
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv|max:10240']);
        $rows = IOFactory::load($request->file('file')->getRealPath())->getActiveSheet()->toArray();
        array_shift($rows);
        $rows = array_values(array_filter($rows, fn ($r) => trim((string) ($r[0] ?? '').($r[1] ?? '').($r[2] ?? '')) !== ''));
        $token = \Illuminate\Support\Str::random(32);
        \Illuminate\Support\Facades\Cache::put("imp-lok-$token", $rows, 600);
        return response()->json(['token' => $token, 'total' => count($rows)]);
    }

    public function importChunk(Request $request): JsonResponse
    {
        $data = $request->validate(['token' => 'required|string', 'offset' => 'required|integer|min:0', 'limit' => 'required|integer|min:1|max:500']);
        $rows = \Illuminate\Support\Facades\Cache::get("imp-lok-{$data['token']}", []);
        $slice = array_slice($rows, $data['offset'], $data['limit']);
        $ok = 0; $errors = [];
        foreach ($slice as $i => $r) {
            $no = $data['offset'] + $i + 2;
            $code = trim((string) ($r[0] ?? ''));
            $name = trim((string) ($r[1] ?? ''));
            $bcode = trim((string) ($r[2] ?? ''));
            if ($code === '' || $name === '' || $bcode === '') { $errors[] = "Baris $no: kode+nama+kode_gedung wajib."; continue; }
            $b = Building::where('code', $bcode)->first();
            if (! $b) { $errors[] = "Baris $no: gedung $bcode tak ada."; continue; }
            $email = trim((string) ($r[3] ?? ''));
            $pic = $email !== '' ? User::where('email', $email)->first() : null;
            if ($email !== '' && ! $pic) { $errors[] = "Baris $no: email PIC tak ada."; continue; }
            Location::updateOrCreate(['code' => $code], ['name' => $name, 'building_id' => $b->id, 'pic_user_id' => $pic?->id]);
            $ok++;
        }
        return response()->json(['ok' => $ok, 'errors' => $errors]);
    }
}
