<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perangkat;

class Logistik extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $nama_perangkat = $request->input('nama_perangkat');
        $jumlah = (int)$request->input('jumlah_stok');
        $harga = (int) str_replace(['Rp', '.', ' '], '', $request->input('harga'));
        $total = $jumlah * $harga;
        // dd($total);
        $data = [
            'nama_perangkat' => $nama_perangkat,
            'jumlah_stok' => $jumlah,
            'harga' => $total,
        ];
        Perangkat::create($data);
        return redirect()->route('logistik');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
