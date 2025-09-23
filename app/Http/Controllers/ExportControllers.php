<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\ExportPelanggan;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Paket;

class ExportControllers extends Controller
{
    public function exportSemua()
    {
        return Excel::download(new ExportPelanggan, 'semua-pelanggan.xlsx');
    }

    public function exportAktif()
    {
        return Excel::download(new ExportPelanggan('aktif'), 'pelanggan-aktif.xlsx');
    }

    public function exportNonAktif()
    {
        return Excel::download(new ExportPelanggan('nonaktif'), 'pelanggan-nonaktif.xlsx');
    }

    public function exportPaket($id)
    {
        return Excel::download(new ExportPelanggan('paket', $id), 'pelanggan-paket-'.$id.'.xlsx');
    }

    public function exportRingkasanPaket()
    {
        return Excel::download(new ExportPelanggan('ringkasan'), 'ringkasan-per-paket.xlsx');
    }
}
