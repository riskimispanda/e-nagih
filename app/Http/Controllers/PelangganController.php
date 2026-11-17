<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Models
use App\Models\Customer;
use App\Models\Dismantle;
use App\Models\ModemDetail;
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
    $pelanggan = Customer::with(['paket', 'tiket', 'tiket.teknisi', 'tiket.user'])
      ->onlyTrashed()
      ->orderBy('updated_at', 'desc')
      ->get();

    return view('/pelanggan/pelanggan-dismantle', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'pelanggan' => $pelanggan
    ]);
  }

  /**
   * Mengembalikan pelanggan dari status dismantle (soft delete) ke daftar pelanggan aktif.
   */
  public function restoreCustomer(Request $request, $id)
  {
    try {
      $outcome = DB::transaction(function () use ($id) {
        $customer = Customer::onlyTrashed()->lockForUpdate()->find($id);

        if (!$customer) {
          $activeCustomer = Customer::find($id);
          if ($activeCustomer) {
            return [
              'status_code' => 409,
              'success' => false,
              'message' => 'Pelanggan sudah aktif atau sudah di-restore sebelumnya.',
            ];
          }

          return [
            'status_code' => 404,
            'success' => false,
            'message' => 'Data pelanggan dismantle tidak ditemukan.',
          ];
        }

        // Memulihkan deleted_at sekaligus memicu event Customer::restored (kurangiStokPerangkat)
        $customer->restore();

        // Tiket open/deaktivasi sebelumnya mengubah status_id ke 4; kembalikan ke Aktif (3)
        if ($customer->status_id === null || in_array((int) $customer->status_id, [0, 1, 2, 4, 5], true)) {
          $customer->status_id = 3;
          $customer->save();
        }

        // Fallback: jika ModemDetail tidak cocok lewat seri/MAC di Customer, cek riwayat Dismantle terakhir
        $modemTerpakai = ModemDetail::where('customer_id', $customer->id)
          ->where('status_id', 13)
          ->exists();

        if (!$modemTerpakai) {
          $lastDismantle = Dismantle::where('customer_id_lama', $customer->id)
            ->whereNotNull('modem_detail_id')
            ->latest('id')
            ->first();

          if ($lastDismantle) {
            $modem = ModemDetail::where('id', $lastDismantle->modem_detail_id)
              ->whereIn('status_id', [4, 14])
              ->whereNull('customer_id')
              ->lockForUpdate()
              ->first();

            if ($modem) {
              $modem->update([
                'status_id' => 13,
                'customer_id' => $customer->id,
                'tanggal_terpakai' => now(),
              ]);
              $modemTerpakai = true;
            }
          }
        }

        activity('Restore Pelanggan')
          ->performedOn($customer)
          ->causedBy(auth()->user())
          ->log(auth()->user()->name . " Melakukan restore pelanggan {$customer->nama_customer}");

        $message = $modemTerpakai
          ? "Pelanggan {$customer->nama_customer} berhasil di-restore ke daftar aktif dan perangkat modem dikaitkan kembali."
          : "Pelanggan {$customer->nama_customer} berhasil di-restore ke daftar aktif.";

        return [
          'status_code' => 200,
          'success' => true,
          'message' => $message,
          'modem_restored' => $modemTerpakai,
        ];
      });

      if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
          'success' => $outcome['success'],
          'message' => $outcome['message'],
          'modem_restored' => $outcome['modem_restored'] ?? false,
        ], $outcome['status_code']);
      }

      return redirect()->back()->with(
        $outcome['success'] ? 'success' : 'error',
        $outcome['message']
      );
    } catch (\Throwable $e) {
      Log::error('Gagal melakukan restore pelanggan dismantle', [
        'customer_id' => $id,
        'error' => $e->getMessage(),
      ]);

      $message = 'Terjadi kesalahan saat melakukan restore pelanggan.';

      if ($request->expectsJson() || $request->ajax()) {
        return response()->json([
          'success' => false,
          'message' => $message,
        ], 500);
      }

      return redirect()->back()->with('error', $message);
    }
  }
}
