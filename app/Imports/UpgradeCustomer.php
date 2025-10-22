<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use App\Models\Router;
use App\Models\Paket;
use App\Services\MikrotikServices;
use App\Models\Invoice;
use App\Models\Customer;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;

class UpgradeCustomer implements ToModel, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function model(array $row)
    {
        if(empty($row['nama_customer'])){
            return null;
        }

        // Format tanggal_selesai dari Excel (2024-08-31 00:00:00) ke format database
        $tanggalSelesai = null;
        if (!empty($row['tanggal_selesai'])) {
            try {
                $tanggalSelesai = Carbon::createFromFormat('Y-m-d H:i:s', $row['tanggal_selesai'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Jika format tidak sesuai, coba format lain
                try {
                    $tanggalSelesai = Carbon::parse($row['tanggal_selesai'])->format('Y-m-d');
                } catch (\Exception $e) {
                    $tanggalSelesai = null;
                }
            }
        }

        return tap(Customer::updateOrCreate(
            [
                'nama_customer'  => $row['nama_customer'],
                'email'          => $row['email'] ?? null,
                'no_hp'          => $row['no_hp'],
                'alamat'         => $row['alamat'],
                'identitas'      => $row['identitas'] ?? null,
                'no_identitas'   => $row['no_identitas'] ?? null,
                'gps'            => $row['gps'] ?? null,
                'paket_id'       => $row['paket_id'] ?? null,
                'lokasi_id'      => $row['lokasi_id'] ?? null,
                'koneksi_id'     => $row['koneksi_id'] ?? null,
                'router_id'      => $row['router_id'] ?? null,
                'status_id'      => $row['status_id'] ?? null,
                'perangkat_id'   => $row['perangkat_id'] ?? null,
                'media_id'       => $row['media_id'] ?? null,
                'mac_address'    => $row['mac_address'] ?? null,
                'teknisi_id'     => $row['teknisi_id'] ?? null,
                'agen_id'        => $row['agen_id'] ?? null,
                'seri_perangkat' => $row['seri_perangkat'] ?? null,
                'usersecret'     => $row['usersecret'] ?? null,
                'pass_secret'    => $row['pass_secret'] ?? null,
                'local_address'  => $row['local_address'] ?? null,
                'remote_address' => $row['remote_address'] ?? null,
                'transiver'      => $row['transiver'] ?? null,
                'receiver'       => $row['receiver'] ?? null,
                'foto_rumah'     => $row['foto_rumah'] ?? null,
                'foto_perangkat' => $row['foto_perangkat'] ?? null,
                'panjang_kabel'  => $row['panjang_kabel'] ?? null,
                'redaman'        => $row['redaman'] ?? null,
                'tanggal_selesai' => $tanggalSelesai ?? null,
                'access_point'   => $row['access_point'] ?? null,
                'station'        => $row['station'] ?? null,
                'remote'         => $row['remote'] ?? null,
                'cek'            => 'Imported',
            ]
        ), function ($customer) use ($row) {
            $harga = $customer->paket->harga ?? 0;

            // Tentukan bulan invoice berdasarkan status_bayar
            $lunasBulan = (int) $row['status_bayar']; // bulan terakhir yang sudah dibayar
            $bulanInvoice = $lunasBulan + 1;

            $tahun = Carbon::now()->year;
            if ($bulanInvoice > 12) {
                $bulanInvoice = 1;
                $tahun++;
            }

            $periode = Carbon::createFromDate($tahun, $bulanInvoice, 1);

            Invoice::firstOrCreate([
                'customer_id'   => $customer->id,
                'paket_id'      => $customer->paket_id,
                'jatuh_tempo'   => $periode->copy()->endOfMonth(),
            ], [
                'status_id'     => 7,
                'tanggal_blokir' => $periode->copy()->day(10),
                'cek'           => 'Imported',
                'tagihan'       => $harga,
            ]);

            // try {
            //     $paket = Paket::find($customer->paket_id);

            //     if ($paket && $customer->usersecret) {
            //         $router = $customer->router; 
            //         if ($router) {
            //             $client = MikrotikServices::connect($router);
            //             if ($client) {
            //                 MikrotikServices::changeProfileUpgrade($client, $customer->usersecret, $paket->paket_name, $customer->local_address, $customer->remote_address);
            //                 MikrotikServices::removeActiveConnections($client, $customer->usersecret);
            //                 Log::info('Berhasil Upgrade melalui Import untuk Customer: ' . $customer->nama_customer);
            //             }
            //         }
            //     }
            // } catch (\Throwable $e) {
            //     Log::error("Gagal upgrade profile untuk {$customer->nama_customer}: " . $e->getMessage());
            // }
        });
    }
}
