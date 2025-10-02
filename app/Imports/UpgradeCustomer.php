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

        return tap(new Customer([
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
            'tanggal_selesai'=> $row['tanggal_selesai'] ?? null,
            'access_point'   => $row['access_point'] ?? null,
            'station'        => $row['station'] ?? null,
            'remote'         => $row['remote'] ?? null,
            'cek'            => 'Imported'
        ]), function ($customer) use ($row) {
            $customer->save();
            $harga = $customer->paket->harga ?? 0;
            // Tentukan bulan invoice berdasarkan status_bayar
            $baseMonth = 7; // Juli sebagai awal
            $lunasBulan = (int) $row['status_bayar']; // 1=Juli, 2=Agustus, 3=September, 4=Oktober

            // Bulan berikutnya dari bulan lunas
            $bulanInvoice = $baseMonth + $lunasBulan; 

            $tahun = Carbon::now()->year;
            if ($bulanInvoice > 12) {
                $bulanInvoice = $bulanInvoice - 12;
                $tahun++;
            }

            $periode = Carbon::createFromDate($tahun, $bulanInvoice, 1);

            Invoice::create([
                'customer_id'  => $customer->id,
                'paket_id'     => $customer->paket_id,
                'status_id'    => 7,
                'tanggal_blokir' => 10,
                'cek' => 'Imported',
                'tagihan' => $harga,
                'jatuh_tempo'  => $periode->copy()->endOfMonth(),
            ]);

            try {
                $paket = Paket::find($customer->paket_id);
            
                if ($paket && $customer->usersecret) {
                    $router = $customer->router; 
                    if ($router) {
                        $client = MikrotikServices::connect($router);
                        if ($client) {
                            MikrotikServices::changeProfileUpgrade($client, $customer->usersecret, $paket->paket_name, $customer->local_address, $customer->remote_address);
                            MikrotikServices::removeActiveConnections($client, $customer->usersecret);
                            Log::info('Berhasil Upgrade melalui Import untuk Customer: ' . $customer->nama_customer);
                        }
                    }
                }
            } catch (\Throwable $e) {
                Log::error("Gagal upgrade profile untuk {$customer->nama_customer}: " . $e->getMessage());
            }            

        });        

    }
}
