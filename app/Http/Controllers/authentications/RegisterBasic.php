<?php

namespace App\Http\Controllers\authentications;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Models\User;

class RegisterBasic extends Controller
{
  public function index()
  {
    return view('content.authentications.auth-register-basic');
  }

  public function generateAll()
  {
    DB::beginTransaction();

    try {
      // Get customers tanpa user accounts
      $customers = Customer::where(function ($query) {
        $query->whereNull('user_id')
          ->orWhereNotExists(function ($subquery) {
            $subquery->select(DB::raw(1))
              ->from('users')
              ->whereColumn('users.id', 'customers.user_id');
          });
      })
        ->get();

      if ($customers->isEmpty()) {
        return redirect()->route('dashboard')
          ->with('info', 'Semua customer sudah memiliki akun user!');
      }

      $successCount = 0;
      $failedCount = 0;

      foreach ($customers as $customer) {
        try {
          // Reset user_id jika menunjuk ke user yang tidak ada
          if ($customer->user_id && !User::where('id', $customer->user_id)->exists()) {
            $customer->user_id = null;
          }

          // Gunakan nama_customer untuk name, generate email
          $name = $customer->nama_customer ?? 'Customer ' . $customer->id;
          $email = $customer->nama_customer . '@niscala.net';

          // Buat user dengan password default dan roles_id 8
          $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('password123'),
            'roles_id' => 8,
            'email_verified_at' => now(),
          ]);

          // Update customer dengan user_id
          $customer->update(['user_id' => $user->id]);

          $successCount++;
        } catch (\Exception $e) {
          $failedCount++;
          continue;
        }
      }

      DB::commit();

      return redirect()->route('dashboard')
        ->with('success', "Berhasil generate $successCount user dari " . $customers->count() . " customer!")
        ->with('failed_count', $failedCount);
    } catch (\Exception $e) {
      DB::rollBack();

      return redirect()->route('dashboard')
        ->with('error', 'Gagal generate users: ' . $e->getMessage());
    }
  }
}
