<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\KategoriTiket;

class TiketController extends Controller
{
    public function TiketOpen()
    {
        $customer = Customer::with('status')->paginate(10);
        return view('Helpdesk.tiket-open-pelanggan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
        ]);
    }

    public function formOpenTiket($id)
    {
        $customer = Customer::with('router', 'paket', 'lokasi', 'lokasi.odc')->findOrFail($id);
        $nama_odc = optional($customer->lokasi->odc->first())->nama_odc ?? '-';
        $nama_odp = optional(optional($customer->lokasi->odc->first())->odp->first())->nama_odp ?? '-';

        $kategori = KategoriTiket::all();

        return view('Helpdesk.tiket.open-tiket',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'customer' => $customer,
            'nama_odc' => $nama_odc,
            'nama_odp' => $nama_odp,
            'kategori' => $kategori,
        ]);
    }

}
