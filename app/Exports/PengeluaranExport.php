<?php

namespace App\Exports;

use App\Models\Pengeluaran;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PengeluaranExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithChunkReading
{
    protected $month;

    public function __construct($month = null)
    {
        $this->month = $month;
    }

    public function query()
    {
        $query = Pengeluaran::with('user:id,name');
        
        if ($this->month && $this->month !== 'all') {
            $query->whereMonth('tanggal_pengeluaran', $this->month)
                  ->whereYear('tanggal_pengeluaran', date('Y'));
        }
        
        return $query->orderBy('tanggal_pengeluaran', 'desc');
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal Pengeluaran',
            'Jenis Pengeluaran',
            'Keterangan',
            'Jumlah Pengeluaran',
            'Metode Pengeluaran',
            'Status',
            'Admin'
        ];
    }

    public function map($pengeluaran): array
    {
        static $i = 0;
        $i++;
        
        return [
            $i,
            $pengeluaran->tanggal_pengeluaran,
            $pengeluaran->jenis_pengeluaran,
            $pengeluaran->keterangan,
            $pengeluaran->jumlah_pengeluaran,
            $pengeluaran->metode_pengeluaran,
            $this->getStatusText($pengeluaran->status_id),
            $pengeluaran->user->name ?? 'N/A'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style untuk header
            1 => [
                'font' => [
                    'bold' => true, 
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => 'solid', 
                    'startColor' => ['rgb' => '3498DB']
                ]
            ],
            // Style untuk kolom jumlah (rata kanan)
            'E' => [
                'alignment' => [
                    'horizontal' => 'right'
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,  // No
            'B' => 15, // Tanggal
            'C' => 20, // Jenis Pengeluaran
            'D' => 30, // Keterangan
            'E' => 18, // Jumlah
            'F' => 18, // Metode
            'G' => 20, // Status
            'H' => 15, // Admin
        ];
    }

    public function chunkSize(): int
    {
        return 1000; // Process 1000 records at a time
    }

    private function getStatusText($statusId)
    {
        $status = [
            1 => 'Menunggu Konfirmasi',
            2 => 'Approved',
            3 => 'Berhasil'
        ];

        return $status[$statusId] ?? 'Unknown';
    }
}