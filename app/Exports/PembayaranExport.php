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
    protected $startDate;
    protected $endDate;

    public function __construct($filter = 'harian', $startDate = null, $endDate = null)
    {
        $this->filter = $filter;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Pembayaran::with(['invoice.customer', 'invoice.paket']);

        // Filter berdasarkan tipe (harian/bulanan) jika tidak ada date range
        if (!$this->startDate && !$this->endDate) {
            if ($this->filter === 'harian') {
                $query->whereDate('created_at', Carbon::today());
            } elseif ($this->filter === 'bulanan') {
                $query->whereMonth('created_at', Carbon::now()->month)
                    ->whereYear('created_at', Carbon::now()->year);
            }
        }

        // Filter berdasarkan date range jika ada
        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay()
            ]);
        }

        return $query->get()->map(function ($pembayaran) {
            $tanggalBayar = Carbon::parse($pembayaran->tanggal_bayar)
                ->locale('id') // set locale ke bahasa Indonesia
                ->translatedFormat('d F Y H:i:s');

            $periode = Carbon::parse($pembayaran->invoice->jatuh_tempo)
                ->locale('id') // set locale ke bahasa Indonesia
                ->translatedFormat('F Y');

            return [
                'id' => $pembayaran->id,
                'nama_pelanggan' => optional($pembayaran->invoice->customer)->nama_customer,
                'paket' => optional($pembayaran->invoice->paket)->nama_paket,
                'jumlah_bayar' => $pembayaran->jumlah_bayar,
                'tanggal_bayar' => $tanggalBayar,
                'keterangan' => $pembayaran->keterangan,
                'metode_bayar' => $pembayaran->metode_bayar,
                'periode' => $periode,
            ];
        });
    }

    public function headings(): array
    {
        return ['ID', 'Nama Pelanggan', 'Paket', 'Jumlah Bayar', 'Tanggal Pembayaran', 'Keterangan', ' Metode Bayar', 'Periode'];
    }

    public function styles(Worksheet $sheet)
    {
        // Jumlah baris data + header
        $lastRow = $sheet->getHighestRow();

        // Style header (baris 1)
        $sheet->getStyle('A1:H1')->applyFromArray([
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
        $sheet->getStyle("A1:H{$lastRow}")->applyFromArray([
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