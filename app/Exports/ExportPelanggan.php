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
use PhpOffice\PhpSpreadsheet\Style\Conditional;
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
            ->whereNull('customer.deleted_at')
            ->orderBy('customer.id');

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
                    $totalColumns = 31; // 31 kolom (A sampai AE)
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
                    $maxColumn = $this->getColumnLetter($totalColumns - 1); // AE
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

                // Lebar kolom tetap. Auto-size memindai seluruh sel dan
                // memakan banyak memori untuk export ribuan baris.
                $columnWidths = $this->type === 'ringkasan'
                    ? ['A' => 30, 'B' => 20]
                    : [
                        'A' => 8, 'B' => 30, 'C' => 40, 'D' => 16, 'E' => 26,
                        'F' => 16, 'G' => 18, 'H' => 22, 'I' => 20, 'J' => 20,
                        'K' => 18, 'L' => 18, 'M' => 20, 'N' => 20, 'O' => 20,
                        'P' => 18, 'Q' => 22, 'R' => 16, 'S' => 16, 'T' => 16,
                        'U' => 16, 'V' => 18, 'W' => 18, 'X' => 14, 'Y' => 14,
                        'Z' => 20, 'AA' => 16, 'AB' => 20, 'AC' => 20, 'AD' => 20,
                        'AE' => 22,
                    ];

                foreach ($columnWidths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
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

                // Style data memakai conditional formatting agar tidak membuat
                // objek style baru untuk setiap sel (penyebab memory exhausted).
                $lastRow = $sheet->getHighestRow();
                if ($lastRow > 4) {
                    $zebra = new Conditional();
                    $zebra->setConditionType(Conditional::CONDITION_EXPRESSION);
                    $zebra->setConditions(['MOD(ROW(),2)=0']);
                    $zebra->getStyle()->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB('FFF8F9FA');
                    $sheet->getStyle("A5:{$maxColumn}{$lastRow}")
                        ->setConditionalStyles([$zebra]);

                    if ($this->type !== 'ringkasan') {
                        $statusColumn = $this->getColumnLetter(6); // Status Customer
                        $deaktivasi = new Conditional();
                        $deaktivasi->setConditionType(Conditional::CONDITION_CONTAINSTEXT);
                        $deaktivasi->setOperatorType(Conditional::OPERATOR_CONTAINSTEXT);
                        $deaktivasi->setText('Deaktivasi');
                        $deaktivasi->getStyle()->getFont()
                            ->setBold(true)
                            ->getColor()->setARGB(Color::COLOR_RED);
                        $deaktivasi->getStyle()->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FFFEE2E2');
                        $sheet->getStyle("{$statusColumn}5:{$statusColumn}{$lastRow}")
                            ->setConditionalStyles([$deaktivasi]);
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