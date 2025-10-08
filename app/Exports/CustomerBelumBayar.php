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
use Illuminate\Support\Facades\Log;

class CustomerBelumBayar implements FromCollection, WithMapping, WithHeadings, WithStyles, WithColumnFormatting, ShouldAutoSize
{
    protected $type;
    protected $bulan;
    protected $startDate;
    protected $endDate;
    protected $agenId;
    protected $includeDeleted;

    public function __construct($type = 'all', $bulan = null, $startDate = null, $endDate = null, $agenId = null, $includeDeleted = true)
    {
        $this->type = $type;
        $this->bulan = $bulan;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->agenId = $agenId;
        $this->includeDeleted = $includeDeleted;
    }

    public function collection()
    {
        Log::info('CustomerBelumBayar Export Started', [
            'type' => $this->type,
            'bulan' => $this->bulan,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'agenId' => $this->agenId
        ]);

        // Query dasar
        $query = Invoice::with([
            'customer' => function ($q) {
                if ($this->includeDeleted) {
                    $q->withTrashed();
                }
            },
            'paket',
            'status',
            'customer.agen'
        ]);

        // Filter status
        $query->where(function($q) {
            $q->whereHas('status', function($statusQuery) {
                $statusQuery->whereIn('nama_status', ['Belum Bayar', 'Unpaid']);
            })->orWhere('status_id', 7);
        });

        // Filter by agen_id
        if ($this->agenId) {
            $query->whereHas('customer', function ($q) {
                $q->where('agen_id', $this->agenId);
                if ($this->includeDeleted) {
                    $q->withTrashed();
                }
            });
        }

        // Apply date filters
        if ($this->type === 'range' && $this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            
            Log::info('Applying date range filter', [
                'start' => $start,
                'end' => $end
            ]);
            
            $query->whereBetween('jatuh_tempo', [$start, $end]);
            
        } elseif ($this->type === 'bulan' && is_array($this->bulan)) {
            $query->whereMonth('jatuh_tempo', $this->bulan['month'])
                  ->whereYear('jatuh_tempo', $this->bulan['year']);
                  
        } elseif ($this->type === 'all') {
            // Tampilkan SEMUA data unpaid tanpa filter tanggal
        } else {
            // Default: bulan ini
            $query->whereMonth('jatuh_tempo', now()->month)
                  ->whereYear('jatuh_tempo', now()->year);
        }

        $results = $query->orderBy('jatuh_tempo', 'asc')->get();

        Log::info('CustomerBelumBayar Export Results', [
            'total_records' => $results->count()
        ]);

        return $results;
    }

    public function map($invoice): array
    {
        // Hitung total keseluruhan
        $totalKeseluruhan = ($invoice->tagihan ?? 0) +
            ($invoice->tambahan ?? 0) +
            ($invoice->tunggakan ?? 0) -
            ($invoice->saldo ?? 0);

        // Tandai customer yang sudah dihapus
        $namaCustomer = $invoice->customer->nama_customer ?? '-';
        $statusCustomer = 'Aktif';

        if ($invoice->customer && $invoice->customer->trashed()) {
            $statusCustomer = 'Deaktivasi';
            $namaCustomer .= ' (Deaktivasi)';
        }

        return [
            $namaCustomer,
            $invoice->customer->alamat ?? '-',
            $invoice->customer->no_hp ?? '-',
            $invoice->customer->agen->name ?? '-',
            $invoice->paket->nama_paket ?? '-',
            $invoice->status->nama_status ?? '-',
            $invoice->tagihan ?? 0,
            $invoice->tambahan ?? 0,
            $invoice->tunggakan ?? 0,
            $invoice->saldo ?? 0,
            $totalKeseluruhan,
            $invoice->jatuh_tempo ? Carbon::parse($invoice->jatuh_tempo)->format('F Y') : '-',
            $statusCustomer
        ];
    }

    public function headings(): array
    {
        $rangeInfo = '';
        
        if ($this->type === 'range' && $this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->format('d M Y');
            $end = Carbon::parse($this->endDate)->format('d M Y');
            $rangeInfo = " ({$start} hingga {$end})";
        } elseif ($this->type === 'bulan' && is_array($this->bulan)) {
            $monthName = $this->getMonthName($this->bulan['month']);
            $rangeInfo = " ({$monthName} {$this->bulan['year']})";
        } elseif ($this->type === 'all') {
            $rangeInfo = " (Semua Data)";
        }

        return [
            'Nama Pelanggan' . $rangeInfo,
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
            'Status Customer'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => '000000'],
                    'size' => 11,
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'color' => ['rgb' => 'E9ECEF'] // Abu-abu sangat muda
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ]
            ],
            // Data rows (diperbarui dari A:O menjadi A:M karena kolom berkurang)
            'A:M' => [
                'alignment' => [
                    'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
                ],
                'font' => [
                    'size' => 10,
                ]
            ],
            // Number columns (G, H, I, J, K) - tetap sama
            'G:K' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT,
                ]
            ],
            // Kolom Jatuh Tempo (L) dan Status Customer (M) center
            'L:M' => [
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ]
            ],
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
            // Kolom N (hari keterlambatan) dihapus
        ];
    }

    private function getMonthName($month)
    {
        $months = [
            '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
            '04' => 'April', '05' => 'Mei', '06' => 'Juni',
            '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
            '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
        ];

        return $months[str_pad($month, 2, '0', STR_PAD_LEFT)] ?? 'Unknown';
    }
}