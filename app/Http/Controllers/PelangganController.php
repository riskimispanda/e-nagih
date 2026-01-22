<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;

// Models
use App\Models\Customer;
use App\Models\TiketOpen;
use App\Models\Perusahaan;
use App\Models\User;

class PelangganController extends Controller
{
  public function corporate()
  {
    $teknisi = User::where('roles_id', 5)->get();
    $totalCorporate = Perusahaan::count();
    $aktif = Perusahaan::where('status_id', 3)->count();
    $pending = Perusahaan::where('status_id', 1)->count();
    $nonAktif = Perusahaan::where('status_id', 4)->count();

    return view('/data/data-corporate', [
      'users' => Auth::user(),
      'roles' => Auth::user()->roles,
      'teknisi' => $teknisi,
      'stats' => [
        'total' => $totalCorporate,
        'aktif' => $aktif,
        'pending' => $pending,
        'nonAktif' => $nonAktif
      ]
    ]);
  }

  public function corporateData()
  {
    $data = Perusahaan::with(['status', 'admin'])
      ->orderBy('created_at', 'desc')
      ->get();

    return response()->json([
      'data' => $data->map(function ($item) {
        return [
          'id' => $item->id,
          'id_formatted' => '#COR' . str_pad($item->id, 3, '0', STR_PAD_LEFT),
          'nama_perusahaan' => $item->nama_perusahaan,
          'bidang_usaha' => 'Corporate',
          'kontak' => $item->no_hp,
          'email' => '-',
          'status' => $item->status->nama_status ?? 'Unknown',
          'status_class' => $this->getStatusClass($item->status->nama_status ?? 'Unknown'),
          'tanggal' => $item->tanggal ?? $item->created_at->format('Y-m-d'),
        ];
      })
    ]);
  }

  private function getStatusClass($status)
  {
    switch ($status) {
      case 'Aktif':
        return 'green';
      case 'Pending':
        return 'yellow';
      case 'Non-Aktif':
        return 'red';
      default:
        return 'gray';
    }
  }

  public function storeCorporate(Request $request)
  {
    $request->validate([
      'nama_pic' => 'required|string|max:255',
      'nama_perusahaan' => 'required|string|max:255',
      'no_hp' => 'required|string|max:20',
      'gps' => 'required|string',
      'alamat' => 'required|string',
      'harga_real' => 'required|numeric',
      'teknisi' => 'required|exists:users,id',
      'paket' => 'required|string',
      'speed' => 'required|string',
      'tanggal' => 'required|date',
    ]);

    try {
      $corporate = Perusahaan::create([
        'nama_pic' => $request->nama_pic,
        'nama_perusahaan' => $request->nama_perusahaan,
        'no_hp' => $request->no_hp,
        'gps' => $request->gps,
        'alamat' => $request->alamat,
        'harga' => $request->harga_real,
        'user_id' => Auth::id(),
        'admin_id' => $request->teknisi,
        'paket' => $request->paket,
        'speed' => $request->speed,
        'status_id' => 1,
        'tanggal' => $request->tanggal,
      ]);

      if ($request->hasFile('foto')) {
        $fileName = time() . '_' . str_replace(' ', '_', $request->file('foto')->getClientOriginalName());
        $path = 'uploads/identitas';
        $request->file('foto')->move(public_path($path), $fileName);
        $corporate->foto = $path . '/' . $fileName;
        $corporate->save();
      }

      return response()->json([
        'success' => true,
        'message' => 'Data corporate berhasil ditambahkan!',
        'data' => $corporate
      ], 201);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menyimpan data: ' . $e->getMessage()
      ], 500);
    }
  }

  public function pelangganDismantle()
  {
    $pelanggan = Customer::with(['paket', 'tiket', 'tiket.teknisi'])
      ->onlyTrashed()
      ->orderBy('updated_at', 'desc')
      ->get();

    return view('/pelanggan/pelanggan-dismantle', [
      'users' => Auth::user(),
      'roles' => Auth::user()->roles,
      'pelanggan' => $pelanggan
    ]);
  }
}
