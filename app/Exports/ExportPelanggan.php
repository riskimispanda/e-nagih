<?php
namespace App\Exports;

use App\Models\Customer;
use App\Models\Paket;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Carbon\Carbon;

class ExportPelanggan implements FromCollection, WithHeadings, WithMapping
{
    protected $type;
    protected $paketId;

    public function __construct($type = 'semua', $paketId = null)
    {
        $this->type = $type;
        $this->paketId = $paketId;
    }

    public function collection()
    {
        // Eager load semua relasi
        $query = Customer::with([
            'paket', 
            'odp.odc.olt', 
            'router', 
            'agen', 
            'teknisi', 
            'koneksi', 
            'perangkat', 
            'media'
        ]);

        switch ($this->type) {
            case 'aktif':
                return $query->where('status_id', 3)->get();
            case 'nonaktif':
                return $query->where('status_id', 9)->get();
            case 'paket':
                return $query->where('paket_id', $this->paketId)->get();
            case 'ringkasan':
                return Paket::withCount('customer')->get();
            default:
                return $query->get();
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
                // Pastikan string bisa di-parse
                if (strtotime($ts) !== false) {
                    $tanggalSelesai = Carbon::parse($ts)->format('d-M-y H:i:s');
                }
            } catch (\Exception $e) {
                $tanggalSelesai = '-';
            }
        }

        return [
            $customer->id,
            $customer->nama_customer,
            $customer->no_hp,
            $customer->email,
            $customer->status?->nama_status ?? '-',
            $customer->paket?->nama_paket ?? '-',
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
            $tanggalSelesai
        ];
    }

    // Heading kolom Excel
    public function headings(): array
    {
        if ($this->type === 'ringkasan') {
            return ['Nama Paket', 'Jumlah Pelanggan'];
        }

        return [
            'ID',
            'Nama',
            'No HP',
            'Email',
            'Status',
            'Paket',
            'Lokasi',
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
            'Reciver',
            'Access Point',
            'Station',
            'Tanggal Registrasi',
            'Tanggal Installasi'
        ];
    }
}