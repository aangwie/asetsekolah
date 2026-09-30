<?php

namespace App\Http\Controllers;

use App\Models\BhpItem;
use App\Models\BhpTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BhpTransactionController extends Controller
{
    public function indexMasuk(): View
    {
        return view('transactions.bhp_masuk_index', ['txs' => BhpTransaction::with('item')->where('type', 'in')->latest()->get()]);
    }

    public function createMasuk(): View
    {
        return view('transactions.bhp_masuk_form', [
            'yearNow' => (int) date('Y'),
            'txs' => BhpTransaction::with('item')->where('type', 'in')->latest()->get(),
        ]);
    }

    public function storeMasuk(Request $request): RedirectResponse
    {
        if (is_string($request->input('unit_price'))) {
            $request->merge(['unit_price' => str_replace('.', '', $request->input('unit_price'))]);
        }
        $data = self::validateMasuk($request);
        $code = 'BHP-'.$data['procurement_year'].'-'.strtoupper(Str::random(6));
        while (BhpItem::where('code', $code)->exists()) {
            $code = 'BHP-'.$data['procurement_year'].'-'.strtoupper(Str::random(6));
        }
        $item = BhpItem::create([
            'code' => $code, 'name' => $data['name'], 'category' => $data['category'],
            'unit' => 'pcs', 'initial_stock' => $data['quantity'], 'current_stock' => 0,
            'unit_price' => $data['unit_price'],
        ]);
        BhpTransaction::create([
            'bhp_item_id' => $item->id, 'user_id' => auth()->id(), 'type' => 'in',
            'quantity' => $data['quantity'], 'transaction_date' => $data['transaction_date'],
        ]);

        return redirect()->route('transactions.bhp.masuk.create')->with('ok', 'BHP masuk simpan.');
    }

    public function editMasuk(BhpTransaction $masuk): View
    {
        return view('transactions.bhp_masuk_form', [
            'yearNow' => (int) date('Y'),
            'txs' => BhpTransaction::with('item')->where('type', 'in')->latest()->get(),
            'tx' => $masuk->load('item'),
        ]);
    }

    public function updateMasuk(Request $request, BhpTransaction $masuk): RedirectResponse
    {
        if (is_string($request->input('unit_price'))) {
            $request->merge(['unit_price' => str_replace('.', '', $request->input('unit_price'))]);
        }
        $data = self::validateMasuk($request);
        $masuk->item->update(['name' => $data['name'], 'category' => $data['category'], 'unit_price' => $data['unit_price']]);
        $masuk->update(['transaction_date' => $data['transaction_date'], 'quantity' => $data['quantity']]);

        return redirect()->route('transactions.bhp.masuk.create')->with('ok', 'BHP masuk update.');
    }

    public function destroyMasuk(BhpTransaction $masuk): RedirectResponse
    {
        $masuk->delete();

        return back()->with('ok', 'BHP masuk hapus.');
    }

    public function indexKeluar(): View
    {
        return view('transactions.bhp_keluar_index', ['txs' => BhpTransaction::with(['item', 'location'])->where('type', 'out')->latest()->get()]);
    }

    public function createKeluar(): View
    {
        return view('transactions.bhp_keluar_form', [
            'items' => BhpItem::where('current_stock', '>', 0)->orderBy('name')->get(),
            'locations' => \App\Models\Location::orderBy('name')->get(),
            'txs' => BhpTransaction::with(['item', 'location'])->where('type', 'out')->latest()->get(),
        ]);
    }

    public function storeKeluar(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'bhp_item_id' => 'required|exists:bhp_items,id',
            'transaction_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'location_id' => 'required|exists:locations,id',
        ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($data) {
            $item = BhpItem::lockForUpdate()->findOrFail($data['bhp_item_id']);
            if ($data['quantity'] > (int) $item->current_stock) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi stok tersedia ('.$item->current_stock.').']);
            }
            BhpTransaction::create([
                'bhp_item_id' => $item->id, 'user_id' => auth()->id(), 'type' => 'out',
                'quantity' => $data['quantity'], 'transaction_date' => $data['transaction_date'],
                'location_id' => $data['location_id'],
            ]);
        });

        return redirect()->route('transactions.bhp.keluar.create')->with('ok', 'BHP keluar simpan.');
    }

    public function editKeluar(BhpTransaction $keluar): View
    {
        abort_if($keluar->type !== 'out', 404);
        $keluar->load(['item', 'location']);

        return view('transactions.bhp_keluar_form', [
            'items' => BhpItem::where('current_stock', '>', 0)->orWhere('id', $keluar->bhp_item_id)->orderBy('name')->get(),
            'locations' => \App\Models\Location::orderBy('name')->get(),
            'txs' => BhpTransaction::with(['item', 'location'])->where('type', 'out')->latest()->get(),
            'tx' => $keluar,
            'stockAvail' => (int) $keluar->item->current_stock + (int) $keluar->quantity,
        ]);
    }

    public function updateKeluar(Request $request, BhpTransaction $keluar): RedirectResponse
    {
        abort_if($keluar->type !== 'out', 404);
        $data = $request->validate([
            'transaction_date' => 'required|date',
            'quantity' => 'required|integer|min:1',
            'location_id' => 'required|exists:locations,id',
        ]);
        \Illuminate\Support\Facades\DB::transaction(function () use ($data, $keluar) {
            $item = BhpItem::lockForUpdate()->findOrFail($keluar->bhp_item_id);
            $max = (int) $item->current_stock + (int) $keluar->quantity;
            if ($data['quantity'] > $max) {
                throw ValidationException::withMessages(['quantity' => 'Jumlah melebihi stok tersedia ('.$max.').']);
            }
            $keluar->update($data);
        });

        return redirect()->route('transactions.bhp.keluar.create')->with('ok', 'BHP keluar update.');
    }

    public function destroyKeluar(BhpTransaction $keluar): RedirectResponse
    {
        abort_if($keluar->type !== 'out', 404);
        $keluar->delete();

        return back()->with('ok', 'BHP keluar hapus.');
    }

    private static function validateMasuk(Request $request): array
    {
        $data = $request->validate([
            'procurement_year' => 'required|integer|min:1990|max:'.((int) date('Y') + 1),
            'transaction_date' => 'required|date',
            'name' => 'required|string|max:255',
            'category' => 'required|in:ATK,Kebersihan,Kesehatan,Pemeliharaan,Lainnya',
            'unit_price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:1',
        ]);
        if ((int) date('Y', strtotime($data['transaction_date'])) !== (int) $data['procurement_year']) {
            throw ValidationException::withMessages(['transaction_date' => 'Tahun tanggal beda dengan Tahun Perolehan.']);
        }

        return $data;
    }
}
