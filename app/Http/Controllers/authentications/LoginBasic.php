<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Spatie\Activitylog\Models\Activity;

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
    $credentials = $request->only('name', 'password');
    
    if (auth()->attempt($credentials)) {
      $user = auth()->user();
      if (auth()->user()->roles_id == 6) {
        return redirect()->intended('/helpdesk/data-antrian')->with('toast_success', 'Selamat Datang di E-Nagih ' .
        auth()->user()->name);
      }
      activity('user')
      ->performedOn($user)
      ->causedBy(auth()->user())
      ->log('Login ke Dashboard');
      return redirect()->intended('dashboard')->with('toast_success', 'Login successful! ' . auth()->user()->name);
    }
    
    return redirect()->back()->with('toast_error', 'Username atau Password salah!');
  }
  
  public function logout()
  {
    auth()->logout();
    return redirect('/')->with('toast_success', 'Logout successful!');
  }
}
