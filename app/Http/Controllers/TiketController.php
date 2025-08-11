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

class TiketController extends Controller
{
    public function TiketOpen()
    {
        $customer = Customer::with('status')->whereIn('status_id', [3, 4])->paginate(10);
        return view('Helpdesk.tiket-open-pelanggan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
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

        $karyawan = User::whereIn('roles_id', [1, 4, 5])->get();

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

    public function closedTiket()
    {
        $coba = TiketOpen::with(['kategori','customer','user'])
            ->whereHas('customer', function ($query) {
                $query->where('status_id', 4);
            })
            ->paginate(10);

        return view('Helpdesk.tiket.closed-tiket', [
            'users'    => auth()->user(),
            'roles'    => auth()->user()->roles,
            'customer' => $coba,
        ]);
    }


    public function tutupTiket(Request $request, $id)
    {
        $tiket = TiketOpen::findOrFail($id);
        $kategori = TiketOpen::where('kategori_id', $tiket->kategori_id)->first();
        $router = Router::with('paket')->get();
        $paket = Paket::with('router')->get();
        return view('/helpdesk/tiket/confirm-closed-tiket',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'tiket' => $tiket,
            'kategori' => $kategori,
            'router' => $router,
            'paket' => $paket,
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
        });

        $tiket->delete();

        return redirect('/tiket-closed')->with('success', 'Tiket Closed Berhasil Ditutup');
    }


}
