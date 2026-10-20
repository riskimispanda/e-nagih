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
  protected $year;
  protected $kategori;
  protected $search;

  public function __construct($month = null, $year = null, $kategori = null, $search = null)
  {
    $this->month = $month;
    $this->year = $year ?? date('Y');
    $this->kategori = $kategori;
    $this->search = $search;
  }

  public function query()
  {
    $query = Pengeluaran::with('user:id,name');

    if ($this->month && $this->month !== 'all') {
      $query->whereMonth('tanggal_pengeluaran', $this->month)
        ->whereYear('tanggal_pengeluaran', $this->year);
    } elseif ($this->year) {
      // Jika hanya tahun yang dipilih (tanpa bulan spesifik)
      $query->whereYear('tanggal_pengeluaran', $this->year);
    }

    // Filter kategori (sama seperti filter pada halaman /pengeluaran/global)
    if ($this->kategori) {
      $query->where('jenis_pengeluaran', 'like', '%' . $this->kategori . '%');
    }

    // Filter pencarian (sama seperti filter pada halaman /pengeluaran/global)
    if ($this->search) {
      $query->where(function ($q) {
        $q->where('keterangan', 'like', '%' . $this->search . '%')
          ->orWhere('jenis_pengeluaran', 'like', '%' . $this->search . '%');
      });
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
      'Jenis Kas',
      'Status',
      'Admin',
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
      $pengeluaran->metode_bayar,
      $pengeluaran->kas->jenis_kas ?? '-',
      $this->getStatusText($pengeluaran->status_id),
      $pengeluaran->user->name ?? 'N/A',
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
      'G' => 15, // Jenis Kas
      'H' => 20, // Status
      'I' => 15, // Admin
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
