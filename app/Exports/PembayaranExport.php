<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class PembayaranExport implements FromCollection, WithHeadings, WithStyles
{
    protected $filter;

    public function __construct($filter = 'harian')
    {
        $this->filter = $filter;
    }

    public function collection()
    {
        $query = Pembayaran::with(['invoice.customer', 'invoice.paket']);

        if ($this->filter === 'harian') {
            $query->whereDate('created_at', Carbon::today());
        } elseif ($this->filter === 'bulanan') {
            $query->whereMonth('created_at', Carbon::now()->month)
                  ->whereYear('created_at', Carbon::now()->year);
        }

        return $query->get()->map(function ($pembayaran) {
            return [
                'id' => $pembayaran->id,
                'nama_pelanggan' => optional($pembayaran->invoice->customer)->nama_customer,
                'paket' => optional($pembayaran->invoice->paket)->nama_paket,
                'jumlah_bayar' => 'Rp ' . number_format($pembayaran->jumlah_bayar, 0, ',', '.'),
                'tanggal_bayar' => $pembayaran->tanggal_bayar,
                'keterangan' => $pembayaran->keterangan,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Nama Pelanggan', 'Paket', 'Jumlah Bayar', 'Tanggal Pembayaran', 'Keterangan'];
    }

    public function styles(Worksheet $sheet)
    {
        // Jumlah baris data + header
        $lastRow = $sheet->getHighestRow();

        // Style header (baris 1)
        $sheet->getStyle('A1:F1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '000000'], // Hitam
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Style seluruh data termasuk keterangan (A1 sampai F terakhir)
        $sheet->getStyle("A1:F{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        // Auto height baris
        $sheet->getDefaultRowDimension()->setRowHeight(-1);
    }

}