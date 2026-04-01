<?php

namespace App\Exports;

use App\Models\TiketOpen;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Carbon\Carbon;

class TiketExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle
{
    protected $type;
    protected $month;
    protected $kategoriId;
    protected $search;
    protected $title;

    public function __construct($type = 'proses', $month = 'all', $kategoriId = 'all', $search = '')
    {
        $this->type = $type;
        $this->month = $month;
        $this->kategoriId = $kategoriId;
        $this->search = $search;
        $this->title = $this->generateTitle();
    }

    public function title(): string
    {
        return $this->title;
    }

    private function generateTitle()
    {
        $typeName = ($this->type === 'proses') ? 'Dalam Proses' : 'Selesai';
        $title = "Laporan Tiket " . $typeName;
        
        if ($this->month && $this->month != 'all') {
            $monthName = Carbon::create()->month($this->month)->translatedFormat('F');
            $title .= " - Bulan " . $monthName;
        }

        return $title;
    }

    public function collection()
    {
        $query = TiketOpen::with(['kategori', 'user', 'teknisi', 'customer' => function ($q) {
            $q->withTrashed();
        }]);

        if ($this->type === 'proses') {
            $query->whereHas('customer', function ($q) {
                $q->whereIn('status_id', [3, 4, 9])->whereNull('deleted_at');
            })->where('status_id', 6);
        } else {
            $query->whereHas('customer', function ($q) {
                $q->whereIn('status_id', [3, 4])->withTrashed();
            })->where('status_id', 3);
        }

        if ($this->search) {
            $query->whereHas('customer', function ($q) {
                $q->where('nama_customer', 'like', "%{$this->search}%")
                  ->orWhere('alamat', 'like', "%{$this->search}%")
                  ->orWhere('no_hp', 'like', "%{$this->search}%");
            });
        }

        if ($this->month && $this->month != 'all') {
            $query->whereMonth('created_at', $this->month);
        }

        if ($this->kategoriId && $this->kategoriId != 'all') {
            $query->where('kategori_id', $this->kategoriId);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function map($item): array
    {
        $status = '';
        if ($item->status_id == 6) {
            $status = 'Menunggu';
        } elseif ($item->status_id == 3) {
            $status = 'Selesai';
        }

        return [
            $item->id,
            $item->customer->nama_customer ?? '-',
            $item->customer->alamat ?? '-',
            $item->customer->no_hp ?? '-',
            $item->keterangan,
            $status,
            $item->kategori->nama_kategori ?? '-',
            $item->created_at->format('d-m-Y H:i:s'),
            $this->type === 'proses' ? ($item->user->name ?? '-') : ($item->teknisi->name ?? '-'),
            $this->type === 'selesai' ? ($item->tanggal_selesai ?: $item->updated_at->format('d-m-Y H:i:s')) : '-',
        ];
    }

    public function headings(): array
    {
        return [
            'ID Tiket',
            'Nama Pelanggan',
            'Alamat',
            'No HP',
            'Keterangan',
            'Status',
            'Kategori',
            'Tanggal Dibuat',
            $this->type === 'proses' ? 'Dibuat Oleh' : 'Teknisi',
            'Tanggal Selesai',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return $sheet;
    }

    private function getColumnLetter($index)
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = floor($index / 26) - 1;
        }
        return $letters;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $totalColumns = 10;
                $maxColumn = $this->getColumnLetter($totalColumns - 1);

                $sheet->insertNewRowBefore(1, 3);
                $sheet->setCellValue('A1', strtoupper($this->title));
                $sheet->setCellValue('A2', 'Tanggal Export: ' . Carbon::now()->format('d-m-Y H:i:s'));
                
                $sheet->mergeCells("A1:{$maxColumn}1");
                $sheet->mergeCells("A2:{$maxColumn}2");
                $sheet->mergeCells("A3:{$maxColumn}3");

                $sheet->getStyle('A1')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 14],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]
                ]);

                $headerRange = "A4:{$maxColumn}4";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2C3E50'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ],
                    ],
                ]);

                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 4) {
                    $sheet->getStyle("A5:{$maxColumn}{$lastRow}")->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                    ]);
                }

                foreach (range(0, $totalColumns - 1) as $col) {
                    $sheet->getColumnDimension($this->getColumnLetter($col))->setAutoSize(true);
                }
            },
        ];
    }
}
