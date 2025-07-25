<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Models\OrangTua;

class ForgotPasswordWAController extends Controller
{
    public function formLupaPassword() {
        return view('auth.wa-lupa-password');
    }

    public function kirimTokenWA(Request $request) {
    $request->validate([
        'no_hp' => 'required|numeric'
    ]);

    $no_hp_input = $request->no_hp;

    // Cari nomor yang disimpan dalam format 08 di database
    $orangTua = OrangTua::where('no_hp', $no_hp_input)->first();

    if (!$orangTua || !$orangTua->user) {
        return back()->withErrors(['no_hp' => 'Nomor WhatsApp tidak ditemukan di sistem.']);
    }

    // Simpan token ke session
    $token = rand(100000, 999999);

    Session::put('wa_reset_no_hp', $no_hp_input);
    Session::put('wa_reset_user_id', $orangTua->user->id);
    Session::put('wa_reset_token', $token);

    // Format nomor menjadi 628xx untuk dikirim ke API WhatsApp
    $no_hp_format_api = preg_replace('/^0/', '62', $no_hp_input);

    // Kirim ke WhatsApp menggunakan Fonnte
    Http::withHeaders([
        'Authorization' => 'xrb29R157HH8WQe7YgXS'
    ])->post('https://api.fonnte.com/send', [
        'target' => $no_hp_format_api,
        'message' => "Kode verifikasi untuk reset password Anda adalah *$token*.\nJangan berikan kepada siapa pun.",
    ]);

    return redirect()->route('wa.form.verifikasi')->with('success', 'Kode verifikasi telah dikirim ke WhatsApp Anda.');
}


    public function formVerifikasiToken() {
        return view('auth.wa-verifikasi-token');
    }

    public function prosesVerifikasiToken(Request $request) {
        $request->validate([
            'token' => 'required'
        ]);

        if ($request->token == Session::get('wa_reset_token')) {
            return view('auth.wa-password-baru');
        }

        return back()->withErrors(['token' => 'Kode token salah.']);
    }

   public function simpanPasswordBaru(Request $request) {
        $request->validate([
            'password' => 'required|min:6|confirmed'
        ]);

        $user = User::find(Session::get('wa_reset_user_id'));

        if (!$user) {
            return back()->withErrors(['password' => 'User tidak ditemukan.']);
        }

        $user->password = Hash::make($request->password);
        $user->save();

        Session::forget(['wa_reset_token', 'wa_reset_no_hp', 'wa_reset_user_id']);

        return redirect()->route('login')->with('ubahPassword', 'Password berhasil direset. Silakan login.');
    }


}
