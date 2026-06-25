<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Metode;
use App\Models\Pengeluaran;
use App\Models\JenisKas;
use App\Models\Kas;
use App\Models\Rab;
use Carbon\Carbon;
use Spatie\Activitylog\Models\Activity;
use App\Models\Pendapatan;
use App\Models\Pembayaran;
use App\Exports\PengeluaranExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PengeluaranController extends Controller
{
  public function index(Request $request)
  {
    $tahunSekarang = Carbon::now()->year;
    $bulanSekarang = Carbon::now()->month;
    
    // Get filter values from request, defaulting to current month and year
    $month = $request->input('month', $bulanSekarang);
    $year = $request->input('year', $tahunSekarang);

    $rab = Rab::all();
    $kas = JenisKas::all();
    $metodes = Metode::all();

    $pengeluaransQuery = Pengeluaran::with('user')
      ->orderBy('tanggal_pengeluaran', 'desc')
      ->orderBy('created_at', 'desc')
      ->where('status_id', 3)
      ->whereYear('tanggal_pengeluaran', $year);

    if ($month !== 'all') {
      $pengeluaransQuery->whereMonth('tanggal_pengeluaran', $month);
    }

    $pengeluarans = $pengeluaransQuery->paginate(10);

    $totalPengeluaran = Pengeluaran::where('status_id', 3)->sum('jumlah_pengeluaran');
    $dailyPengeluaran = Pengeluaran::where('status_id', 3)
      ->whereDate('tanggal_pengeluaran', Carbon::today())
      ->sum('jumlah_pengeluaran');

    $monthlyPengeluaranQuery = Pengeluaran::where('status_id', 3)->whereYear('tanggal_pengeluaran', $year);
    if ($month !== 'all') {
      $monthlyPengeluaranQuery->whereMonth('tanggal_pengeluaran', $month);
    }
    $monthlyPengeluaran = $monthlyPengeluaranQuery->sum('jumlah_pengeluaran');

    $totalRequest = Pengeluaran::where('status_id', 1)->count();

    $pembayaranQuery = Pembayaran::where('status_id', 8)->whereYear('tanggal_bayar', $year);
    if ($month !== 'all') {
      $pembayaranQuery->whereMonth('tanggal_bayar', $month);
    }
    $pendapatanLanggananPerBulan = $pembayaranQuery->sum('jumlah_bayar');

    $pendapatanQuery = Pendapatan::whereYear('tanggal', $year);
    if ($month !== 'all') {
      $pendapatanQuery->whereMonth('tanggal', $month);
    }
    $pendapatanNonLanggananPerBulan = $pendapatanQuery->sum('jumlah_pendapatan');

    $totalSaldoBulanIni = $pendapatanLanggananPerBulan + $pendapatanNonLanggananPerBulan - $monthlyPengeluaran;

    $totalPembayaran = Pembayaran::where('status_id', 8)->sum('jumlah_bayar');
    $totalPendapatan = Pendapatan::sum('jumlah_pendapatan');
    $totalSaldo = $totalPembayaran + $totalPendapatan - $totalPengeluaran;

    // Mengambil semua jenis pengeluaran yang unik untuk filter
    $kategoriPengeluaran = Pengeluaran::where('status_id', 3)
      ->whereNotNull('jenis_pengeluaran')
      ->where('jenis_pengeluaran', '!=', '')
      ->distinct()->pluck('jenis_pengeluaran');

    $namaBulan = [
      '1' => 'Januari',
      '2' => 'Februari',
      '3' => 'Maret',
      '4' => 'April',
      '5' => 'Mei',
      '6' => 'Juni',
      '7' => 'Juli',
      '8' => 'Agustus',
      '9' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember'
    ];

    if ($month !== 'all') {
      $bulanText = $namaBulan[$month] ?? '';
      $saldoLabel = 'Saldo Bulan Ini';
      $saldoSub = "Saldo bersih bulan $bulanText $year";
      $pengeluaranLabel = 'Bulan Ini';
      $pengeluaranSub = "Pengeluaran bulan $bulanText $year";
    } else {
      $saldoLabel = 'Saldo Tahun Ini';
      $saldoSub = "Saldo bersih tahun $year";
      $pengeluaranLabel = 'Tahun Ini';
      $pengeluaranSub = "Pengeluaran tahun $year";
    }

    return view('keuangan.pengeluaran', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'metodes' => $metodes,
      'pengeluarans' => $pengeluarans,
      'totalPengeluaran' => $totalPengeluaran,
      'dailyPengeluaran' => $dailyPengeluaran,
      'monthlyPengeluaran' => $monthlyPengeluaran,
      'kas' => $kas,
      'rab' => $rab,
      'totalRequest' => $totalRequest,
      'saldoBulanIni' => $totalSaldoBulanIni,
      'total' => $totalSaldo,
      'kategoriPengeluaran' => $kategoriPengeluaran,
      'saldoLabel' => $saldoLabel,
      'saldoSub' => $saldoSub,
      'pengeluaranLabel' => $pengeluaranLabel,
      'pengeluaranSub' => $pengeluaranSub
    ]);
  }

  public function tambahPengeluaran(Request $request)
  {
    $request->validate([
      'jumlahPengeluaran' => 'required|numeric|min:1',
      // tambahkan validasi lain sesuai kebutuhan
    ]);

    $rabId = $request->rab_id;
    $jumlah = $request->jumlahPengeluaran;

    // ✅ Cek jika ada RAB
    if ($rabId) {
      $rab = Rab::with('pengeluaran')->find($rabId);
      if (!$rab) {
        return back()->with('error', 'RAB tidak ditemukan.');
      }

      $totalRealisasi = $rab->pengeluaran->sum('jumlah_pengeluaran');
      $sisa = $rab->jumlah_anggaran - $totalRealisasi;
    }

    // ✅ Upload bukti pengeluaran
    if ($request->bukti_pengeluaran != null) {
      $path = $request->file('buktiPengeluaran')->getClientOriginalName();
      $request->file('buktiPengeluaran')->storeAs('public/uploads', $path);
    } else {
      $path = null;
    }

    // ✅ Simpan ke tabel pengeluaran
    $pengeluaran = new Pengeluaran();
    $pengeluaran->jumlah_pengeluaran = $jumlah;
    $pengeluaran->jenis_pengeluaran = $request->jenisPengeluaran;
    $pengeluaran->keterangan = $request->keterangan;
    $pengeluaran->metode_bayar = $request->metodePengeluaran;
    $pengeluaran->user_id = auth()->id();
    $pengeluaran->bukti_pengeluaran = $path;
    $pengeluaran->kas_id = $request->kas_id;
    $pengeluaran->tanggal_pengeluaran = $request->tanggalPengeluaran;
    $pengeluaran->status_id = 3;
    $pengeluaran->rab_id = $rabId;
    $pengeluaran->save();

    // ✅ Simpan ke kas
    $kas = new Kas();
    $kas->kredit = $jumlah;
    $kas->keterangan = $request->keterangan;
    $kas->tanggal_kas = $request->tanggalPengeluaran;
    $kas->kas_id = $request->kas_id;
    $kas->user_id = auth()->id();
    $kas->pengeluaran_id = $pengeluaran->id;
    $kas->status_id = 3;
    $kas->save();

    // ✅ Update status RAB kalau perlu
    if ($rabId && $sisa - $jumlah <= 0) {
      $rab->update([
        'status_id' => 11 // Misal: "Sudah Direalisasi Semua"
      ]);
    }

    // ✅ Log activity
    activity('pengeluaran')
      ->performedOn($pengeluaran)
      ->causedBy(auth()->user())
      ->log('Menambah Pengeluaran');

    return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil ditambahkan');
  }


  public function hapusPengeluaran(Request $request, $id)
  {
    $hapus = Pengeluaran::find($id);

    $kas = Kas::where('pengeluaran_id', $id);
    $rab = Rab::where('id', $hapus->rab_id);

    $hapus->update([
      'alasan' => $request->alasan,
      'status_id' => 1
    ]);

    $kas->update([
      'status_id' => 1
    ]);

    $rab->update([
      'status_id' => 1
    ]);

    // Log
    activity('Request Hapus Pengeluaran')
      ->causedBy(auth()->user())
      ->log(auth()->user()->name . ' Request Hapus Pengeluaran');

    return redirect()->back()->with('success', 'Pengeluaran berhasil direquest');
  }

  public function requestHapus()
  {
    $pengeluarans = Pengeluaran::with('user')
      ->where('status_id', 1)
      ->orderBy('created_at', 'desc')
      ->orderBy('tanggal_pengeluaran', 'desc')
      ->get();

    return view('keuangan.pengeluaran-acc', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'pengeluarans' => $pengeluarans,
    ]);
  }

  public function tolakHapus($id)
  {
    $hapus = Pengeluaran::find($id);

    $kas = Kas::where('pengeluaran_id', $id);

    if ($hapus) {
      $hapus->update([
        'status_id' => 3
      ]);

      $kas->update([
        'status_id' => 3
      ]);

      Rab::where('id', $hapus->rab_id)->update([
        'status_id' => 11
      ]);
      // Log
      activity('Tolak Hapus Pengeluaran')
        ->causedBy(auth()->user())
        ->log(auth()->user()->name . ' Tolak Hapus Pengeluaran');
      return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil ditolak');
    }

    return redirect('/pengeluaran/global')->with('error', 'Data pengeluaran tidak ditemukan');
  }

  public function konfirmasiHapus($id)
  {
    $hapus = Pengeluaran::find($id);

    if ($hapus) {
      $hapus->delete();
      Kas::where('pengeluaran_id', $id)->delete();
      Rab::where('id', $hapus->rab_id)->update([
        'status_id' => 12
      ]);
      // Log
      activity('Konrifmasi Hapus Pengeluaran')
        ->causedBy(auth()->user())
        ->log(auth()->user()->name . ' Mengkonfirmasi Hapus Pengeluaran');
      return redirect('/pengeluaran/global')->with('success', 'Pengeluaran berhasil dihapus');
    }

    return redirect('/pengeluaran/global')->with('error', 'Data pengeluaran tidak ditemukan');
  }

  public function editPengeluaran($id)
  {
    $pengeluaran = Pengeluaran::with('kas', 'rab')->findOrFail($id);
    $dataRab = Rab::all();
    $kas = JenisKas::all();

    return view('keuangan.edit-pengeluaran', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'pengeluaran' => $pengeluaran,
      'kas' => $kas,
      'data' => $dataRab
    ]);
  }


  public function updatePengeluaran(Request $request, $id)
  {
    $pengeluaran = Pengeluaran::findOrFail($id);
    // dd($pengeluaran);
    $pengeluaran->update([
      'tanggal_pengeluaran' => $request->tanggal,
      'jenis_pengeluaran' => $request->jenis_pengeluaran,
      'jumlah_pengeluaran' => $request->jumlah_pengeluaran,
      'kas_id' => $request->jenis_kas,
      'rab_id' => $request->rab_id,
      'keterangan' => $request->keterangan
    ]);

    // Log
    activity('Request Hapus Pengeluaran')
      ->causedBy(auth()->user())
      ->log(auth()->user()->name . ' melakukan Edit data pengeluaran');
    return redirect('/pengeluaran/global')->with('toast_success', 'Pengeluaran Berhasil diperbarui');
  }

  public function filterByMonth(Request $request)
  {
    $query = Pengeluaran::with('user')
      ->orderBy('tanggal_pengeluaran', 'desc')
      ->where('status_id', 3);

    $year = $request->year ?? date('Y');
    $query->whereYear('tanggal_pengeluaran', $year);

    if ($request->month != 'all') {
      $query->whereMonth('tanggal_pengeluaran', $request->month);
    }
    if ($request->kategori) {
      $query->where('jenis_pengeluaran', 'like', '%' . $request->kategori . '%');
    }
    if ($request->search) {
      $query->where(function ($q) use ($request) {
        $q->where('keterangan', 'like', '%' . $request->search . '%')
          ->orWhere('jenis_pengeluaran', 'like', '%' . $request->search . '%');
      });
    }

    $pengeluarans = $query->paginate(10);
    $pengeluarans->appends($request->all());

    if ($request->ajax()) {
      $view = view('keuangan.partials.pengeluaran-table', compact('pengeluarans'))->render();
      $pagination = view('pagination.bootstrap-5', ['paginator' => $pengeluarans])->render();

      $totalPengeluaran = Pengeluaran::where('status_id', 3)->sum('jumlah_pengeluaran');
      $dailyPengeluaran = Pengeluaran::where('status_id', 3)
        ->whereDate('tanggal_pengeluaran', Carbon::today())->sum('jumlah_pengeluaran');

      $month = $request->month ?? date('n');

      $monthlyPengeluaranQuery = Pengeluaran::where('status_id', 3)->whereYear('tanggal_pengeluaran', $year);
      if ($month !== 'all') {
        $monthlyPengeluaranQuery->whereMonth('tanggal_pengeluaran', $month);
      }
      $monthlyPengeluaran = $monthlyPengeluaranQuery->sum('jumlah_pengeluaran');

      return response()->json([
        'table' => $view,
        'pagination' => $pagination,
        'count' => $pengeluarans->count(),
        'total' => $pengeluarans->total(),
        'totalPengeluaran' => number_format($totalPengeluaran, 0, ',', '.'),
        'dailyPengeluaran' => number_format($dailyPengeluaran, 0, ',', '.'),
        'monthlyPengeluaran' => number_format($monthlyPengeluaran, 0, ',', '.')
      ]);
    }

    return view('keuangan.pengeluaran', compact('pengeluarans'));
  }

  public function ajaxFilter(Request $request)
  {
    $query = Pengeluaran::with('user')
      ->orderBy('tanggal_pengeluaran', 'desc')
      ->where('status_id', 3);

    // Filter berdasarkan tahun (default tahun sekarang)
    $year = $request->year ?? date('Y');
    $query->whereYear('tanggal_pengeluaran', $year);

    // Filter berdasarkan bulan jika bulan dipilih
    if ($request->month && $request->month != 'all') {
      $query->whereMonth('tanggal_pengeluaran', $request->month);
    }

    // Filter berdasarkan kategori
    if ($request->kategori) {
      $query->where('jenis_pengeluaran', 'like', '%' . $request->kategori . '%');
    }

    // Filter berdasarkan pencarian
    if ($request->search) {
      $query->where(function ($q) use ($request) {
        $q->where('keterangan', 'like', '%' . $request->search . '%')
          ->orWhere('jenis_pengeluaran', 'like', '%' . $request->search . '%');
      });
    }

    $pengeluarans = $query->paginate(10)->appends($request->except('page'));

    $totalPengeluaran = Pengeluaran::where('status_id', 3)->sum('jumlah_pengeluaran');
    $dailyPengeluaran = Pengeluaran::where('status_id', 3)
      ->whereDate('tanggal_pengeluaran', Carbon::today())
      ->sum('jumlah_pengeluaran');

    $month = $request->month ?? date('n');

    $monthlyPengeluaranQuery = Pengeluaran::where('status_id', 3)->whereYear('tanggal_pengeluaran', $year);
    if ($month !== 'all') {
      $monthlyPengeluaranQuery->whereMonth('tanggal_pengeluaran', $month);
    }
    $monthlyPengeluaran = $monthlyPengeluaranQuery->sum('jumlah_pengeluaran');

    $pembayaranQuery = Pembayaran::where('status_id', 8)->whereYear('tanggal_bayar', $year);
    if ($month !== 'all') {
      $pembayaranQuery->whereMonth('tanggal_bayar', $month);
    }
    $pendapatanLanggananPerBulan = $pembayaranQuery->sum('jumlah_bayar');

    $pendapatanQuery = Pendapatan::whereYear('tanggal', $year);
    if ($month !== 'all') {
      $pendapatanQuery->whereMonth('tanggal', $month);
    }
    $pendapatanNonLanggananPerBulan = $pendapatanQuery->sum('jumlah_pendapatan');

    $totalSaldoBulanIni = $pendapatanLanggananPerBulan + $pendapatanNonLanggananPerBulan - $monthlyPengeluaran;

    $totalPembayaran = Pembayaran::where('status_id', 8)->sum('jumlah_bayar');
    $totalPendapatan = Pendapatan::sum('jumlah_pendapatan');
    $totalSaldo = $totalPembayaran + $totalPendapatan - $totalPengeluaran;

    $namaBulan = [
      '1' => 'Januari',
      '2' => 'Februari',
      '3' => 'Maret',
      '4' => 'April',
      '5' => 'Mei',
      '6' => 'Juni',
      '7' => 'Juli',
      '8' => 'Agustus',
      '9' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember'
    ];

    if ($month !== 'all') {
      $bulanText = $namaBulan[$month] ?? '';
      $saldoLabel = 'Saldo Bulan Ini';
      $saldoSub = "Saldo bersih bulan $bulanText $year";
      $pengeluaranLabel = 'Bulan Ini';
      $pengeluaranSub = "Pengeluaran bulan $bulanText $year";
    } else {
      $saldoLabel = 'Saldo Tahun Ini';
      $saldoSub = "Saldo bersih tahun $year";
      $pengeluaranLabel = 'Tahun Ini';
      $pengeluaranSub = "Pengeluaran tahun $year";
    }

    $data = [
      'table' => view('keuangan.partials.pengeluaran-table', compact('pengeluarans'))->render(),
      'pagination' => $pengeluarans->links('pagination::bootstrap-5')->toHtml(),
      'total' => $pengeluarans->total(),
      'count' => $pengeluarans->count(),
      'totalPengeluaran' => number_format($totalPengeluaran, 0, ',', '.'),
      'dailyPengeluaran' => number_format($dailyPengeluaran, 0, ',', '.'),
      'monthlyPengeluaran' => number_format($monthlyPengeluaran, 0, ',', '.'),
      'totalSaldo' => number_format($totalSaldo, 0, ',', '.'),
      'saldoBulanIni' => number_format($totalSaldoBulanIni, 0, ',', '.'),
      'saldoLabel' => $saldoLabel,
      'saldoSub' => $saldoSub,
      'pengeluaranLabel' => $pengeluaranLabel,
      'pengeluaranSub' => $pengeluaranSub
    ];

    return response()->json($data);
  }

  public function exportAll(): BinaryFileResponse
  {
    // Increase memory limit and execution time
    ini_set('memory_limit', '512M');
    ini_set('max_execution_time', 300);

    $filename = 'pengeluaran-semua-data-' . date('Y-m-d-His') . '.xlsx';

    // Log
    activity('Export Pengeluaran')
      ->causedBy(auth()->user())
      ->log(auth()->user()->name . ' Melakukan export semua data pengeluaran');

    return Excel::download(new PengeluaranExport(), $filename, \Maatwebsite\Excel\Excel::XLSX, [
      'Cache-Control' => 'no-cache, no-store, must-revalidate',
      'Pragma' => 'no-cache',
      'Expires' => '0'
    ]);
  }

  /**
   * Export data pengeluaran berdasarkan bulan ke Excel dengan memory management
   */
  public function exportByMonth($month, $year = null): BinaryFileResponse
  {
    // Increase memory limit and execution time
    ini_set('memory_limit', '256M');
    ini_set('max_execution_time', 180);

    // Gunakan tahun yang diberikan atau tahun sekarang
    $year = $year ?? date('Y');

    $bulan = [
      '1' => 'Januari',
      '2' => 'Februari',
      '3' => 'Maret',
      '4' => 'April',
      '5' => 'Mei',
      '6' => 'Juni',
      '7' => 'Juli',
      '8' => 'Agustus',
      '9' => 'September',
      '10' => 'Oktober',
      '11' => 'November',
      '12' => 'Desember'
    ];

    $namaBulan = $bulan[$month] ?? 'Bulan';
    $filename = 'pengeluaran-' . strtolower($namaBulan) . '-' . $year . '.xlsx';

    // Log
    activity('Export Pengeluaran')
      ->causedBy(auth()->user())
      ->log(auth()->user()->name . ' Melakukan export data pengeluaran bulan: ' . $namaBulan . ' tahun: ' . $year);

    return Excel::download(new PengeluaranExport($month, $year), $filename, \Maatwebsite\Excel\Excel::XLSX, [
      'Cache-Control' => 'no-cache, no-store, must-revalidate',
      'Pragma' => 'no-cache',
      'Expires' => '0'
    ]);
  }
}
