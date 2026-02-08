<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\RedeemCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedeemController extends Controller
{
    public function showForm()
    {
        return view('user.redeem');
    }

    public function redeem(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:16',
        ]);

        $code = strtoupper(str_replace(' ', '', $request->code));
        $redeemCode = RedeemCode::where('code', $code)->first();

        if (!$redeemCode) {
            return back()->with('error', 'Kode redeem tidak valid.');
        }

        if (!$redeemCode->isValid()) {
            return back()->with('error', 'Kode redeem sudah digunakan atau kadaluarsa.');
        }

        $user = Auth::user();
        $amount = $redeemCode->amount;

        // Add balance
        $user->addBalance($amount);

        // Mark code as used
        $redeemCode->markAsUsed($user->id);

        // Create transaction
        $user->transactions()->create([
            'type' => 'deposit',
            'amount' => $amount,
            'description' => 'Redeem kode: ' . $redeemCode->code,
            'reference_type' => 'redeem_code',
            'reference_id' => $redeemCode->id,
            'balance_after' => $user->balance,
        ]);

        return back()->with('success', "Berhasil redeem! Saldo Rp " . number_format($amount) . " telah ditambahkan.");
    }
}
