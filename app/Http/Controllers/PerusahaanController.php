<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\MediaKoneksi;
use App\Models\Perangkat;
use App\Models\Kas;
use App\Models\Pembayaran;
use App\Models\User;
use App\Models\Router;
use App\Models\InvoiceCorporate;
use Illuminate\Support\Facades\DB;
use App\Models\PerusahaanCorporate;


class PerusahaanController extends Controller
{
  //
  public function update(Request $request, $id)
  {
    // dd($request->all());
    $corp = Perusahaan::findOrFail($id);
    // dd($corp);
    $corp->ip_address = $request->ip;
    $corp->admin_id = $request->teknisi_id;
    $corp->status_id = 1;
    $corp->save();

    return redirect('/data/antrian-noc')->with('success', 'Berhasil di Prosess');
  }

  public function prosesCorp(Request $request, $id)
  {
    $corp = Perusahaan::findOrFail($id);
    // dd($corp);
    $media = MediaKoneksi::all();
    $dev = Perangkat::all();
    $router = Router::all();
    return view('teknisi.corp.proses-corp', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'corp' => $corp,
      'media' => $media,
      'dev' => $dev,
      'router' => $router
    ]);
  }

  public function confirm(Request $request, $id)
  {
    // Validasi input
    $validated = $request->validate([
      'perangkat' => 'required|string|max:255',
      'seri' => 'required|string|max:255',
      'mac' => 'required|string|max:17',
      'router' => 'required|exists:router,id',
      'foto_perangkat' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
      'foto_tempat' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
    ]);

    $corp = Perusahaan::findOrFail($id);

    // Upload foto helper
    $uploadFoto = function ($file, $folder) {
      $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
      $path = 'uploads/' . $folder;
      $file->move(public_path($path), $fileName);
      return $path . '/' . $fileName;
    };

    // Cek dan upload foto perangkat
    if ($request->hasFile('foto_perangkat')) {
      $corp->foto_perangkat = $uploadFoto($request->file('foto_perangkat'), 'foto_perangkat');
    }

    // Cek dan upload foto tempat
    if ($request->hasFile('foto_tempat')) {
      $corp->foto_tempat = $uploadFoto($request->file('foto_tempat'), 'foto_tempat');
    }

    // Update data perusahaan
    $corp->update([
      'perangkat' => $validated['perangkat'],
      'seri_perangkat' => $validated['seri'],
      'mac_address' => $validated['mac'],
      'router_id' => $validated['router'],
      'status_id' => 3
    ]);

    // Buat invoice dengan tanggal yang tepat
    InvoiceCorporate::create([
      'perusahaan_id' => $corp->id,
      'tagihan' => $corp->harga,
      'status_id' => 7, // Status unpaid
      'tanggal_invoice' => now(),
      'jatuh_tempo' => now()->addDays(30), // Jatuh tempo 30 hari dari sekarang
    ]);

    return redirect('/teknisi/antrian')->with('toast_success', 'Berhasil Dikonfirmasi');
  }

  public function pendapatan()
  {
    $corp = Perusahaan::where('status_id', 3)->sum('harga');
    $jumlah = Perusahaan::where('status_id', 3)->count();
    $agen = User::where('roles_id', 6)->count();

    $personal = Pembayaran::where('status_id', 8)->sum('jumlah_bayar');

    return view('keuangan.corp-pendapatan', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'corp' => $corp,
      'jumlah' => $jumlah,
      'personal' => $personal,
      'agen' => $agen,
    ]);
  }

  /**
   * Fetch data corporate untuk DataTables
   */
  public function dataCorporate(Request $request)
  {
    try {
      $query = Perusahaan::with(['status', 'admin', 'router'])->where('status_id', 3);

      // Filter berdasarkan status jika ada
      if ($request->has('status_id') && $request->status_id != '') {
        $query->where('status_id', $request->status_id);
      }

      // Search functionality
      if ($request->has('search') && $request->search['value'] != '') {
        $searchValue = $request->search['value'];
        $query->where(function ($q) use ($searchValue) {
          $q->where('nama_perusahaan', 'like', "%{$searchValue}%")
            ->orWhere('nama_pic', 'like', "%{$searchValue}%")
            ->orWhere('no_hp', 'like', "%{$searchValue}%")
            ->orWhere('alamat', 'like', "%{$searchValue}%");
        });
      }

      // Total records before filtering
      $totalRecords = Perusahaan::count();
      $filteredRecords = $query->count();

      // Ordering
      if ($request->has('order')) {
        $orderColumn = $request->order[0]['column'];
        $orderDir = $request->order[0]['dir'];

        $columns = ['id', 'nama_perusahaan', 'no_hp', 'alamat', 'status_id'];
        if (isset($columns[$orderColumn])) {
          $query->orderBy($columns[$orderColumn], $orderDir);
        }
      } else {
        $query->orderBy('created_at', 'desc');
      }

      // Pagination
      $start = $request->start ?? 0;
      $length = $request->length ?? 10;
      $data = $query->skip($start)->take($length)->get();

      // Format data untuk DataTables
      $formattedData = $data->map(function ($item, $index) use ($start) {
        return [
          'DT_RowIndex' => $start + $index + 1,
          'id' => $item->id,
          'nama_perusahaan' => $item->nama_perusahaan,
          'nama_pic' => $item->nama_pic,
          'no_hp' => $item->no_hp,
          'alamat' => $item->alamat,
          'status' => $item->status ? $item->status->nama_status : '-',
          'status_id' => $item->status_id,
          'admin' => $item->admin ? $item->admin->name : '-',
          'router' => $item->router ? $item->router->nama : '-',
          'harga' => $item->harga,
          'paket' => $item->paket,
          'speed' => $item->speed,
        ];
      });

      return response()->json([
        'draw' => intval($request->draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $formattedData
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'error' => true,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Fetch data invoice corporate untuk DataTables
   */
  public function dataInvoiceCorporate(Request $request)
  {
    try {
      $query = InvoiceCorporate::with(['perusahaan', 'status']);

      // Filter berdasarkan status jika ada
      if ($request->has('status_id') && $request->status_id != '') {
        $query->where('status_id', $request->status_id);
      }

      // Filter berdasarkan bulan jika ada
      if ($request->has('month') && $request->month != '') {
        $query->whereMonth('tanggal_invoice', $request->month);
      }

      // Filter berdasarkan tahun jika ada
      if ($request->has('year') && $request->year != '') {
        $query->whereYear('tanggal_invoice', $request->year);
      }

      // Search functionality
      if ($request->has('search') && $request->search['value'] != '') {
        $searchValue = $request->search['value'];
        $query->where(function ($q) use ($searchValue) {
          $q->where('invoice_number', 'like', "%{$searchValue}%")
            ->orWhereHas('perusahaan', function ($q2) use ($searchValue) {
              $q2->where('nama_perusahaan', 'like', "%{$searchValue}%");
            });
        });
      }

      // Total records before filtering
      $totalRecords = InvoiceCorporate::count();
      $filteredRecords = $query->count();

      // Ordering
      if ($request->has('order')) {
        $orderColumn = $request->order[0]['column'];
        $orderDir = $request->order[0]['dir'];

        $columns = ['id', 'invoice_number', 'perusahaan_id', 'tanggal_invoice', 'tagihan', 'status_id'];
        if (isset($columns[$orderColumn])) {
          $query->orderBy($columns[$orderColumn], $orderDir);
        }
      } else {
        $query->orderBy('created_at', 'desc');
      }

      // Pagination
      $start = $request->start ?? 0;
      $length = $request->length ?? 10;
      $data = $query->skip($start)->take($length)->get();

      // Format data untuk DataTables
      $formattedData = $data->map(function ($item, $index) use ($start) {
        return [
          'DT_RowIndex' => $start + $index + 1,
          'id' => $item->id,
          'invoice_number' => $item->invoice_number,
          'periode' => $item->tanggal_invoice ? $item->tanggal_invoice->translatedFormat('F Y') : '-',
          'perusahaan' => $item->perusahaan ? $item->perusahaan->nama_perusahaan : '-',
          'perusahaan_id' => $item->perusahaan_id,
          'tanggal_invoice' => $item->tanggal_invoice ? $item->tanggal_invoice->format('d M Y') : '-',
          'jatuh_tempo' => $item->jatuh_tempo ? $item->jatuh_tempo->format('d M Y') : '-',
          'tagihan' => $item->tagihan,
          'tagihan_formatted' => 'Rp ' . number_format($item->tagihan, 0, ',', '.'),
          'status' => $item->status ? $item->status->nama_status : '-',
          'status_id' => $item->status_id,
          'is_overdue' => $item->isOverdue(),
        ];
      });

      return response()->json([
        'draw' => intval($request->draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $formattedData
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'error' => true,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Get statistics untuk dashboard corporate
   */
  public function getStatistics()
  {
    try {
      $totalCorporate = Perusahaan::count();
      $activeCorporate = Perusahaan::where('status_id', 3)->count(); // Status aktif
      $inactiveCorporate = Perusahaan::whereNotIn('status_id', [3])->count();

      // Hitung pendapatan bulan ini dari tabel history pembayaran
      $revenueThisMonth = \App\Models\PerusahaanCorporate::whereMonth('tanggal_bayar', now()->month)
        ->whereYear('tanggal_bayar', now()->year)
        ->sum('jumlah_bayar');

      return response()->json([
        'total' => $totalCorporate,
        'active' => $activeCorporate,
        'inactive' => $inactiveCorporate,
        'revenue' => 'Rp ' . number_format($revenueThisMonth, 0, ',', '.')
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'error' => true,
        'message' => $e->getMessage()
      ], 500);
    }
  }

  /**
   * Konfirmasi pembayaran invoice corporate
   */
  public function confirmPayment(Request $request, $id)
  {
    try {
      $invoice = InvoiceCorporate::with('perusahaan')->findOrFail($id);

      // Validasi: pastikan invoice belum dibayar
      if ($invoice->status_id == 8) {
        return response()->json([
          'error' => true,
          'message' => 'Invoice ini sudah dikonfirmasi pembayarannya'
        ], 400);
      }

      // Validasi Input
      $request->validate([
        'tanggal_bayar' => 'required|date',
        'jumlah_bayar' => 'required|numeric',
        'metode_bayar' => 'required|string',
        'bukti_bayar' => 'nullable|image|mimes:jpeg,jpg,png|max:2048',
        'keterangan' => 'nullable|string'
      ]);

      // Handle Upload Bukti Bayar
      $buktiBayarPath = null;
      if ($request->hasFile('bukti_bayar')) {
        $file = $request->file('bukti_bayar');
        $fileName = time() . '_' . str_replace(' ', '_', $file->getClientOriginalName());
        $path = 'uploads/bukti_bayar_corporate';
        $file->move(public_path($path), $fileName);
        $buktiBayarPath = $path . '/' . $fileName;
      }

      DB::beginTransaction();

      try {
        // 1. Simpan Record Pembayaran di PerusahaanCorporate
        // Note: Asumsikan PerusahaanCorporate adalah tabel history pembayaran/mutasi saldo corporate
        PerusahaanCorporate::create([
          'invoice_corporate_id' => $invoice->id,
          'perusahaan_id' => $invoice->perusahaan_id,
          'user_id' => auth()->id(), // User yang mengkonfirmasi (admin/teknisi)
          'tanggal_bayar' => $request->tanggal_bayar,
          'jumlah_bayar' => $request->jumlah_bayar,
          'metode_bayar' => $request->metode_bayar,
          'bukti_bayar' => $buktiBayarPath,
          'keterangan' => $request->keterangan ?? 'Pembayaran Invoice ' . $invoice->invoice_number,
          'status_id' => 8 // Status Paid/Success (disesuaikan dengan logic status App)
        ]);

        // 2. Update Status Invoice menjadi Paid
        $invoice->status_id = 8;
        $invoice->save();

        // 3. Generate Invoice Bulan Depan
        // Ambil tanggal invoice sekarang
        $currentInvoiceDate = $invoice->tanggal_invoice;

        // Buat tanggal invoice bulan depan (pertahankan tanggal harinya)
        $nextInvoiceDate = $currentInvoiceDate->copy()->addMonth();
        $nextDueDate = $nextInvoiceDate->copy()->addDays(30);

        // Logic check: pastikan tidak ada double invoice untuk bulan depan (opsional tapi recommended)
        $existingNextInvoice = InvoiceCorporate::where('perusahaan_id', $invoice->perusahaan_id)
          ->whereMonth('tanggal_invoice', $nextInvoiceDate->month)
          ->whereYear('tanggal_invoice', $nextInvoiceDate->year)
          ->exists();

        $nextInvoiceInfo = null;

        if (!$existingNextInvoice) {

          $newInvoice = InvoiceCorporate::create([
            'invoice_number' => 'INV-CORP-' . time(), // Placeholder jika tidak auto
            'perusahaan_id' => $invoice->perusahaan_id,
            'tagihan' => $invoice->tagihan, // Tagihan sama dengan bulan ini (flat rate)
            'status_id' => 7, // Unpaid
            'tanggal_invoice' => $nextInvoiceDate,
            'jatuh_tempo' => $nextDueDate,
          ]);

          // Jika punya logic generate number yang lebih kompleks, bisa update $newInvoice->invoice_number disini

          $nextInvoiceInfo = 'Invoice bulan depan berhasil dibuat (Jatuh Tempo: ' . $nextDueDate->format('d M Y') . ')';
        }

        // Log Activity
        activity('Konfirmasi Pembayaran Invoice Corporate')
          ->causedBy(auth()->user()->id)
          ->log(auth()->user()->name . ' mengkonfirmasi pembayaran invoice ' . $invoice->invoice_number . ' sebesar Rp ' . number_format($request->jumlah_bayar) . ' via ' . $request->metode_bayar . '. ' . ($nextInvoiceInfo ?? ''));

        DB::commit();

        return response()->json([
          'success' => true,
          'message' => 'Pembayaran dikonfirmasi. ' . ($nextInvoiceInfo ?? ''),
          'data' => [
            'invoice_number' => $invoice->invoice_number,
            'perusahaan' => $invoice->perusahaan->nama_perusahaan,
            'status' => 'Paid'
          ]
        ]);

      } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
      }

    } catch (\Exception $e) {
      return response()->json([
        'error' => true,
        'message' => 'Terjadi kesalahan: ' . $e->getMessage()
      ], 500);
    }
  }


  /**
   * Halaman History Pembayaran Corporate
   */
  public function paymentPage()
  {
    return view('perusahaan.payment_perusahaan', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
    ]);
  }

  /**
   * Data JSON History Pembayaran Corporate untuk DataTable
   */
  public function dataPaymentCorporate(Request $request)
  {
    try {
      $query = PerusahaanCorporate::with(['perusahaan', 'user']);

      // Filter by Month & Year based on tanggal_bayar
      if ($request->has('month') && $request->month != '') {
        $query->whereMonth('tanggal_bayar', $request->month);
      }
      if ($request->has('year') && $request->year != '') {
        $query->whereYear('tanggal_bayar', $request->year);
      }

      // Search
      if ($request->has('search') && $request->search['value'] != '') {
        $searchValue = $request->search['value'];
        $query->where(function ($q) use ($searchValue) {
          $q->whereHas('perusahaan', function ($q2) use ($searchValue) {
            $q2->where('nama_perusahaan', 'like', "%{$searchValue}%");
          })
            ->orWhere('keterangan', 'like', "%{$searchValue}%");
        });
      }

      // Sorting
      if ($request->has('order')) {
        foreach ($request->order as $order) {
          $colIndex = $order['column'];
          $colName = $request->columns[$colIndex]['data'];
          $dir = $order['dir'];
          if ($colName != 'DT_RowIndex' && $colName != 'aksi') {
            $query->orderBy($colName, $dir);
          }
        }
      } else {
        $query->orderBy('tanggal_bayar', 'desc');
      }

      // Pagination
      $start = $request->start ?? 0;
      $length = $request->length ?? 10;
      $displayData = $query->skip($start)->take($length)->get();
      $totalRecords = PerusahaanCorporate::count();

      // Clone query for filtered count
      $countQuery = clone $query;
      $filteredRecords = $countQuery->count();

      $displayData = $query->skip($start)->take($length)->get();

      $data = $displayData->map(function ($item, $index) use ($start) {
        // Try to get invoice period from invoice relation if possible, else use payment date
        // Assuming invoice_corporate_id links to an invoice which has a date
        $periode = '-';
        if ($item->invoice_corporate_id) {
          // If there's a relation to invoice model (not currently loaded but we can try slightly different approach or just format date)
          // For now, let's format the payment date as the period "Month Year" or if we can access invoice date
          $periode = $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->translatedFormat('F Y') : '-';
        }

        return [
          'DT_RowIndex' => $start + $index + 1,
          'id' => $item->id,
          'invoice_number' => $periode, // Mapping 'periode' to this column as requested in UI
          'nama_perusahaan' => $item->perusahaan ? $item->perusahaan->nama_perusahaan : '-',
          'tanggal_bayar' => $item->tanggal_bayar ? \Carbon\Carbon::parse($item->tanggal_bayar)->translatedFormat('d F Y') : '-',
          'jumlah_bayar_formatted' => 'Rp ' . number_format($item->jumlah_bayar, 0, ',', '.'),
          'metode_bayar' => $item->metode_bayar,
          'status' => 'Lunas',
          'bukti_bayar' => $item->bukti_bayar,
          'keterangan' => $item->keterangan
        ];
      });

      return response()->json([
        'draw' => intval($request->draw),
        'recordsTotal' => $totalRecords,
        'recordsFiltered' => $filteredRecords,
        'data' => $data
      ]);

    } catch (\Exception $e) {
      return response()->json([
        'error' => true,
        'message' => $e->getMessage()
      ], 500);
    }
  }
}
