<?php
namespace App\Exports;

use App\Models\Customer;
use App\Models\Paket;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use Carbon\Carbon;

class ExportPelanggan implements FromQuery, WithHeadings, WithMapping, WithStyles, WithEvents, WithTitle, WithChunkReading
{
    protected $type;
    protected $paketId;
    protected $title;

    public function __construct($type = 'semua', $paketId = null)
    {
        $this->type = $type;
        $this->paketId = $paketId;
        $this->title = $this->generateTitle();
    }

    public function title(): string
    {
        return $this->title;
    }

    private function generateTitle()
    {
        switch ($this->type) {
            case 'aktif':
                return 'Pelanggan Aktif';
            case 'nonaktif':
                return 'Pelanggan Nonaktif';
            case 'paket':
                return 'Pelanggan Berdasarkan Paket';
            case 'ringkasan':
                return 'Ringkasan Paket';
            case 'bulan':
                $month = $this->paketId['month'] ?? date('m');
                $year = $this->paketId['year'] ?? date('Y');
                return "Pelanggan Bulan {$month}-{$year}";
            default:
                return 'Semua Pelanggan';
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }

    public function query()
    {
        if ($this->type === 'ringkasan') {
            return Paket::withCount(['customer' => function ($q) {
                $q->withTrashed();
            }]);
        }

        $query = Customer::query()
            ->with([
                'paket',
                'odp.odc.olt.server',
                'router',
                'agen',
                'teknisi',
                'koneksi',
                'perangkat',
                'media',
                'invoice.pembayaran',
            ])
            ->select('customer.*')
            ->selectRaw('(
                SELECT MAX(pembayaran.tanggal_bayar)
                FROM pembayaran
                INNER JOIN invoice ON pembayaran.invoice_id = invoice.id
                WHERE invoice.customer_id = customer.id
            ) as last_pembayaran_date')
            ->selectRaw('(
                SELECT invoice.jatuh_tempo
                FROM pembayaran
                INNER JOIN invoice ON pembayaran.invoice_id = invoice.id
                WHERE invoice.customer_id = customer.id
                ORDER BY pembayaran.tanggal_bayar DESC
                LIMIT 1
            ) as periode_pembayaran')
            ->whereNull('customer.deleted_at');

        switch ($this->type) {
            case 'aktif':
                return $query->where('status_id', 3);
            case 'nonaktif':
                return $query->where('status_id', 9);
            case 'paket':
                return $query->where('paket_id', $this->paketId);
            case 'bulan':
                $month = $this->paketId['month'] ?? date('m');
                $year = $this->paketId['year'] ?? date('Y');
                return $query->whereYear('tanggal_selesai', $year)
                    ->whereMonth('tanggal_selesai', $month);
            default:
                return $query->whereNotIn('customer.status_id', [1, 2, 5]);
        }
    }

    // Mapping setiap baris data pelanggan
    public function map($customer): array
    {
        if ($this->type === 'ringkasan') {
            return [
                $customer->nama_paket,
                $customer->customer_count,
            ];
        }

        $tanggalSelesai = '-';
        if (!empty($customer->tanggal_selesai)) {
            try {
                $ts = $customer->tanggal_selesai;
                if (strtotime($ts) !== false) {
                    $tanggalSelesai = Carbon::parse($ts)->format('d-M-y H:i:s');
                }
            } catch (\Exception $e) {
                $tanggalSelesai = '-';
            }
        }

        $status = $this->getStatusText($customer->status_id);

        // **TAMBAHAN: Status Customer (Aktif/Deaktivasi)**
        $statusCustomer = 'Aktif';
        if ($customer->trashed()) {
            $statusCustomer = 'Deaktivasi';
        }

        // **TAMBAHAN: Pembayaran Terakhir - dari subquery**
        $pembayaranTerakhir = '-';
        if (!empty($customer->last_pembayaran_date)) {
            try {
                $pembayaranTerakhir = Carbon::parse($customer->last_pembayaran_date)->format('d-M-y');
            } catch (\Exception $e) {
                $pembayaranTerakhir = '-';
            }
        }

        // **TAMBAHAN: Periode Terakhir Bayar - bulan tagihan dari invoice yang terakhir dibayar**
        $periodePembayaran = '-';
        if (!empty($customer->periode_pembayaran)) {
            try {
                $periodePembayaran = Carbon::parse($customer->periode_pembayaran)->locale('id')->isoFormat('MMMM Y');
            } catch (\Exception $e) {
                $periodePembayaran = '-';
            }
        }

        return [
            $customer->id,
            $customer->nama_customer,
            $customer->alamat,
            $customer->no_hp,
            $customer->email,
            $status,
            $statusCustomer,
            $customer->paket?->nama_paket ?? '-',
            $customer->odp?->odc?->olt?->server?->lokasi_server ?? '-',
            $customer->odp?->odc?->olt?->nama_lokasi ?? '-',
            $customer->odp?->odc?->nama_odc ?? '-',
            $customer->odp?->nama_odp ?? '-',
            $customer->router?->nama_router ?? '-',
            $customer->agen?->name ?? '-',
            $customer->teknisi?->name ?? '-',
            $customer->koneksi?->nama_koneksi ?? '-',
            $customer->perangkat?->nama_perangkat ?? '-',
            $customer->media?->nama_media ?? '-',
            $customer->local_address ?? '-',
            $customer->remote_address ?? '-',
            $customer->remote ?? '-',
            $customer->usersecret ?? '-',
            $customer->pass_secret ?? '-',
            $customer->transiver ?? '-',
            $customer->receiver ?? '-',
            $customer->access_point ?? '-',
            $customer->station ?? '-',
            $customer->created_at?->format('d-m-Y H:i:s') ?? '-',
            $tanggalSelesai,
            $pembayaranTerakhir, // **KOLOM BARU: Pembayaran Terakhir**
            $periodePembayaran, // **KOLOM BARU: Periode Terakhir Bayar**
        ];
    }

    private function getStatusText($statusId)
    {
        $statusMap = [
            1 => 'Menunggu',
            2 => 'On Progress',
            3 => 'Aktif',
            4 => 'Maintenance',
            5 => 'Assigment',
            9 => 'Blokir',
            16 => 'Menunggu',
            17 => 'Menunggu'
        ];

        return $statusMap[$statusId] ?? '-';
    }

    // Heading kolom Excel
    public function headings(): array
    {
        if ($this->type === 'ringkasan') {
            return ['Nama Paket', 'Jumlah Pelanggan'];
        }

        return [
            'ID',
            'Nama Pelanggan',
            'Alamat',
            'No HP',
            'Email',
            'Status Koneksi',
            'Status Customer',
            'Paket',
            'Server',
            'OLT',
            'ODC',
            'ODP',
            'Router',
            'Agen',
            'Teknisi',
            'Koneksi',
            'Perangkat',
            'Media',
            'Local Address',
            'Remote Address',
            'Remote',
            'Usersecret',
            'Password Secret',
            'Transiver',
            'Receiver',
            'Access Point',
            'Station',
            'Tanggal Registrasi',
            'Tanggal Installasi',
            'Pembayaran Terakhir', // **KOLOM BARU**
            'Periode Terakhir Bayar',
        ];
    }

    // Styling sederhana untuk Excel
    public function styles(Worksheet $sheet)
    {
        return $sheet;
    }

    // Helper function untuk mendapatkan huruf kolom berdasarkan index
    private function getColumnLetter($index)
    {
        $letters = '';
        while ($index >= 0) {
            $letters = chr(65 + ($index % 26)) . $letters;
            $index = floor($index / 26) - 1;
        }
        return $letters;
    }

    // Event untuk styling lengkap
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Tentukan jumlah kolom berdasarkan tipe
                if ($this->type === 'ringkasan') {
                    $totalColumns = 2;
                    $headings = ['Nama Paket', 'Jumlah Pelanggan'];
                    $maxColumn = $this->getColumnLetter($totalColumns - 1); // B
                } else {
                    $totalColumns = 31; // 31 kolom (A sampai AD)
                    $headings = [
                        'ID',
                        'Nama Pelanggan',
                        'Alamat',
                        'No HP',
                        'Email',
                        'Status Koneksi',
                        'Status Customer',
                        'Paket',
                        'Server',
                        'OLT',
                        'ODC',
                        'ODP',
                        'Router',
                        'Agen',
                        'Teknisi',
                        'Koneksi',
                        'Perangkat',
                        'Media',
                        'Local Address',
                        'Remote Address',
                        'Remote',
                        'Usersecret',
                        'Password Secret',
                        'Transiver',
                        'Receiver',
                        'Access Point',
                        'Station',
                        'Tanggal Registrasi',
                        'Tanggal Installasi',
                        'Pembayaran Terakhir', // **KOLOM BARU**
                        'Periode Terakhir Bayar',
                    ];
                    $maxColumn = $this->getColumnLetter($totalColumns - 1); // AC
                }

                // Hapus heading otomatis yang sudah di-generate
                $sheet->fromArray([], null, 'A1');

                // Insert rows untuk judul
                $sheet->insertNewRowBefore(1, 3);

                // Set judul
                $sheet->setCellValue('A1', 'LAPORAN DATA PELANGGAN');
                $sheet->setCellValue('A2', 'Jenis: ' . $this->title);
                $sheet->setCellValue('A3', 'Tanggal Export: ' . Carbon::now()->format('d-m-Y H:i:s'));

                // Set heading manual di row 4
                for ($col = 0; $col < $totalColumns; $col++) {
                    $columnLetter = $this->getColumnLetter($col);
                    $sheet->setCellValue($columnLetter . '4', $headings[$col]);
                }

                // Merge cells untuk judul
                $sheet->mergeCells("A1:{$maxColumn}1");
                $sheet->mergeCells("A2:{$maxColumn}2");
                $sheet->mergeCells("A3:{$maxColumn}3");

                // Style untuk judul utama
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 16,
                        'color' => ['rgb' => '2C3E50'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'ECF0F1'],
                    ],
                ]);

                // Style untuk subtitle
                $sheet->getStyle('A2:A3')->applyFromArray([
                    'font' => [
                        'size' => 11,
                        'color' => ['rgb' => '7F8C8D'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'],
                    ],
                ]);

                // Set row height untuk judul
                $sheet->getRowDimension(1)->setRowHeight(30);
                $sheet->getRowDimension(2)->setRowHeight(20);
                $sheet->getRowDimension(3)->setRowHeight(20);
                $sheet->getRowDimension(4)->setRowHeight(25); // Header

                // Auto size columns
                for ($col = 0; $col < $totalColumns; $col++) {
                    $columnLetter = $this->getColumnLetter($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                // Style untuk header kolom (row 4)
                $headerRange = "A4:{$maxColumn}4";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                    ],
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
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                // Style untuk data (row 5 dan seterusnya)
                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 4) {
                    $dataRange = "A5:{$maxColumn}{$lastRow}";

                    // Border untuk data
                    $sheet->getStyle($dataRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'DDDDDD'],
                            ],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                    ]);

                    if ($this->type !== 'ringkasan') {
                        // Center alignment untuk kolom tertentu
                        $centerColumns = [0, 4, 5, 6, 7, 27, 28, 29, 30]; // ID, Status, Status Customer, Paket, Pembayaran Terakhir, Tanggal, Periode Terakhir Bayar
                        foreach ($centerColumns as $colIndex) {
                            $columnLetter = $this->getColumnLetter($colIndex);
                            $sheet->getStyle("{$columnLetter}5:{$columnLetter}{$lastRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        }

                        // Left alignment untuk teks panjang
                        $leftColumns = [1, 2, 3]; // Nama, No HP, Email
                        foreach ($leftColumns as $colIndex) {
                            $columnLetter = $this->getColumnLetter($colIndex);
                            $sheet->getStyle("{$columnLetter}5:{$columnLetter}{$lastRow}")
                                ->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        }

                        // Conditional formatting untuk Status Customer
                        for ($row = 5; $row <= $lastRow; $row++) {
                            $statusValue = $sheet->getCell($this->getColumnLetter(5) . $row)->getValue();
                            if ($statusValue === 'Deaktivasi') {
                                $sheet->getStyle($this->getColumnLetter(5) . $row)->applyFromArray([
                                    'font' => [
                                        'color' => ['argb' => Color::COLOR_RED],
                                        'bold' => true,
                                    ],
                                    'fill' => [
                                        'fillType' => Fill::FILL_SOLID,
                                        'startColor' => ['argb' => 'FEE2E2'],
                                    ],
                                ]);
                            }
                        }
                    }

                    // Alternating row colors
                    for ($row = 5; $row <= $lastRow; $row++) {
                        if ($row % 2 == 0) {
                            $sheet->getStyle("A{$row}:{$maxColumn}{$row}")
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB('F8F9FA');
                        }
                    }
                }

                // Freeze panes agar header tetap visible
                $sheet->freezePane('A5');

                // Tambahkan filter pada header
                $sheet->setAutoFilter("A4:{$maxColumn}4");
            },
        ];
    }
}