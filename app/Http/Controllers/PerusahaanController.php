<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Perusahaan;
use App\Models\MediaKoneksi;
use App\Models\Perangkat;
use App\Models\Kas;
use App\Models\Pembayaran;


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
        return view('teknisi.corp.proses-corp',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'corp' => $corp,
            'media' => $media,
            'dev' => $dev
        ]);
    }
    
    public function confirm(Request $request, $id)
    {
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
        $corp->redaman         = $request->redaman;
        $corp->kabel           = $request->kabel;
        $corp->perangkat_id    = $request->perangkat;
        $corp->media           = $request->media;
        $corp->server          = $request->olt;
        $corp->seri_perangkat  = $request->seri;
        $corp->mac_address     = $request->mac;
        
        $corp->save();
        
        return redirect('/teknisi/antrian')->with('toast_success', 'Berhasil Dikonfirmasi');
    }
    
    public function pendapatan()
    {
        $corp = Perusahaan::where('status_id', 3)->sum('harga');
        $jumlah = Perusahaan::where('status_id', 3)->count();

        $personal = Pembayaran::where('status_id', 8)->sum('jumlah_bayar');

        return view('keuangan.corp-pendapatan',[
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'corp' => $corp,
            'jumlah' => $jumlah,
            'personal' => $personal,
        ]);
    }
    
}
