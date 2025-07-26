<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\KategoriTiket;

class TiketController extends Controller
{
    public function TiketOpen()
    {
        $customer = Customer::with('status')->where('status_id', 3)->paginate(10);
        return view('Helpdesk.tiket-open-pelanggan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
        ]);
    }

    public function formOpenTiket($id)
    {
        $customer = Customer::with('router', 'paket', 'odp.odc.olt.server')->findOrFail($id);
        dd($customer->odp->odc->nama_odc, $customer->odp->odc->olt->nama_lokasi, $customer->odp->odc->olt->server->lokasi_server);
        $kategori = KategoriTiket::all();

        return view('Helpdesk.tiket.open-tiket',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'kategori' => $kategori,
        ]);
    }

}
