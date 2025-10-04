<?php

namespace App\Exports;

use App\Models\Pembayaran;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class PembayaranExport implements FromCollection, WithHeadings, WithStyles, WithColumnFormatting
{
    protected $filter;
    protected $startDate;
    protected $endDate;
    protected $month;
    protected $year;
    protected $search;
    protected $metode;

    public function __construct($filter = 'harian', $startDate = null, $endDate = null, $month = null, $year = null, $search = null, $metode = null)
    {
        $this->filter = $filter;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->month = $month;
        $this->year = $year ?: date('Y');
        $this->search = $search;
        $this->metode = $metode;

        // Log parameter untuk debugging
        logger('PembayaranExport Constructor Parameters:', [
            'filter' => $filter,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'month' => $month,
            'year' => $year,
            'search' => $search,
            'metode' => $metode
        ]);
    }

    public function collection()
    {
        $query = Pembayaran::with(['invoice.customer', 'invoice.paket', 'invoice.status', 'user']);

        // **PRIORITAS FILTER YANG DIPERBAIKI:**
        // 1. Custom Date Range (tertinggi)
        // 2. Filter Bulan
        // 3. Filter Default

        if ($this->startDate && $this->endDate) {
            // **PRIORITAS 1: Filter berdasarkan date range custom**
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();

            $query->whereBetween('tanggal_bayar', [$start, $end]);

            logger('Using CUSTOM DATE RANGE Filter', [
                'start_date' => $start->format('Y-m-d H:i:s'),
                'end_date' => $end->format('Y-m-d H:i:s'),
                'date_range' => $start->format('d F Y') . ' - ' . $end->format('d F Y')
            ]);
        } elseif ($this->month && $this->month !== '' && $this->month !== null) {
            // **PRIORITAS 2: Filter berdasarkan bulan yang dipilih**
            $query->whereMonth('tanggal_bayar', $this->month)
                ->whereYear('tanggal_bayar', $this->year);

            logger('Using MONTH Filter', [
                'month' => $this->month,
                'year' => $this->year,
                'period' => $this->getMonthName($this->month) . ' ' . $this->year
            ]);
        } else {
            // **PRIORITAS 3: Filter default berdasarkan tipe**
            switch ($this->filter) {
                case 'harian':
                case 'today':
                    $query->whereDate('tanggal_bayar', Carbon::today());
                    logger('Using TODAY Filter');
                    break;
                case 'bulanan':
                case 'monthly':
                    $query->whereMonth('tanggal_bayar', Carbon::now()->month)
                        ->whereYear('tanggal_bayar', Carbon::now()->year);
                    logger('Using CURRENT MONTH Filter');
                    break;
                case 'currentMonth':
                    $query->whereMonth('tanggal_bayar', $this->month ?? Carbon::now()->month)
                        ->whereYear('tanggal_bayar', $this->year);
                    logger('Using CURRENT MONTH (with params) Filter');
                    break;
                case 'custom':
                    // Jika custom tanpa date range, default ke bulan ini
                    $query->whereMonth('tanggal_bayar', Carbon::now()->month)
                        ->whereYear('tanggal_bayar', Carbon::now()->year);
                    logger('Using CUSTOM (default to current month) Filter');
                    break;
                default:
                    // Default safety: ambil data 30 hari terakhir
                    $query->where('tanggal_bayar', '>=', Carbon::now()->subDays(30));
                    logger('Using DEFAULT (30 days) Filter');
                    break;
            }
        }

        // Filter tambahan berdasarkan search
        if ($this->search) {
            $query->where(function ($q) {
                $q->whereHas('invoice.customer', function ($customerQuery) {
                    $customerQuery->where('nama_customer', 'like', '%' . $this->search . '%')
                        ->orWhere('no_hp', 'like', '%' . $this->search . '%');
                })->orWhereHas('invoice.paket', function ($paketQuery) {
                    $paketQuery->where('nama_paket', 'like', '%' . $this->search . '%');
                });
            });
            logger('Applying SEARCH Filter', ['search_term' => $this->search]);
        }

        // Filter metode pembayaran
        if ($this->metode) {
            $query->where('metode_bayar', $this->metode);
            logger('Applying METODE Filter', ['metode' => $this->metode]);
        }

        // Sort by tanggal_bayar descending untuk data terbaru di atas
        $query->orderBy('tanggal_bayar', 'desc');

        $data = $query->get();

        return $data->map(function ($pembayaran, $index) {
            // **KOMBINASI: Tanggal dari tanggal_bayar + Jam dari created_at**
            $tanggalDariBayar = Carbon::parse($pembayaran->tanggal_bayar)
                ->locale('id')
                ->translatedFormat('d F Y');

            $jamDariCreated = Carbon::parse($pembayaran->created_at)
                ->locale('id')
                ->translatedFormat('H:i:s');

            // Gabungkan tanggal dan jam
            $tanggalBayarKombinasi = $tanggalDariBayar . ' ' . $jamDariCreated;

            // Format periode berdasarkan jatuh tempo invoice
            $periode = null;
            if ($pembayaran->invoice && $pembayaran->invoice->jatuh_tempo) {
                $periode = Carbon::parse($pembayaran->invoice->jatuh_tempo)
                    ->locale('id')
                    ->translatedFormat('F Y');
            }

            // Tipe pembayaran dengan fallback
            $tipePembayaran = $pembayaran->tipe_pembayaran ?? 'Reguler';

            // Status invoice untuk informasi tambahan
            $statusInvoice = $pembayaran->invoice->status->nama_status ?? 'Tidak Diketahui';

            return [
                'no' => $index + 1,
                'nama_pelanggan' => $pembayaran->invoice->customer->nama_customer ?? 'Pelanggan Tidak Diketahui',
                'no_hp' => $pembayaran->invoice->customer->no_hp ?? '-',
                'paket' => $pembayaran->invoice->paket->nama_paket ?? 'Paket Tidak Diketahui',
                'jumlah_bayar' => $pembayaran->jumlah_bayar, // Simpan sebagai number murni (tanpa format)
                'tanggal_bayar' => $tanggalBayarKombinasi,
                'metode_bayar' => $pembayaran->metode_bayar ?? 'Tidak Diketahui',
                'tipe_pembayaran' => ucfirst(strtolower($tipePembayaran)),
                'keterangan' => $pembayaran->keterangan ?? '-',
                'periode_tagihan' => $periode ?? '-',
                'status_invoice' => $statusInvoice,
                'admin_input' => $pembayaran->user->name ?? 'System',
                'pic' => $pembayaran->invoice->customer->agen->name ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nama Pelanggan',
            'Nomor HP',
            'Paket Internet',
            'Jumlah Bayar (Rupiah)',
            'Tanggal Pembayaran',
            'Metode Pembayaran',
            'Tipe Pembayaran',
            'Keterangan',
            'Periode Tagihan',
            'Status Invoice',
            'Admin Input',
            'PIC/Agent',
        ];
    }

    /**
     * Format kolom untuk Excel
     */
    public function columnFormats(): array
    {
        return [
            'E' => '#,##0', // Format kolom E (Jumlah Bayar) sebagai number dengan pemisah ribuan
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Jumlah baris data + header
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();

        // Style header (baris 1)
        $sheet->getStyle("A1:{$lastColumn}1")->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['argb' => Color::COLOR_WHITE],
                'size' => 12,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => '1f2937'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '374151'],
                ],
            ],
        ]);

        // Style data cells
        if ($lastRow > 1) {
            $sheet->getStyle("A2:{$lastColumn}{$lastRow}")->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['argb' => 'e5e7eb'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'size' => 10,
                ],
            ]);

            // Zebra striping untuk readability
            for ($row = 2; $row <= $lastRow; $row++) {
                if ($row % 2 == 0) {
                    $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('f8fafc');
                }
            }

            // Style untuk kolom angka (Jumlah Bayar) - right align dengan format currency
            $sheet->getStyle("E2:E{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
                'numberFormat' => [
                    'formatCode' => '#,##0',
                ],
            ]);

            // Style untuk kolom No - center align
            $sheet->getStyle("A2:A{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Style untuk kolom Tanggal Pembayaran - left align
            $sheet->getStyle("F2:F{$lastRow}")->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                ],
            ]);
        }

        // Auto width untuk semua kolom
        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Set minimum width untuk beberapa kolom penting
        $sheet->getColumnDimension('B')->setWidth(20); // Nama Pelanggan
        $sheet->getColumnDimension('D')->setWidth(15); // Paket Internet
        $sheet->getColumnDimension('E')->setWidth(18); // Jumlah Bayar (diperlebar)
        $sheet->getColumnDimension('F')->setWidth(25); // Tanggal Pembayaran
        $sheet->getColumnDimension('J')->setWidth(15); // Periode Tagihan

        // Auto height baris
        $sheet->getDefaultRowDimension()->setRowHeight(-1);

        // Freeze header row
        $sheet->freezePane('A2');

        // Tambahkan informasi filter pada sheet
        $this->addFilterInfo($sheet, $lastRow);

        // Tambahkan total summary
        $this->addTotalSummary($sheet, $lastRow);
    }

    /**
     * Tambahkan informasi filter di bagian bawah sheet
     */
    private function addFilterInfo(Worksheet $sheet, $lastRow)
    {
        $infoRow = $lastRow + 3;

        $filterInfo = "RINGKASAN EXPORT PEMBAYARAN\n";
        $filterInfo .= "=" . str_repeat("=", 50) . "\n";
        $filterInfo .= "Periode: " . $this->getFilterInfo() . "\n";
        $filterInfo .= "Jenis Filter: " . $this->getCurrentFilterType() . "\n";
        $filterInfo .= "Total Data: " . ($lastRow - 1) . " pembayaran\n";

        if ($this->search) {
            $filterInfo .= "Pencarian: " . $this->search . "\n";
        }

        if ($this->metode) {
            $filterInfo .= "Metode Bayar: " . $this->metode . "\n";
        }

        $filterInfo .= "Export dibuat: " . Carbon::now()->locale('id')->translatedFormat('d F Y H:i:s') . "\n";
        $filterInfo .= "Oleh: " . (auth()->user()->name ?? 'System') . "\n";
        $filterInfo .= "Data By Nbilling";

        $sheet->setCellValue("A{$infoRow}", $filterInfo);
        $sheet->mergeCells("A{$infoRow}:G" . ($infoRow + 9));

        $sheet->getStyle("A{$infoRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 10,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_LEFT,
                'vertical' => Alignment::VERTICAL_TOP,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'f3f4f6'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'd1d5db'],
                ],
            ],
        ]);
    }

    /**
     * Tambahkan total summary
     */
    private function addTotalSummary(Worksheet $sheet, $lastRow)
    {
        $summaryRow = $lastRow + 1;

        // Hitung total jumlah bayar
        $total = 0;
        for ($row = 2; $row <= $lastRow; $row++) {
            $value = $sheet->getCell("E{$row}")->getValue();
            if (is_numeric($value)) {
                $total += $value;
            }
        }

        // Tambahkan row total
        $sheet->setCellValue("D{$summaryRow}", "TOTAL:");
        $sheet->setCellValue("E{$summaryRow}", $total);

        // Style untuk total
        $sheet->getStyle("D{$summaryRow}:E{$summaryRow}")->applyFromArray([
            'font' => [
                'bold' => true,
                'size' => 11,
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['argb' => 'dbeafe'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => '93c5fd'],
                ],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
            ],
        ]);

        // Format number untuk total
        $sheet->getStyle("E{$summaryRow}")->getNumberFormat()->setFormatCode('#,##0');
    }

    /**
     * Mendapatkan informasi filter yang diterapkan
     */
    public function getFilterInfo(): string
    {
        if ($this->startDate && $this->endDate) {
            return Carbon::parse($this->startDate)->translatedFormat('d F Y') .
                " - " . Carbon::parse($this->endDate)->translatedFormat('d F Y');
        } elseif ($this->month && $this->month !== '' && $this->month !== null) {
            return "Bulan " . $this->getMonthName($this->month) . " " . $this->year;
        } else {
            $monthName = $this->getMonthName(Carbon::now()->month);
            return ucfirst($this->filter) . " (" . $monthName . " " . Carbon::now()->year . ")";
        }
    }

    /**
     * Mendapatkan nama bulan
     */
    private function getMonthName($month): string
    {
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];

        return $monthNames[$month] ?? $month;
    }

    /**
     * Mendapatkan tipe filter yang sedang aktif
     */
    private function getCurrentFilterType(): string
    {
        if ($this->startDate && $this->endDate) {
            return 'Custom Date Range';
        } elseif ($this->month && $this->month !== '' && $this->month !== null) {
            return 'Bulan Tertentu';
        } else {
            return ucfirst($this->filter);
        }
    }
}