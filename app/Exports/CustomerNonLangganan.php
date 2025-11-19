<?php

namespace App\Exports;

use App\Models\Pendapatan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CustomerNonLangganan implements FromCollection, WithHeadings, WithMapping, WithTitle, WithStyles, WithEvents, WithColumnFormatting, ShouldAutoSize
{
    protected $month;
    protected $year;

    public function __construct($month, $year)
    {
        $this->month = $month;
        $this->year = $year;
    }

    public function title(): string
    {
        $monthName = 'Semua';
        if ($this->month && $this->month !== 'all') {
            $monthName = Carbon::createFromDate(null, $this->month, 1)->translatedFormat('F');
        }
        return "Pendapatan Non-Langganan {$monthName} {$this->year}";
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Pendapatan::with('user');

        if ($this->month && $this->month != 'all') {
            $query->whereMonth('tanggal', $this->month);
        }

        if ($this->year) {
            $query->whereYear('tanggal', $this->year);
        }

        return $query->orderBy('tanggal', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jumlah Pendapatan',
            'Jenis Pendapatan',
            'Deskripsi',
            'Tanggal',
            'Metode Bayar',
            'Admin',
        ];
    }

    public function map($pendapatan): array
    {
        return [
            $pendapatan->id,
            $pendapatan->jumlah_pendapatan,
            $pendapatan->jenis_pendapatan,
            $pendapatan->deskripsi,
            Carbon::parse($pendapatan->tanggal)->format('d-m-Y'),
            $pendapatan->metode_bayar,
            $pendapatan->user->name ?? 'N/A',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Placeholder, styling utama dilakukan di registerEvents
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Judul Laporan
                $sheet->insertNewRowBefore(1, 3);
                $sheet->mergeCells('A1:G1');
                $sheet->mergeCells('A2:G2');
                $sheet->setCellValue('A1', 'LAPORAN PENDAPATAN NON-LANGGANAN');

                $monthName = 'Semua Bulan';
                if ($this->month && $this->month !== 'all') {
                    $monthName = Carbon::createFromDate(null, $this->month, 1)->translatedFormat('F');
                }
                $sheet->setCellValue('A2', "Periode: {$monthName} {$this->year}");

                // Style Judul
                $sheet->getStyle('A1:A2')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                    ],
                ]);
                $sheet->getStyle('A2')->getFont()->setSize(12);

                // Style Header Tabel (sekarang di baris 4)
                $headerRange = 'A4:G4';
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4F81BD'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Style untuk seluruh data tabel
                $lastRow = $sheet->getHighestRow();
                $dataRange = 'A5:G' . $lastRow;
                $sheet->getStyle($dataRange)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'DDDDDD'],
                        ],
                    ],
                ]);

                // Rata tengah untuk kolom ID, Tanggal, Metode Bayar, Admin
                $sheet->getStyle('A5:A' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('E5:G' . $lastRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Freeze pane (bekukan baris header)
                $sheet->freezePane('A5');

                // Set AutoFilter
                $sheet->setAutoFilter($headerRange);
            },
        ];
    }
}
