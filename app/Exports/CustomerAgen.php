<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use App\Models\Invoice;
use Carbon\Carbon;

class CustomerAgen implements FromCollection, WithMapping, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $type;
    protected $bulan;
    protected $startDate;
    protected $endDate;
    protected $agenId;
    protected $filterStatus;

    /**
     * @param string $type 'bulan' untuk filter bulan tertentu, 'range' untuk custom date, 'all' untuk semua bulan
     * @param array|null $bulan ['month' => int, 'year' => int] atau 'all'
     * @param string|null $startDate format Y-m-d
     * @param string|null $endDate format Y-m-d
     * @param int|null $agenId ID agen untuk filter
     * @param string|null $filterStatus Status tagihan untuk filter
     */
    public function __construct($type = 'bulan', $bulan = null, $startDate = null, $endDate = null, $agenId = null, $filterStatus = null)
    {
        $this->type = $type;
        $this->bulan = $bulan;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->agenId = $agenId;
        $this->filterStatus = $filterStatus;
    }

    public function collection()
    {
        $query = Invoice::with(['customer', 'paket', 'status', 'customer.agen', 'pembayaran.user'])
            ->whereHas('customer', function ($q) {
                $q->whereIn('status_id', [1, 2, 3, 4, 5, 9]);
            });

        // Filter by agen_id
        if ($this->agenId) {
            $query->whereHas('customer', function ($q) {
                $q->where('agen_id', $this->agenId);
            });
        }

        // Apply status filter jika ada
        if ($this->filterStatus) {
            if ($this->filterStatus == 'Sudah Bayar') {
                $query->whereHas('status', fn($q) => $q->where('nama_status', 'Sudah Bayar'));
            } elseif ($this->filterStatus == 'Belum Bayar') {
                $query->whereHas('status', fn($q) => $q->where('nama_status', 'Belum Bayar'));
            }
        }

        // Apply date filters - PERBAIKAN DI SINI
        if ($this->type === 'range' && $this->startDate && $this->endDate) {
            // Custom date range
            $query->whereBetween('jatuh_tempo', [$this->startDate, $this->endDate]);
        } elseif ($this->type === 'bulan') {
            // Handle filter bulan
            if ($this->bulan === 'all') {
                // Tampilkan semua data untuk tahun berjalan
                $query->whereYear('jatuh_tempo', now()->year);
            } elseif (is_array($this->bulan) && !empty($this->bulan)) {
                // Filter bulan & tahun tertentu
                $query->whereMonth('jatuh_tempo', $this->bulan['month'])
                    ->whereYear('jatuh_tempo', $this->bulan['year']);
            } else {
                // Default: bulan ini
                $query->whereMonth('jatuh_tempo', now()->month)
                    ->whereYear('jatuh_tempo', now()->year);
            }
        } else {
            // Default: bulan ini
            $query->whereMonth('jatuh_tempo', now()->month)
                ->whereYear('jatuh_tempo', now()->year);
        }

        return $query->orderBy('jatuh_tempo', 'desc')->get();
    }

    public function map($invoice): array
    {
        // Format tanggal pembayaran
        $tanggalPembayaran = '-';
        $metodeBayar = '-';
        $adminAgen = '-';
        $keterangan = '-';

        if ($invoice->pembayaran->isNotEmpty()) {
            $pembayaran = $invoice->pembayaran->first();
            $tanggalPembayaran = $pembayaran->created_at ?
                Carbon::parse($pembayaran->created_at)->format('d-M-Y H:i:s') : '-';
            $metodeBayar = $pembayaran->metode_bayar ?? '-';
            $adminAgen = $pembayaran->user ?
                $pembayaran->user->name . ' / ' . ($pembayaran->user->roles->name ?? '-') :
                'By Tripay';
            $keterangan = $pembayaran->keterangan ?? '-';
        }

        // Hitung total keseluruhan
        $totalKeseluruhan = ($invoice->tagihan ?? 0) +
            ($invoice->tambahan ?? 0) +
            ($invoice->tunggakan ?? 0) -
            ($invoice->saldo ?? 0);

        return [
            $invoice->customer->nama_customer ?? '-',
            $invoice->customer->alamat ?? '-',
            $invoice->customer->no_hp ?? '-',
            $invoice->customer->agen->name ?? '-', // PIC
            $invoice->paket->nama_paket ?? '-',
            $invoice->status->nama_status ?? '-',
            $invoice->tagihan ?? 0,
            $invoice->tambahan ?? 0,
            $invoice->tunggakan ?? 0,
            $invoice->saldo ?? 0,
            $totalKeseluruhan,
            $invoice->jatuh_tempo ? Carbon::parse($invoice->jatuh_tempo)->translatedFormat('F Y') : '-',
            $metodeBayar,
            $tanggalPembayaran,
            $adminAgen,
            $keterangan
        ];
    }

    public function headings(): array
    {
        return [
            'Nama Pelanggan',
            'Alamat',
            'No HP',
            'PIC',
            'Paket',
            'Status Tagihan',
            'Total Tagihan',
            'Tambahan',
            'Tunggakan',
            'Saldo',
            'Total Keseluruhan',
            'Periode',
            'Metode Bayar',
            'Tanggal Pembayaran',
            'Admin/Agen',
            'Keterangan'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk kompatibilitas WPS Office
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => '2c3e50']
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ]
            ],
            // Data rows
            'A:Z' => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'size' => 10,
                ]
            ],
            // Number columns (G, H, I, J, K)
            'G:K' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ]
            ]
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Tagihan
            'H' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Tambahan
            'I' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Tunggakan
            'J' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Saldo
            'K' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1, // Total Keseluruhan
        ];
    }
}