<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use App\Models\Invoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CustomerAgen implements FromCollection, WithMapping, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize, WithEvents
{
    protected $agenId;
    protected $filterStatus;
    protected $includeDeleted;
    protected $filterMonth;

    public function __construct($agenId, $filterMonth = null, $filterStatus = null, $includeDeleted = true)
    {
        $this->agenId = $agenId;
        $this->filterMonth = $filterMonth;
        $this->filterStatus = $filterStatus;
        $this->includeDeleted = $includeDeleted;
    }

    public function collection()
    {
        try {
            Log::info('Starting export for agen.', [
                'agenId'       => $this->agenId,
                'filterMonth'  => $this->filterMonth,
                'filterStatus' => $this->filterStatus
            ]);

            $query = Invoice::with([
                'customer' => function ($q) {
                    if ($this->includeDeleted) {
                        $q->withTrashed();
                    }
                },
                'paket',
                'status',
                'customer.agen',
                'pembayaran.user',
            ])->whereHas('customer', function ($q) {
                $q->where('agen_id', $this->agenId);
                if ($this->includeDeleted) {
                    $q->withTrashed();
                }
            });

            // Apply status filter jika ada
            if ($this->filterStatus && $this->filterStatus !== 'all') {
                $query->whereHas('status', fn($q) => $q->where('nama_status', $this->filterStatus));
                Log::info('Applying status filter: ' . $this->filterStatus);
            }

            // Apply date filters
            if (is_array($this->filterMonth) && isset($this->filterMonth['month'])) {
                $month = $this->filterMonth['month'];
                $year = $this->filterMonth['year'] ?? now()->year;
                $query->whereMonth('jatuh_tempo', $month)
                    ->whereYear('jatuh_tempo', $year);
                Log::info("Applying month filter: {$month}-{$year}");
            }

            $result = $query->orderBy('jatuh_tempo', 'desc')->get();
            Log::info('Export collection result count: ' . $result->count());

            return $result;
        } catch (\Exception $e) {
            Log::error('Error in export collection: ' . $e->getMessage());
            Log::error('File: ' . $e->getFile() . 'Line: ' . $e->getLine());
            return collect();
        }
    }

    public function map($invoice): array
    {
        try {
            // Format tanggal pembayaran dengan pengecekan yang lebih ketat
            $tanggalPembayaran = '-';
            $metodeBayar = '-';
            $adminAgen = '-';
            $keterangan = '-';

            if (
                isset($invoice->pembayaran) &&
                is_object($invoice->pembayaran) &&
                method_exists($invoice->pembayaran, 'isNotEmpty') &&
                $invoice->pembayaran->isNotEmpty()
            ) {

                $pembayaran = $invoice->pembayaran->first();
                if ($pembayaran) {
                    $tanggalPembayaran = $pembayaran->created_at ?
                        Carbon::parse($pembayaran->created_at)->format('d-M-Y H:i:s') : '-';
                    $metodeBayar = $pembayaran->metode_bayar ?? '-';

                    // Cek user pembayaran
                    if (isset($pembayaran->user) && $pembayaran->user) {
                        $adminAgen = $pembayaran->user->name . ' / ' .
                            (isset($pembayaran->user->roles) && $pembayaran->user->roles ?
                                $pembayaran->user->roles->name : '-');
                    } else {
                        $adminAgen = 'By Tripay';
                    }

                    $keterangan = $pembayaran->keterangan ?? '-';
                }
            }

            // Hitung total keseluruhan dengan nilai default
            $tagihan = $invoice->tagihan ?? 0;
            $tambahan = $invoice->tambahan ?? 0;
            $tunggakan = $invoice->tunggakan ?? 0;
            $saldo = $invoice->saldo ?? 0;

            $totalKeseluruhan = $tagihan + $tambahan + $tunggakan - $saldo;

            // Tandai customer yang sudah dihapus
            $namaCustomer = '-';
            $statusCustomer = 'Aktif';

            if (isset($invoice->customer) && $invoice->customer) {
                $namaCustomer = $invoice->customer->nama_customer ?? '-';

                // Cek jika customer dihapus
                if (method_exists($invoice->customer, 'trashed') && $invoice->customer->trashed()) {
                    $statusCustomer = 'Deaktivasi';
                    $namaCustomer .= ' (Deaktivasi)';
                }
            }

            // Cek semua relasi dengan isset
            $alamat = isset($invoice->customer) ? ($invoice->customer->alamat ?? '-') : '-';
            $noHp = isset($invoice->customer) ? ($invoice->customer->no_hp ?? '-') : '-';
            $pic = isset($invoice->customer->agen) ? ($invoice->customer->agen->name ?? '-') : '-';
            $paket = isset($invoice->paket) ? ($invoice->paket->nama_paket ?? '-') : '-';
            $statusTagihan = isset($invoice->status) ? ($invoice->status->nama_status ?? '-') : '-';
            $periode = $invoice->jatuh_tempo ? Carbon::parse($invoice->jatuh_tempo)->translatedFormat('F Y') : '-';

            return [
                $namaCustomer,
                $alamat,
                $noHp,
                $pic,
                $paket,
                $statusTagihan,
                $tagihan,
                $tambahan,
                $tunggakan,
                $saldo,
                $totalKeseluruhan,
                $periode,
                $metodeBayar,
                $tanggalPembayaran,
                $adminAgen,
                $keterangan,
                $statusCustomer
            ];
        } catch (\Exception $e) {
            Log::error('Error mapping invoice data: ' . $e->getMessage());
            Log::error('Invoice ID: ' . ($invoice->id ?? 'Unknown'));
            return array_fill(0, 17, 'Error');
        }
    }

    public function headings(): array
    {
        return [
            'NAMA PELANGGAN',
            'ALAMAT',
            'NO HP',
            'PIC',
            'PAKET',
            'STATUS TAGIHAN',
            'TOTAL TAGIHAN',
            'TAMBAHAN',
            'TUNGGAKAN',
            'SALDO',
            'TOTAL KESELURUHAN',
            'PERIODE',
            'METODE BAYAR',
            'TANGGAL PEMBAYARAN',
            'ADMIN/AGEN',
            'KETERANGAN',
            'STATUS CUSTOMER'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Dapatkan jumlah data
        $dataCount = $this->collection()->count();
        $lastDataRow = $dataCount > 0 ? 4 + $dataCount : 5;

        return [
            // Style untuk judul utama
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '2c3e50']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Style untuk info periode
            2 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'color' => ['rgb' => '2c3e50'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Style untuk tanggal generate
            3 => [
                'font' => [
                    'italic' => true,
                    'size' => 10,
                    'color' => ['rgb' => '666666'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ],
            // Style untuk header tabel - DI BARIS 4
            4 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => '34495e']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '1a252f'],
                    ],
                ]
            ],
            // Style untuk data rows - MULAI DARI BARIS 5
            'A5:Q' . $lastDataRow => [
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
                'font' => [
                    'size' => 10,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'ecf0f1'],
                    ],
                ]
            ],
            // Style untuk kolom numerik (rata kanan)
            'G5:K' . $lastDataRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ]
            ],
            // Style untuk kolom teks (rata kiri dengan wrap text)
            'A5:F' . $lastDataRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true
                ]
            ],
            'L5:Q' . $lastDataRow => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_LEFT,
                    'wrapText' => true
                ]
            ],
        ];
    }

    public function columnFormats(): array
    {
        return [
            'G' => '#,##0',
            'H' => '#,##0',
            'I' => '#,##0',
            'J' => '#,##0',
            'K' => '#,##0',
        ];
    }

    public function registerEvents(): array
    {
        return [
            BeforeSheet::class => function (BeforeSheet $event) {
                // Set nama worksheet
                $event->getSheet()->setTitle('Laporan Pelanggan');
            },

            AfterSheet::class => function (AfterSheet $event) {
                try {
                    $sheet = $event->sheet->getDelegate();

                    // Ambil nama agen
                    $agenName = 'LAPORAN PELANGGAN AGEN';
                    if ($this->agenId) {
                        $agen = User::find($this->agenId);
                        if ($agen) {
                            $agenName = 'LAPORAN PELANGGAN AGEN: ' . strtoupper($agen->name);
                        }
                    }

                    // Buat info periode
                    $periodInfo = 'PERIODE: ';
                    if ($this->filterStatus && $this->filterStatus !== 'all') {
                        $periodInfo .= strtoupper($this->filterStatus);
                    }

                    if (is_array($this->filterMonth) && isset($this->filterMonth['month'])) {
                        $month = $this->filterMonth['month'];
                        $year = $this->filterMonth['year'] ?? now()->year;
                        $monthName = Carbon::create()->month($month)->translatedFormat('F');
                        $periodInfo .= ($this->filterStatus && $this->filterStatus !== 'all' ? ' - ' : '') .
                            strtoupper($monthName) . ' ' . $year;
                    } else {
                        $periodInfo .= ($this->filterStatus && $this->filterStatus !== 'all' ? ' - ' : '') . 'SEMUA BULAN';
                    }

                    // Tanggal generate laporan
                    $generateDate = 'Dibuat pada: ' . Carbon::now()->translatedFormat('l, d F Y H:i:s');

                    // Set header manual
                    $sheet->setCellValue('A1', $agenName);
                    $sheet->mergeCells('A1:Q1');

                    $sheet->setCellValue('A2', $periodInfo);
                    $sheet->mergeCells('A2:Q2');

                    $sheet->setCellValue('A3', $generateDate);
                    $sheet->mergeCells('A3:Q3');

                    // Set header kolom di baris 4
                    $headings = $this->headings();
                    $column = 'A';
                    foreach ($headings as $heading) {
                        $sheet->setCellValue($column . '4', $heading);
                        $column++;
                    }

                    // Apply styles untuk header
                    $sheet->getStyle('A1')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 16,
                            'color' => ['rgb' => 'FFFFFF'],
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => '2c3e50']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ]);

                    $sheet->getStyle('A2')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12,
                            'color' => ['rgb' => '2c3e50'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ]);

                    $sheet->getStyle('A3')->applyFromArray([
                        'font' => [
                            'italic' => true,
                            'size' => 10,
                            'color' => ['rgb' => '666666'],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ]
                    ]);

                    // Style untuk header tabel di baris 4
                    $sheet->getStyle('A4:Q4')->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'color' => ['rgb' => 'FFFFFF'],
                            'size' => 11,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'color' => ['rgb' => '34495e']
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => '1a252f'],
                            ],
                        ]
                    ]);

                    // Set row dimensions
                    $sheet->getRowDimension(1)->setRowHeight(30);
                    $sheet->getRowDimension(2)->setRowHeight(20);
                    $sheet->getRowDimension(3)->setRowHeight(18);
                    $sheet->getRowDimension(4)->setRowHeight(25); // Header tabel

                    // Set manual column widths untuk kolom dengan konten panjang
                    $sheet->getColumnDimension('A')->setWidth(25); // NAMA PELANGGAN
                    $sheet->getColumnDimension('B')->setWidth(40); // ALAMAT (lebih lebar)
                    $sheet->getColumnDimension('C')->setWidth(15); // NO HP
                    $sheet->getColumnDimension('D')->setWidth(20); // PIC
                    $sheet->getColumnDimension('E')->setWidth(20); // PAKET
                    $sheet->getColumnDimension('F')->setWidth(15); // STATUS TAGIHAN
                    $sheet->getColumnDimension('G')->setWidth(15); // TOTAL TAGIHAN
                    $sheet->getColumnDimension('H')->setWidth(12); // TAMBAHAN
                    $sheet->getColumnDimension('I')->setWidth(12); // TUNGGAKAN
                    $sheet->getColumnDimension('J')->setWidth(12); // SALDO
                    $sheet->getColumnDimension('K')->setWidth(18); // TOTAL KESELURUHAN
                    $sheet->getColumnDimension('L')->setWidth(15); // PERIODE
                    $sheet->getColumnDimension('M')->setWidth(15); // METODE BAYAR
                    $sheet->getColumnDimension('N')->setWidth(20); // TANGGAL PEMBAYARAN
                    $sheet->getColumnDimension('O')->setWidth(25); // ADMIN/AGEN
                    $sheet->getColumnDimension('P')->setWidth(25); // KETERANGAN
                    $sheet->getColumnDimension('Q')->setWidth(15); // STATUS CUSTOMER

                    // Set wrap text untuk kolom dengan konten panjang
                    $sheet->getStyle('B5:B' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('P5:P' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
                    $sheet->getStyle('O5:O' . $sheet->getHighestRow())->getAlignment()->setWrapText(true);

                    // Freeze panes pada header (row 4) agar header tetap visible saat scroll
                    $sheet->freezePane('A5');

                    // Set row height otomatis untuk baris data (mulai dari baris 5)
                    for ($row = 5; $row <= $sheet->getHighestRow(); $row++) {
                        $sheet->getRowDimension($row)->setRowHeight(-1); // Auto height berdasarkan content
                    }

                    // IMPORTANT: Write data starting from row 5
                    $data = $this->collection();
                    $row = 5;
                    foreach ($data as $invoice) {
                        $mappedData = $this->map($invoice);
                        $col = 'A';
                        foreach ($mappedData as $value) {
                            $sheet->setCellValue($col . $row, $value);
                            $col++;
                        }
                        $row++;
                    }
                } catch (\Exception $e) {
                    Log::error('Error in registerEvents: ' . $e->getMessage());
                }
            },
        ];
    }
}