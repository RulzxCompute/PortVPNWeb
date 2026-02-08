<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RedeemCode;
use Illuminate\Http\Request;

class RedeemCodeController extends Controller
{
    public function index()
    {
        $codes = RedeemCode::with('user')
            ->latest()
            ->paginate(20);
        return view('admin.redeem-codes.index', compact('codes'));
    }

    public function create()
    {
        return view('admin.redeem-codes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:1000',
            'quantity' => 'required|integer|min:1|max:100',
            'expires_at' => 'nullable|date|after:now',
        ]);

        $codes = [];
        for ($i = 0; $i < $validated['quantity']; $i++) {
            $codes[] = [
                'code' => RedeemCode::generateCode(),
                'amount' => $validated['amount'],
                'expires_at' => $validated['expires_at'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        RedeemCode::insert($codes);

        return redirect()->route('admin.redeem-codes.index')
            ->with('success', "{$validated['quantity']} kode redeem berhasil dibuat.");
    }

    public function destroy(RedeemCode $redeemCode)
    {
        if ($redeemCode->is_used) {
            return back()->with('error', 'Kode sudah digunakan.');
        }

        $redeemCode->delete();
        return redirect()->route('admin.redeem-codes.index')
            ->with('success', 'Kode redeem berhasil dihapus.');
    }

    public function export()
    {
        $codes = RedeemCode::valid()
            ->select('code', 'amount', 'expires_at')
            ->get();

        $filename = 'redeem-codes-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($codes) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Kode', 'Nominal', 'Kadaluarsa']);
            foreach ($codes as $code) {
                fputcsv($file, [
                    $code->code,
                    $code->amount,
                    $code->expires_at ? $code->expires_at->format('Y-m-d H:i') : 'Tidak ada'
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
