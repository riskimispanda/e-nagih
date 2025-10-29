<?php

namespace App\Http\Controllers;

// Models and Plugin
use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\User;
use App\Models\KategoriTiket;
use App\Models\TiketOpen;
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

// Class Controller
class TiketController extends Controller
{
    public function TiketOpen(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = Customer::with(['status', 'paket']);

        // Search filter
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%")
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
        $tiketOpenAktif = TiketOpen::where('status_id', 6)->count();
        $tiketClosed = TiketOpen::where('status_id', 3)->count();

        return view('Helpdesk.tiket-open-pelanggan',[
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

        return view('Helpdesk.tiket.open-tiket',[
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

        if ($tiket->kategori_id == 1 || $tiket->kategori_id == 2 || $tiket->kategori_id == 5) {
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
        $search = $request->get('search');
        $month = $request->get('month');
        $kategoriId = $request->get('kategori');

        // Condition
        if (auth()->user()->roles_id == 1 || auth()->user()->roles_id == 2 || auth()->user()->roles_id == 3 || auth()->user()->roles_id == 4) {
            $query = TiketOpen::with(['kategori', 'user', 'customer' => function ($query) {
                $query->withTrashed(); // ✅ Hanya untuk customer yang soft delete
            }])
                ->whereHas('customer', function ($query) {
                    $query->whereIn('status_id', [3, 4])
                        ->withTrashed(); // ✅ Juga untuk whereHas
                })
                ->whereIn('status_id', [3, 6])
                ->orderBy('created_at', 'desc');
        } elseif (auth()->user()->roles_id == 5) {
            $query = TiketOpen::with(['kategori', 'user', 'customer' => function ($query) {
                $query->withTrashed(); // ✅ Hanya untuk customer yang soft delete
            }])
                ->whereHas('kategori', function ($k) {
                    $k->whereIn('id', [1, 2, 3, 5]);
                })
                ->whereHas('customer', function ($query) {
                    $query->whereIn('status_id', [3, 4])
                        ->withTrashed(); // ✅ Juga untuk whereHas
                })
                ->whereIn('status_id', [3, 6])
                ->orderBy('created_at', 'desc');
        }

        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%")
                    ->orWhere('no_hp', 'like', "%{$search}%");
            });
        }

        // Filter by month if provided
        if ($month && $month != 'all') {
            $query->whereMonth('created_at', $month);
        }

        // Filter by category if provided
        if ($kategoriId && $kategoriId != 'all') {
            $query->where('kategori_id', $kategoriId);
        }

        $customer = $query->paginate(10)->appends($request->query());

        // Generate all months from January to December for the dropdown
        $months = [];
        for ($m = 1; $m <= 12; $m++) {
            $months[$m] = Carbon::create()->month($m)->translatedFormat('F');
        }

        $kategoriTiket = KategoriTiket::all();

        return view('Helpdesk.tiket.closed-tiket', [
            'users'    => auth()->user(),
            'roles'    => auth()->user()->roles,
            'customer' => $customer,
            'search' => $search,
            'months' => $months,
            'kategoriTiket' => $kategoriTiket,
            'selectedKategori' => $kategoriId,
            'selectedMonth' => $month,
        ]);
    }

    public function cancelTiket(Request $request, $id)
    {
        $tiket = TiketOpen::findOrFail($id);
        $tiket->update([
            'status_id' => 3
        ]);

        $customer = Customer::where('id', $tiket->customer_id)->first();
        $customer->update([
            'status_id' => 3
        ]);

        activity('Cancel Tiket')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Membatalkan tiket untuk pelanggan ' . $customer->nama_customer);

        return redirect('/tiket-closed')->with('success', 'Tiket Berhasil Dibatalkan');
    }


    public function tutupTiket(Request $request, $id)
    {
        $tiket = TiketOpen::findOrFail($id);
        $kategori = TiketOpen::where('kategori_id', $tiket->kategori_id)->first();
        $router = Router::with('paket')->get();
        $paket = Paket::with('router')->get();
        $modemLama = ModemDetail::with('perangkat')->where('customer_id', $id)->first();
        $perangkat = Perangkat::whereIn('kategori_id', [1, 4, 5])->get();

        return view('Helpdesk.tiket.confirm-closed-tiket',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'tiket' => $tiket,
            'kategori' => $kategori,
            'router' => $router,
            'paket' => $paket,
            'modemLama' => $modemLama,
            'perangkat' => $perangkat
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
                'teknisi_id' => auth()->user()->id,
                'tanggal_selesai' => Carbon::now()->toDate()
            ]);

            // Jika router berbeda → tambah PPP secret di router baru
            if ($request->router != $customer->router_id) {
                $newRouter = Router::findOrFail($request->router);
                $customer->update([
                    'router_id'   => $request->router,
                    'paket_id'    => $request->paket,
                    'status_id'   => 3,
                    'usersecret'  => $request->usersecret ?: $customer->usersecret,
                    'pass_secret' => $request->pass_secret ?: $customer->pass_secret,
                ]);
                $customer->refresh();

                MikrotikServices::addPPPSecret(
                    MikrotikServices::connect($newRouter),
                    [
                        'name'          => $customer->usersecret ?: $customer->usersecret,
                        'password'      => $customer->pass_secret ?: $customer->pass_secret,
                        'remoteAddress' => $request->remote_address,
                        'localAddress'  => $request->local_address,
                        'profile'       => $customer->paket->paket_name,
                        'service'       => strtolower($customer->koneksi->nama_koneksi)
                    ]
                );
            } else {
                // Router sama → cukup update profil
                $customer->update([
                    'router_id'   => $request->router,
                    'paket_id'    => $request->paket,
                    'status_id'   => 3,
                    'usersecret'  => $request->usersecret ?: $customer->usersecret,
                    'pass_secret' => $request->pass_secret ?: $customer->pass_secret,
                ]);
                $customer->refresh();

                MikrotikServices::UpgradeDowngrade(
                    MikrotikServices::connect(Router::findOrFail($customer->router_id)),
                    $customer->usersecret,
                    $customer->paket->paket_name
                );
            }

            // Update invoice sekali saja
            $invoice = Invoice::where('customer_id', $customer->id)->first();
            if ($invoice) {
                $invoice->update([
                    'paket_id' => $customer->paket_id,
                    'tagihan'  => $customer->paket->harga,
                ]);
            }
            activity('NOC')
                ->causedBy(auth()->user()->id)
                ->log(auth()->user()->name . ' Update Paket Pelanggan ' . $customer->nama_customer . ' ke Paket ' . $customer->paket->nama_paket);
        });

