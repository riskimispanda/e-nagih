<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Activitylog\Models\Activity;
use Carbon\Carbon;

class LoginBasic extends Controller
{
  public function index()
  {
    return view('auth.login');
  }

  public function isolir()
  {
    return view('/isolir/isolir-page');
  }

  public function login(Request $request)
  {
    try {
      $credentials = $request->only('name', 'password');

      if (auth()->attempt($credentials)) {
        $user = auth()->user();
        if (auth()->user()->roles_id == 6) {
          activity('agen')
            ->performedOn($user)
            ->causedBy(auth()->user())
            ->log(auth()->user()->name . ' Login ke Dashboard');
          return redirect()->intended('/helpdesk/data-antrian')->with('toast_success', 'Selamat Datang di E-Nagih ' . auth()->user()->name);
        }
        activity('user')
          ->performedOn($user)
          ->causedBy(auth()->user())
          ->log(auth()->user()->name . ' Login ke Dashboard');
        return redirect()->intended('dashboard')->with('toast_success', 'Login successful! ' . auth()->user()->name);
      }

      // Log failed login attempt
      Log::warning('Percobaan login gagal', [
        'username' => $request->input('name'),
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent()
      ]);

      return redirect()->back()->with('toast_error', 'Username atau Password salah!');
    } catch (\Throwable $e) {
      // Log critical/error exceptions
      Log::error('Error pada proses login: ' . $e->getMessage(), [
        'username' => $request->input('name'),
        'ip_address' => $request->ip(),
        'exception' => $e
      ]);

      return redirect()->back()->with('toast_error', 'Terjadi kesalahan pada sistem. Silakan coba beberapa saat lagi.');
    }
  }

  public function logout()
  {
    activity('User')
      ->causedBy(auth()->user())
      ->log(auth()->user()->name . ' Logout Dari Sistem pada ' . Carbon::now('Asia/Jakarta')->locale('id')->isoFormat('dddd, D MMMM Y'));
    auth()->logout();
    return redirect('/')->with('toast_success', 'Logout successful!');
  }
}
