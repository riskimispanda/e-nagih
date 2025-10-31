<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\TiketOpen;
use App\Models\User;
use Illuminate\Support\Carbon;

class KinerjaController extends Controller
{
    public function index()
    {
        // Customer Counting per Teknisi bulan
        $teknisi = User::where('roles_id', 5)->get()->map(function($item) {
            $item->customer_count = Customer::where('teknisi_id', $item->id)->whereMonth('tanggal_selesai', Carbon::now()->month)->count();
            $item->tiket_count = TiketOpen::where('teknisi_id', $item->id)->where('status_id', 3)->whereMonth('tanggal_selesai', Carbon::now()->month)->count();
            $item->tiket_count_year = TiketOpen::where('teknisi_id', $item->id)->where('status_id', 3)->whereYear('tanggal_selesai', Carbon::now()->year)->count();
            $item->customer_count_year = Customer::where('teknisi_id', $item->id)->whereYear('tanggal_selesai', Carbon::now()->year)->count();
            return $item;
        });

        return view('kinerja.kinerja-teknisi',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'teknisi' => $teknisi
        ]);
    }
}
