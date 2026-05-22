<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

// Models
use App\Models\Customer;
use App\Models\TiketOpen;
use App\Models\User;

class PelangganController extends Controller
{
  public function corporate()
  {
    return view('/data/data-corporate', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'teknisi' => User::where('roles_id', 5)->get()
    ]);
  }



  public function pelangganDismantle()
  {
    $pelanggan = Customer::with(['paket', 'tiket', 'tiket.teknisi'])
      ->onlyTrashed()
      ->orderBy('updated_at', 'desc')
      ->get();

    return view('/pelanggan/pelanggan-dismantle', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'pelanggan' => $pelanggan
    ]);
  }
}
