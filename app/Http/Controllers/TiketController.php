<?php

namespace App\Http\Controllers;

// Models and Plugin
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\KategoriTiket;
use App\Models\TiketOpen;
use App\Models\Status;
use Spatie\Activitylog\Models\Activity;
use App\Services\ChatServices;
use App\Models\Router;
use App\Models\Paket;
use App\Services\MikrotikServices;
use Illuminate\Support\Facades\DB;
use App\Models\Invoice;
use App\Models\ModemDetail;
use Carbon\Carbon;
use App\Models\Perangkat;
use App\Exports\TiketExport;
use Maatwebsite\Excel\Facades\Excel;

// Class Controller
class TiketController extends Controller
{
  public function TiketOpen(Request $request)
  {
    $search = $request->get('search');
    $perPage = $request->get('per_page', 10);

    $query = Customer::with(['status', 'paket'])->whereIn('status_id', [3, 4, 9]);

    // Search filter
    if ($search) {
      $query->where(function ($q) use ($search) {
        $q->where('nama_customer', 'like', "%{$search}%")
          ->orWhere('alamat', 'like', "%{$search}%")
          ->orWhere('no_hp', 'like', "%{$search}%")
          ->orWhere('usersecret', 'like', "%{$search}%")
          ->orWhereHas('paket', function ($q) use ($search) {
            $q->where('nama_paket', 'like', "%{$search}%");
          })
          ->orWhereHas('status', function ($q) use ($search) {
            $q->where('nama_status', 'like', "%{$search}%");
          });
      });
    }

    // Handle "all" option
    if ($perPage === 'all') {
      $customer = $query->get();
    } else {
      $customer = $query->paginate($perPage)->appends([
        'search' => $search,
        'per_page' => $perPage
      ]);
    }

    $tiketAktif = TiketOpen::count();
    $tiketOpenAktif = TiketOpen::with('customer')->whereHas('customer', function ($q) {
      $q->whereNull('deleted_at');
    })->where('status_id', 6)->count();
    $tiketClosed = TiketOpen::with('customer')->whereHas('customer', function ($q) {
      $q->whereNull('deleted_at');
    })->where('status_id', 3)->count();

    return view('Helpdesk.tiket-open-pelanggan', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'customer' => $customer,
      'search' => $search,
      'perPage' => $perPage,
      'tiketAktif' => $tiketAktif,
      'tiketOpenAktif' => $tiketOpenAktif,
      'tiketClosed' => $tiketClosed
    ]);
  }

  public function formOpenTiket($id)
  {
    $customer = Customer::with('router', 'paket', 'odp.odc.olt.server')->findOrFail($id);
    // dd($customer->odp->nama_odp ,$customer->odp->odc->nama_odc, $customer->odp->odc->olt->nama_lokasi, $customer->odp->odc->olt->server->lokasi_server);
    $kategori = KategoriTiket::all();

    return view('Helpdesk.tiket.open-tiket', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'customer' => $customer,
      'kategori' => $kategori,
    ]);
  }

  public function addTiketOpen(Request $request)
  {
    // dd($request->all());
    $user = auth()->user()->id;
    // dd($user);

    $karyawan = User::whereIn('roles_id', [4, 5])->get();
    $noc = User::where('roles_id', 4)->get();

    $foto = null;
    if ($request->hasFile('foto')) {
      $file = $request->file('foto');
      $extension = $file->getClientOriginalExtension();
      $fileName = time() . '.' . $extension;
      $file->move(public_path('uploads/tiket'), $fileName);
      $foto = 'uploads/tiket/' . $fileName;
    }

    $tiket = new TiketOpen();
    $tiket->customer_id = $request->customer_id;
    $tiket->kategori_id = $request->kategori;
    $tiket->keterangan = $request->keterangan;
    $tiket->foto = $foto;
    $tiket->user_id = $user;
    $tiket->status_id = 6;
    $tiket->save();
    $tiket->refresh();

    $customer = Customer::findOrFail($request->customer_id);
    $customer->update(['status_id' => 4]);

    if ($tiket->kategori_id == 4 || $tiket->kategori_id == 6 || $tiket->kategori_id == 7) {
      // Kirim Notif ke NOC
      $chat = new ChatServices();
      foreach ($noc as $kar) {
        $nomor = preg_replace('/[^0-9]/', '', $kar->no_hp);
        if (str_starts_with($nomor, '0')) {
          $nomor = '62' . substr($nomor, 1);
        }
        $chat->kirimNotifikasiTiketOpen($nomor, $kar, $tiket);
      }
    }

    if ($tiket->kategori_id == 1 || $tiket->kategori_id == 2 || $tiket->kategori_id == 4 || $tiket->kategori_id == 5) {
      // Kirim Notif ke Teknisi
      $chat = new ChatServices();
      foreach ($karyawan as $kar) {
        $nomor = preg_replace('/[^0-9]/', '', $kar->no_hp);
        if (str_starts_with($nomor, '0')) {
          $nomor = '62' . substr($nomor, 1);
        }
        $chat->kirimNotifikasiTiketOpen($nomor, $kar, $tiket);
      }
    }

    // Log Activity
    activity('tiket')
      ->performedOn($tiket)
      ->causedBy(auth()->user())
      ->log(auth()->user()->name . ' Membuka tiket untuk pelanggan ' . $customer->nama_customer);

    return redirect('/tiket-open')->with('success', 'Tiket Open Berhasil Ditambahkan');
  }

  public function closedTiket(Request $request)
  {
    // Filter untuk tabel "Tiket Proses"
    $searchProses = $request->get('search_proses');
    $monthProses = $request->get('month_proses');
    $kategoriProses = $request->get('kategori_proses');

    // Filter untuk tabel "Tiket Selesai"
    $searchSelesai = $request->get('search_selesai');
    $monthSelesai = $request->get('month_selesai');
    $kategoriSelesai = $request->get('kategori_selesai');

    // Filter untuk tabel "Tiket Dibatalkan"
    $searchBatal = $request->get('search_batal');
    $monthBatal = $request->get('month_batal');
    $kategoriBatal = $request->get('kategori_batal');

    // ID status "Dibatalkan" (dinamis berdasarkan nama status)
    $statusBatalId = Status::where('nama_status', 'Dibatalkan')->value('id');

    // QUERY UTAMA untuk tiket yang sedang diproses (status_id 6)
    $query = TiketOpen::with([
      'kategori',
      'user',
      'customer' => function ($query) {
        $query->withTrashed();
      }
    ])
      ->whereHas('customer', function ($query) {
        $query->whereIn('status_id', [3, 4, 9])
          ->whereNull('deleted_at');
      })
      ->where('status_id', 6)
      ->orderBy('created_at', 'desc');

    // CLONEQUERY untuk tiket yang sudah selesai (status_id 3 saja)
    $cloneQuery = TiketOpen::with([
      'kategori',
      'user',
      'customer' => function ($query) {
        $query->withTrashed();
      }
    ])
      ->whereHas('customer', function ($query) {
        $query->whereIn('status_id', [3, 4])
          ->withTrashed();
      })
      ->where('status_id', 3) // Hanya status_id 3 (selesai)
      ->orderBy('created_at', 'desc');

    // QUERY tiket yang dibatalkan (status Dibatalkan)
    $cancelQuery = TiketOpen::with([
      'kategori',
      'user',
      'cancelledBy',
      'customer' => function ($query) {
        $query->withTrashed();
      }
    ])
      ->whereHas('customer', function ($query) {
        $query->whereIn('status_id', [3, 4, 9])
          ->withTrashed();
      })
      ->where('status_id', $statusBatalId)
      ->orderBy('created_at', 'desc');

    // Filter search untuk QUERY UTAMA
    if ($searchProses) {
      $query->whereHas('customer', function ($q) use ($searchProses) {
        $q->where('nama_customer', 'like', "%{$searchProses}%")
          ->orWhere('alamat', 'like', "%{$searchProses}%")
          ->orWhere('no_hp', 'like', "%{$searchProses}%")
          ->orWhere('usersecret', 'like', "%{$searchProses}%");
      });
    }

    // Filter search untuk CLONEQUERY
    if ($searchSelesai) {
      $cloneQuery->whereHas('customer', function ($q) use ($searchSelesai) {
        $q->where('nama_customer', 'like', "%{$searchSelesai}%")
          ->orWhere('alamat', 'like', "%{$searchSelesai}%")
          ->orWhere('no_hp', 'like', "%{$searchSelesai}%")
          ->orWhere('usersecret', 'like', "%{$searchSelesai}%");
      });
    }

    // Filter search untuk CANCELQUERY
    if ($searchBatal) {
      $cancelQuery->whereHas('customer', function ($q) use ($searchBatal) {
        $q->where('nama_customer', 'like', "%{$searchBatal}%")
          ->orWhere('alamat', 'like', "%{$searchBatal}%")
          ->orWhere('no_hp', 'like', "%{$searchBatal}%")
          ->orWhere('usersecret', 'like', "%{$searchBatal}%");
      });
    }

    // Filter by month untuk QUERY UTAMA
    if ($monthProses && $monthProses != 'all') {
      $query->whereMonth('created_at', $monthProses);
    }

    // Filter by month untuk CLONEQUERY
    if ($monthSelesai && $monthSelesai != 'all') {
      $cloneQuery->whereMonth('created_at', $monthSelesai);
    }

    // Filter by month untuk CANCELQUERY
    if ($monthBatal && $monthBatal != 'all') {
      $cancelQuery->whereMonth('created_at', $monthBatal);
    }

    // Filter by category untuk QUERY UTAMA
    if ($kategoriProses && $kategoriProses != 'all') {
      $query->where('kategori_id', $kategoriProses);
    }

    // Filter by category untuk CLONEQUERY
    if ($kategoriSelesai && $kategoriSelesai != 'all') {
      $cloneQuery->where('kategori_id', $kategoriSelesai);
    }

    // Filter by category untuk CANCELQUERY
    if ($kategoriBatal && $kategoriBatal != 'all') {
      $cancelQuery->where('kategori_id', $kategoriBatal);
    }

    $customer = $query->paginate(10, ['*'], 'proses_page')->appends($request->except(['selesai_page', 'batal_page']));
    $completedTickets = $cloneQuery->paginate(10, ['*'], 'selesai_page')->appends($request->except(['proses_page', 'batal_page']));
    $cancelledTickets = $cancelQuery->paginate(10, ['*'], 'batal_page')->appends($request->except(['proses_page', 'selesai_page']));

    // Generate all months from January to December for the dropdown
    $months = [];
    for ($m = 1; $m <= 12; $m++) {
      $months[$m] = Carbon::create()->month($m)->translatedFormat('F');
    }

    $kategoriTiket = KategoriTiket::all();

    return view('Helpdesk.tiket.closed-tiket', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'customer' => $customer,
      'completedTickets' => $completedTickets,
      'cancelledTickets' => $cancelledTickets,
      'months' => $months,
      'kategoriTiket' => $kategoriTiket,
      'searchProses' => $searchProses,
      'selectedMonthProses' => $monthProses,
      'selectedKategoriProses' => $kategoriProses,
      'searchSelesai' => $searchSelesai,
      'selectedMonthSelesai' => $monthSelesai,
      'selectedKategoriSelesai' => $kategoriSelesai,
      'searchBatal' => $searchBatal,
      'selectedMonthBatal' => $monthBatal,
      'selectedKategoriBatal' => $kategoriBatal,
    ]);
  }

  public function exportTiketProses(Request $request)
  {
    $search = $request->get('search_proses');
    $month = $request->get('month_proses');
    $kategoriId = $request->get('kategori_proses');

    return Excel::download(new TiketExport('proses', $month, $kategoriId, $search), 'tiket_proses_export.xlsx');
  }

  public function exportTiketSelesai(Request $request)
  {
    $search = $request->get('search_selesai');
    $month = $request->get('month_selesai');
    $kategoriId = $request->get('kategori_selesai');

    return Excel::download(new TiketExport('selesai', $month, $kategoriId, $search), 'tiket_selesai_export.xlsx');
  }

  public function exportTiketBatal(Request $request)
  {
    $search = $request->get('search_batal');
    $month = $request->get('month_batal');
    $kategoriId = $request->get('kategori_batal');

    return Excel::download(new TiketExport('batal', $month, $kategoriId, $search), 'tiket_batal_export.xlsx');
  }

  public function cancelTiket(Request $request, $id)
  {
    $request->validate([
      'alasan_batal' => 'required|string|max:1000',
    ]);

    $tiket = TiketOpen::findOrFail($id);

    $statusBatal = Status::where('nama_status', 'Dibatalkan')->first();
    $statusBatalId = $statusBatal ? $statusBatal->id : null;

    $tiket->update([
      'status_id' => $statusBatalId ?: $tiket->status_id,
      'alasan_batal' => $request->alasan_batal,
      'cancelled_by' => auth()->user()->id,
      'cancelled_at' => now(),
    ]);

    $customer = Customer::where('id', $tiket->customer_id)->first();
    if ($customer) {
      // Kembalikan customer ke status aktif bila saat ini dalam proses tiket (4)
      $customer->update([
        'status_id' => $customer->status_id == 4 ? 3 : $customer->status_id,
      ]);
    }

    activity('Cancel Tiket')
      ->causedBy(auth()->user()->id)
      ->log(auth()->user()->name . ' Membatalkan tiket untuk pelanggan ' . ($customer->nama_customer ?? $tiket->customer_id) . '. Alasan: ' . $request->alasan_batal);

    if ($request->ajax() || $request->wantsJson()) {
      return response()->json(['success' => true, 'message' => 'Tiket Berhasil Dibatalkan']);
    }

    return redirect('/tiket-closed')->with('success', 'Tiket Berhasil Dibatalkan');
  }


  public function tutupTiket(Request $request, $id)
  {
    $tiket = TiketOpen::findOrFail($id);
    $kategori = TiketOpen::where('kategori_id', $tiket->kategori_id)->first();
    $router = Router::with('paket')->get();
    $paket = Paket::with('router')->get();
    $modemLama = ModemDetail::with('perangkat')->where('customer_id', $id)->first();
    // Hanya perangkat kategori Modem (nama_logistik mengandung 'modem')
    $perangkat = Perangkat::whereHas('kategori', function ($q) {
      $q->whereRaw('LOWER(nama_logistik) LIKE ?', ['%modem%']);
    })->get();
    $modemDetails = ModemDetail::with('perangkat')
      ->whereHas('perangkat', function ($q) {
        $q->whereHas('kategori', function ($sub) {
          $sub->whereRaw('LOWER(nama_logistik) LIKE ?', ['%modem%']);
        });
      })
      ->tersedia()
      ->get();

    return view('Helpdesk.tiket.confirm-closed-tiket', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'tiket' => $tiket,
      'kategori' => $kategori,
      'router' => $router,
      'paket' => $paket,
      'modemLama' => $modemLama,
      'perangkat' => $perangkat,
      'modemDetails' => $modemDetails
    ]);
  }

  public function getPaketByRouter($routerId)
  {
    $paket = Paket::where('router_id', $routerId)
      ->whereNot('nama_paket', 'ISOLIREBILLING')
      ->get(['id', 'nama_paket']);

    return response()->json($paket);
  }

  public function confirmClosedTiket(Request $request, $id)
  {
    $tiket = TiketOpen::findOrFail($id);

    DB::transaction(function () use ($request, $tiket) {
      $customer = Customer::findOrFail($tiket->customer_id);

      $tiket->update([
        'status_id' => 3,
        'teknisi_id' => auth()->user()->id,
        'tanggal_selesai' => Carbon::now()->toDate()
      ]);

      // Jika router berbeda → tambah PPP secret di router baru
      if ($request->router != $customer->router_id) {
        $newRouter = Router::findOrFail($request->router);
        $customer->update([
          'router_id' => $request->router,
          'paket_id' => $request->paket,
          'status_id' => 3,
          'usersecret' => $request->usersecret ?: $customer->usersecret,
          'pass_secret' => $request->pass_secret ?: $customer->pass_secret,
          'local_address' => $request->local_address,
          'remote_address' => $request->remote_address,
          'remote' => $request->remote_address
        ]);
        $customer->refresh();

        MikrotikServices::addPPPSecret(
          MikrotikServices::connect($newRouter),
          [
            'name' => $customer->usersecret ?: $customer->usersecret,
            'password' => $customer->pass_secret ?: $customer->pass_secret,
            'remoteAddress' => $request->remote_address,
            'localAddress' => $request->local_address,
            'profile' => $customer->paket->paket_name,
            'service' => strtolower($customer->koneksi->nama_koneksi)
          ]
        );
      } else {
        // Router sama → cukup update profil
        $customer->update([
          'router_id' => $request->router,
          'paket_id' => $request->paket,
          'status_id' => 3,
          'usersecret' => $request->usersecret ?: $customer->usersecret,
          'pass_secret' => $request->pass_secret ?: $customer->pass_secret,
          'local_address' => $request->local_address,
          'remote_address' => $request->remote_address,
          'remote' => $request->remote_address
        ]);
        $customer->refresh();
        $client = MikrotikServices::connect(Router::findOrFail($customer->router_id));
        MikrotikServices::UpgradeDowngrade(
          $client,
          $customer->usersecret,
          $customer->paket->paket_name,
          $customer->local_address,
          $customer->remote_address,
        );
        $disconnectResult = MikrotikServices::removeActiveConnections($client, $customer->usersecret);
      }

      // Update invoice sekali saja
      $invoice = Invoice::where('customer_id', $customer->id)->latest()->first();
      if ($invoice) {
        $invoice->update([
          'paket_id' => $customer->paket_id,
          'tagihan' => $customer->paket->harga,
        ]);
      }
      activity('NOC')
        ->causedBy(auth()->user()->id)
        ->log(auth()->user()->name . ' Update Paket Pelanggan ' . $customer->nama_customer . ' ke Paket ' . $customer->paket->nama_paket);
    });

    return redirect('/tiket-closed')->with('success', 'Tiket Closed Berhasil Ditutup');
  }

  public function confirmDeaktivasi(Request $request, $id)
  {
    DB::transaction(function () use ($request, $id) {
      $tiket = TiketOpen::findOrFail($id);
      $customer = Customer::where('id', $tiket->customer_id)->first();

      if (!$customer) {
        throw new \Exception("Customer tidak ditemukan");
      }

      // Upload & Simpan Foto Modem
      $foto = $tiket->foto;
      if ($request->hasFile('foto')) {
        $file = $request->file('foto');
        $extension = $file->getClientOriginalExtension();
        $fileName = time() . '.' . $extension;
        $file->move(public_path('uploads/tiket'), $fileName);
        $foto = 'uploads/tiket/' . $fileName;
      }

      // 1. Update tiket status
      $tiket->update([
        'status_id' => 3,
        'keterangan' => $request->keterangan,
        'foto' => $foto,
        'teknisi_id' => auth()->user()->id,
        'tanggal_selesai' => Carbon::now()->toDate()
      ]);

      // 2. Kembalikan perangkat ke stok (otomatis via model event)
      // Tidak perlu manual set perangkat_id = null karena sudah otomatis di model

      // 3. SOFT DELETE customer (bukan hard delete)
      $customerId = $customer->id;
      $customerNama = $customer->nama_customer;
      $perangkatId = $customer->perangkat_id;

      // Ambil data modem yang sedang terpakai SEBELUM customer di-soft-delete
      // (model event akan meng-null-kan customer_id pada ModemDetail)
      $modem = \App\Models\ModemDetail::with('perangkat')
        ->where('customer_id', $customerId)
        ->where('status_id', 13)
        ->first();

      $customer->delete(); // ✅ Sekarang ini SOFT DELETE karena model pakai SoftDeletes

      // 4. Catat barang yang ditarik ke tabel dismantles (arsip mandiri)
      $perangkatNama = $modem?->perangkat?->nama_perangkat
        ?? \App\Models\Perangkat::find($perangkatId)?->nama_perangkat
        ?? '-';

      \App\Models\Dismantle::create([
        'modem_detail_id' => $modem?->id,
        'perangkat_id' => $perangkatId,
        'serial_number' => $modem?->serial_number,
        'mac_address' => $modem?->mac_address,
        'customer_id_lama' => $customerId,
        'customer_nama' => $customerNama,
        'perangkat_nama' => $perangkatNama,
        'teknisi_id' => auth()->user()->id,
        'status_barang' => $request->input('status_modem', 4),
        'keterangan_dismantle' => $request->keterangan,
        'tanggal_dismantle' => \Carbon\Carbon::now(),
        'foto' => $foto,
      ]);

      // Log activity
      activity()
        ->causedBy(auth()->user()->id)
        ->log("Customer {$customerNama} dideaktivasi via tiket #{$tiket->id}");
    });

    return redirect('/tiket-closed')->with('success', 'Berhasil deaktivasi pelanggan dan perangkat dikembalikan ke stok');
  }

  public function confirmGangguan(Request $request, $id)
  {
    $tiket = TiketOpen::findOrFail($id);
    
    DB::beginTransaction();
    
    try {
      $customer = Customer::findOrFail($tiket->customer_id);

      $tiket->update([
        'keterangan' => $request->keterangan,
        'status_id' => 3,
        'tanggal_selesai' => $request->tanggal,
        'teknisi_id' => auth()->user()->id
      ]);
      $tiket->refresh();

      // Cek jika ada modem baru yang dipasang
      if ($request->modem_baru_id != null) {
        $mac = $request->mac_address;
        $modemBaru = $request->modem_baru_id;

        // Update data modem di Customer
        $customer->update([
          'status_id' => 3,
          'perangkat_id' => $modemBaru,
          'mac_address' => $mac,
        ]);

        // Cari modem lama customer yang berstatus Terpakai (13)
        $statusModemLama = $request->input('status_modem_lama', 4); // Default ke Maintenance (4)

        $modemLama = ModemDetail::with('perangkat')
          ->where('customer_id', $customer->id)
          ->where('status_id', 13)
          ->first();

        ModemDetail::where('customer_id', $customer->id)
          ->where('status_id', 13)
          ->update([
            'status_id' => $statusModemLama,
            'customer_id' => null
          ]);

        // Catat barang lama yang ditarik ke tabel dismantles (arsip mandiri)
        $perangkatNamaLama = $modemLama?->perangkat?->nama_perangkat
          ?? $customer->perangkat?->nama_perangkat
          ?? '-';
        \App\Models\Dismantle::create([
          'modem_detail_id' => $modemLama?->id,
          'perangkat_id' => $customer->perangkat_id,
          'serial_number' => $modemLama?->serial_number,
          'mac_address' => $modemLama?->mac_address,
          'customer_id_lama' => $customer->id,
          'customer_nama' => $customer->nama_customer,
          'perangkat_nama' => $perangkatNamaLama,
          'teknisi_id' => auth()->user()->id,
          'status_barang' => $statusModemLama,
          'keterangan_dismantle' => $request->keterangan,
          'tanggal_dismantle' => \Carbon\Carbon::parse($request->tanggal),
          'foto' => null,
        ]);

        // Jika pilih dari stok SN tersedia (modem_detail_id)
        if ($request->modem_detail_id) {
          $modemDetail = ModemDetail::findOrFail($request->modem_detail_id);
          $modemDetail->update([
            'status_id' => 13, // Terpakai
            'customer_id' => $customer->id,
            'tanggal_terpakai' => now(),
          ]);
          $customer->update([
            'seri_perangkat' => $modemDetail->serial_number,
          ]);
        } else {
          // Buat detail modem baru dengan status Terpakai (13)
          $sni = $request->sni;
          $customer->update([
            'seri_perangkat' => $sni,
          ]);
          ModemDetail::create([
            'logistik_id' => $modemBaru,
            'mac_address' => $mac,
            'serial_number' => $sni,
            'status_id' => 13, // Terpakai
            'customer_id' => $customer->id,
            'tanggal_terpakai' => now(),
          ]);
        }
      } else {
        // Jika tidak ganti modem, cukup update status customer saja
        $customer->update([
          'status_id' => 3
        ]);
      }

      DB::commit();

      // Catat Log
      activity('Closed Tiket')
        ->causedBy(auth()->user()->id)
        ->log(auth()->user()->name . ' Mengkonfirmasi tiket untuk pelanggan ' . $customer->nama_customer);

      return redirect('/tiket-closed')->with('success', 'Tiket Closed Berhasil Ditutup');
      
    } catch (\Exception $e) {
      DB::rollBack();
      \Log::error("Gagal menutup tiket gangguan: " . $e->getMessage());
      return back()->with('toast_error', 'Gagal menutup tiket: ' . $e->getMessage());
    }
  }

  public function historyTiket($id)
  {
    // Asumsi $id adalah customer_id
    $customer = Customer::findOrFail($id);
    $tickets = TiketOpen::where('customer_id', $id)
      ->with(['kategori', 'user', 'cancelledBy'])
      ->orderBy('created_at', 'desc')
      ->paginate(10);

    return view('Helpdesk.tiket.history-tiket', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'customer' => $customer,
      'tickets' => $tickets,
    ]);
  }
}