        $tiket->delete();

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

            // 1. Update tiket status
            $tiket->update([
                'status_id' => 3,
                'keterangan' => $request->keterangan,
                'teknisi_id' => auth()->user()->id,
                'tanggal_selesai' => Carbon::now()->toDate()
            ]);

            // 2. Kembalikan perangkat ke stok (otomatis via model event)
            // Tidak perlu manual set perangkat_id = null karena sudah otomatis di model

            // 3. SOFT DELETE customer (bukan hard delete)
            $customer->delete(); // ✅ Sekarang ini SOFT DELETE karena model pakai SoftDeletes

            // Log activity
            activity()
                ->causedBy(auth()->user()->id)
                ->log("Customer {$customer->nama_customer} dideaktivasi via tiket #{$tiket->id}");
        });

        return redirect('/tiket-closed')->with('success', 'Berhasil deaktivasi pelanggan dan perangkat dikembalikan ke stok');
    }

    public function confirmGangguan(Request $request, $id)
    {
        $tiket = TiketOpen::findOrFail($id);
        $tiket->update([
            'keterangan' => $request->keterangan,
            'status_id' => 3,
            'tanggal_selesai' => $request->tanggal,
            'teknisi_id' => auth()->user()->id
        ]);
        $tiket->refresh();

        // Validasi
        if ($request->modem_baru_id != null) {
            $mac = $request->mac_address;
            $sni = $request->sni;
            $modemBaru = $request->modem_baru_id;
        } else {
            $mac = $tiket->customer->mac_address;
            $sni = $tiket->customer->seri_perangkat;
            $modemBaru = $tiket->customer->perangkat_id;
        }

        $customer = Customer::where('id', $tiket->customer_id)->first();
        $customer->update([
            'status_id' => 3,
            'perangkat_id' => $modemBaru,
            'mac_address' => $mac,
            'seri_perangkat' => $sni
        ]);
        $customer->refresh();

        $modemDetail = ModemDetail::where('customer_id', $tiket->customer_id)->first();
        $modemDetail->update([
            'logistik_id' => $modemBaru,
            'mac_address' => $mac,
            'serial_number' => $sni
        ]);

        // Catat Log
        activity('Closed Tiket')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Mengkonfirmasi tiket untuk pelanggan ' . $customer->nama_customer);

        return redirect('/tiket-closed')->with('success', 'Tiket Closed Berhasil Ditutup');
    }

    public function historyTiket($id)
    {
        // Asumsi $id adalah customer_id
        $customer = Customer::findOrFail($id);
        $tickets = TiketOpen::where('customer_id', $id)
            ->with(['kategori', 'user'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('Helpdesk.tiket.history-tiket', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'tickets' => $tickets,
        ]);
    }
}
