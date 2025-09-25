<?php

namespace App\Http\Controllers;

use App\Exports\CustomerAgen;
use Illuminate\Http\Request;
use App\Exports\ExportPelanggan;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Paket;

class ExportControllers extends Controller
{
    public function exportSemua()
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export Semua data pelanggan');
        return Excel::download(new ExportPelanggan, 'semua-pelanggan.xlsx');
    }

    public function exportAktif()
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan Aktif');
        return Excel::download(new ExportPelanggan('aktif'), 'pelanggan-aktif.xlsx');
    }

    public function exportNonAktif()
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan Non Aktif');
        return Excel::download(new ExportPelanggan('nonaktif'), 'pelanggan-nonaktif.xlsx');
    }

    public function exportPaket($id)
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan berdasarkan Paket');
        return Excel::download(new ExportPelanggan('paket', $id), 'pelanggan-paket-'.$id.'.xlsx');
    }

    public function exportRingkasanPaket()
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export Ringkasan Paket');
        return Excel::download(new ExportPelanggan('ringkasan'), 'ringkasan-per-paket.xlsx');
    }

    public function exportBulan($month, $year)
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan bulan ' . $month . ' Tahun ' . $year);
        return Excel::download(new ExportPelanggan('bulan', ['month' => $month, 'year' => $year]), 'pelanggan-' . $month . '-' . $year . '.xlsx');
    }

    public function unpaid()
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan Belum Bayar');
        return Excel::download(new CustomerAgen(), 'unpaid-' . now()->format('m-Y') . '.xlsx');
    }

    public function unpaidBulan($month, $year)
    {
        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan Belum Bayar ' . $month . ' Tahun ' . $year);
        return Excel::download(new CustomerAgen('bulan', ['month' => $month, 'year' => $year]), 'unpaid-' . $month . '-' . $year . '.xlsx');
    }

    public function unpaidRange(Request $request)
    {
        $start = $request->get('start_date');
        $end = $request->get('end_date');

        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan Belum Bayar Tanggal ' . $start . ' Sampai ' . $end);
        return Excel::download(
            new CustomerAgen('range', ['start' => $start, 'end' => $end]),
            'pelanggan-unpaid-' . $start . '-' . $end . '.xlsx'
        );
    }
}
