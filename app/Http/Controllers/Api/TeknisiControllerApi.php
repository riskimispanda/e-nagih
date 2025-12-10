<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use Auth;
use Exception;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class TeknisiControllerApi extends Controller
{
  public function getCustomerQueue()
  {
    $month = Carbon::now()->month;
    $year = Carbon::now()->year;

    try {
      $customer = Customer::whereIn('status_id', [1, 5])
        ->whereMonth('created_at', $month)
        ->whereYear('created_at', $year)
        ->orderBy('created_at', 'desc')
        ->get();

      // Looping Customer
      $queueCustomer = $customer->map(function($queue){
          return [
            'id' => $queue->id,
            'name' => $queue->nama_customer,
            'agen' => $queue->agen?->name,
            'alamat' => $queue->alamat,
            'gps' => $queue->gps,
            'no_hp' => $queue->no_hp,
            'router' => $queue->router?->nama_router,
            'koneksi' => $queue->koneksi?->nama_koneksi,
            'paket' => $queue->paket?->nama_paket,
            'usersecret' => $queue->usersecret,
            'password' => $queue->pass_secret,
            'status' => $queue->status?->nama_status,
            'teknisi' => $queue->teknisi?->name,
            'localaddress' => $queue->local_address,
            'remoteaddress' => $queue->remote_address,
            'created_at' => Carbon::parse($queue->created_at)->locale('id')->translatedFormat('d-M-Y')
          ];
      });

      // Response api Json
      return response()->json([
        'success' => true,
        'data' => $queueCustomer,
        'count' => $queueCustomer->count()
      ], 200);

    } catch (Exception $e) {
      Log::error('Gagal mengambil data antrian customer', [
        'error' => $e->getMessage(),
        'line' => $e->getLine()
      ]);

      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data antrian customer',
        'error' => $e->getMessage()
      ], 500);
    }
  }
}
