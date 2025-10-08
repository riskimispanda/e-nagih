<?php

namespace App\Http\Controllers;

use App\Exports\CustomerAgen;
use App\Exports\CustomerBelumBayar;
use Illuminate\Http\Request;
use App\Exports\ExportPelanggan;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Paket;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
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
        $filename = 'semua-pelanggan-unpaid-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new CustomerBelumBayar('all'), $filename);
    }

    public function unpaidBulan($month, $year)
    {
        // Validasi input
        if (!is_numeric($month) || $month < 1 || $month > 12) {
            return back()->with('error', 'Bulan tidak valid');
        }

        if (!is_numeric($year) || $year < 2020 || $year > 2030) {
            return back()->with('error', 'Tahun tidak valid');
        }

        $monthName = $this->getMonthName($month);
        $filename = "pelanggan-unpaid-{$monthName}-{$year}.xlsx";

        activity('Export')
            ->causedBy(auth()->user()->id)
            ->log(auth()->user()->name . ' Melakukan Export data pelanggan Belum Bayar ' . $month . ' Tahun ' . $year);
        return Excel::download(new CustomerBelumBayar('bulan', [
            'month' => $month,
            'year' => $year
        ]), $filename);
    }

    public function unpaidRange(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);

        $startDate = $request->start_date;
        $endDate = $request->end_date;

        // Log untuk debugging
        Log::info('Export by Date Range Request', [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'user' => auth()->user()->name ?? 'Unknown'
        ]);

        // Format filename
        $startFormatted = Carbon::parse($startDate)->format('Y-m-d');
        $endFormatted = Carbon::parse($endDate)->format('Y-m-d');
        $filename = "pelanggan-unpaid-{$startFormatted}-to-{$endFormatted}.xlsx";

        return Excel::download(new CustomerBelumBayar('range', null, $startDate, $endDate), $filename);
    }

    private function getMonthName($month)
    {
        $months = [
            '01' => 'januari',
            '02' => 'februari',
            '03' => 'maret',
            '04' => 'april',
            '05' => 'mei',
            '06' => 'juni',
            '07' => 'juli',
            '08' => 'agustus',
            '09' => 'september',
            '10' => 'oktober',
            '11' => 'november',
            '12' => 'desember'
        ];

        return $months[str_pad($month, 2, '0', STR_PAD_LEFT)] ?? 'unknown';
    }
}
