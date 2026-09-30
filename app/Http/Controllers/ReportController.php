<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\AssetOutflow;
use App\Models\BhpTransaction;
use Illuminate\View\View;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function assetData(Request $request): View
    {
        return $this->asset($request, null, 'lengkap', 'Laporan Data Aset', 'reports.asset.data');
    }

    public function assetRekap(Request $request): View
    {
        return $this->asset($request, null, 'rekap', 'Laporan Rekap Aset', 'reports.asset.rekap');
    }

    public function assetKib(Request $request, string $kib): View
    {
        $kib = strtoupper($kib);
        abort_unless(in_array($kib, ['A', 'B', 'C', 'D', 'E'], true), 404);

        return $this->asset($request, $kib, 'lengkap', 'Laporan KIB '.$kib, 'reports.asset.kib', $kib);
    }

    public function assetMutasi(Request $request): View
    {
        $filter = self::filter($request);
        $rows = collect();
        $summary = null;
        if ($filter) {
            $q = AssetOutflow::with('asset')->whereBetween('outflow_date', [$filter['from'], $filter['to']])->orderBy('outflow_date');
            if ($filter['view'] === 'lengkap') {
                $rows = $q->get();
            } else {
                $all = $q->get();
                $summary = [
                    'total' => $all->count(),
                    'per_type' => $all->groupBy('location_type')->map(fn ($g) => $g->count())->all(),
                ];
            }
        }

        return view('reports.asset', [
            'title' => 'Laporan Mutasi Aset',
            'action' => route('reports.asset.mutasi'),
            'filter' => $filter,
            'defaultView' => 'lengkap',
            'kind' => 'mutasi',
            'kib' => null,
            'rows' => $rows,
            'summary' => $summary,
            'yearNow' => (int) date('Y'),
        ]);
    }

    public function bhp(Request $request): View
    {
        $filter = self::filter($request);
        $rows = collect();
        $summary = null;
        if ($filter) {
            $q = BhpTransaction::with(['item', 'location'])->whereBetween('transaction_date', [$filter['from'], $filter['to']])->orderBy('transaction_date');
            if ($filter['view'] === 'lengkap') {
                $rows = $q->get();
            } else {
                $all = $q->get();
                $summary = [
                    'total_in' => $all->where('type', 'in')->sum('quantity'),
                    'total_out' => $all->where('type', 'out')->sum('quantity'),
                    'per_item' => $all->groupBy('bhp_item_id')->map(fn ($g) => [
                        'name' => $g->first()->item?->name ?? '-',
                        'in' => $g->where('type', 'in')->sum('quantity'),
                        'out' => $g->where('type', 'out')->sum('quantity'),
                    ])->values()->all(),
                ];
            }
        }

        return view('reports.bhp', [
            'filter' => $filter,
            'rows' => $rows,
            'summary' => $summary,
            'yearNow' => (int) date('Y'),
        ]);
    }

    private function asset(Request $request, ?string $kib, string $defaultView, string $title, string $route, ?string $kibParam = null): View
    {
        $filter = self::filter($request);
        $rows = collect();
        $summary = null;
        if ($filter) {
            $q = Asset::with('location')->whereBetween('acquisition_date', [$filter['from'], $filter['to']]);
            if ($kib) {
                $q->where('kib_type', $kib);
            }
            if ($filter['view'] === 'lengkap') {
                $rows = $q->orderBy('acquisition_date')->get();
            } else {
                $all = $q->orderBy('acquisition_date')->get();
                $summary = [
                    'total' => $all->count(),
                    'value' => (float) $all->sum('acquisition_value'),
                    'per_kib' => $all->groupBy('kib_type')->map(fn ($g) => ['count' => $g->count(), 'value' => (float) $g->sum('acquisition_value')])->all(),
                ];
            }
        }

        return view('reports.asset', [
            'title' => $title,
            'action' => $kibParam ? route($route, ['kib' => strtolower($kibParam)]) : route($route),
            'filter' => $filter,
            'defaultView' => $defaultView,
            'kind' => 'asset',
            'kib' => $kib,
            'rows' => $rows,
            'summary' => $summary,
            'yearNow' => (int) date('Y'),
        ]);
    }

    /** @return array{mode:string,from:string,to:string,year:?int,view:string}|null */
    private static function filter(Request $request): ?array
    {
        if (!$request->query('filter_mode')) {
            return null;
        }
        $data = $request->validate([
            'filter_mode' => 'required|in:tahun,periode',
            'view_type' => 'required|in:lengkap,rekap',
            'year' => 'required_if:filter_mode,tahun|nullable|integer|min:1990|max:'.((int) date('Y') + 1),
            'start_date' => 'required_if:filter_mode,periode|nullable|date',
            'end_date' => 'required_if:filter_mode,periode|nullable|date|after_or_equal:start_date',
        ]);
        if ($data['filter_mode'] === 'tahun') {
            $from = sprintf('%04d-01-01', $data['year']);
            $to = sprintf('%04d-12-31', $data['year']);
        } else {
            $from = $data['start_date'];
            $to = $data['end_date'];
        }

        return ['mode' => $data['filter_mode'], 'from' => $from, 'to' => $to, 'year' => $data['year'] ?? null, 'view' => $data['view_type']];
    }
}
