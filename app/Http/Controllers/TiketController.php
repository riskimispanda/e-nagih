<?php

namespace App\Http\Controllers;

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
class TiketController extends Controller
{
    public function TiketOpen(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = Customer::with(['status', 'paket'])
            ->whereIn('status_id', [3, 4]);

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

        return view('Helpdesk.tiket-open-pelanggan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'search' => $search,
            'perPage' => $perPage,
            'tiketAktif' => $tiketAktif
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

        $customer = Customer::findOrFail($request->customer_id);
        $customer->update(['status_id' => 4]);

        // Kirim Notif
        $chat = new ChatServices();
        foreach ($karyawan as $kar) {
            $nomor = preg_replace('/[^0-9]/', '', $kar->no_hp);
            if (str_starts_with($nomor, '0')) {
                $nomor = '62' . substr($nomor, 1);
            }
            $chat->kirimNotifikasiTiketOpen($nomor, $kar, $tiket);
        }

        // Log Activity
        activity('tiket')
            ->performedOn($tiket)
            ->causedBy(auth()->user())
            ->log('Menambah Tiket Open');

        return redirect('/tiket-open')->with('success', 'Tiket Open Berhasil Ditambahkan');
    }

    public function closedTiket(Request $request)
    {
        $search = $request->get('search');

        $query = TiketOpen::with(['kategori', 'user', 'customer' => function ($query) {
            $query->withTrashed(); // ✅ Hanya untuk customer yang soft delete
        }])
            ->whereHas('customer', function ($query) {
            $query->where('status_id', 4)
                ->withTrashed(); // ✅ Juga untuk whereHas
        })
            ->whereIn('status_id', [3, 6]);

        if ($search) {
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('nama_customer', 'like', "%{$search}%")
                    ->orWhere('alamat', 'like', "%{$search}%");
            });
        }

        $customer = $query->paginate(10)->appends(['search' => $search]);

        return view('Helpdesk.tiket.closed-tiket', [
            'users'    => auth()->user(),
            'roles'    => auth()->user()->roles,
            'customer' => $customer,
            'search' => $search,
        ]);
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
                'tanggal_selesai' => Carbon::now()->toDate()
            ]);

            // 2. Kembalikan perangkat ke stok (otomatis via model event)
            // Tidak perlu manual set perangkat_id = null karena sudah otomatis di model

            // 3. SOFT DELETE customer (bukan hard delete)
            $customer->delete(); // ✅ Sekarang ini SOFT DELETE karena model pakai SoftDeletes

            // Log activity
            activity()
                ->performedOn($customer)
                ->log("Customer {$customer->nama_customer} dideaktivasi via tiket #{$tiket->id}");
        });

        return redirect('/tiket-closed')->with('success', 'Berhasil deaktivasi pelanggan dan perangkat dikembalikan ke stok');
    }
}
