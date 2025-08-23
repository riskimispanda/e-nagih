<?php

namespace App\Imports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use App\Models\Router;
use App\Models\Perangkat;
use App\Models\MediaKoneksi;
use Illuminate\Support\Facades\Log;
use App\Models\Invoice;
use Carbon\Carbon;
use App\Models\ModemDetail;


class CustomerImport implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        if (empty($row['nama_customer'])) {
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
        ]), function ($customer) use ($row) {
            $customer->save();

            $harga = $customer->paket->harga ?? 0;

            // default invoice bulan ini
            $jatuhTempo = Carbon::now()->endOfMonth();

            if (isset($row['status_bayar']) && $row['status_bayar'] == 1) {
                // BUAT INVOICE BULAN INI (LUNAS)
                $invoiceBulanIni = Invoice::create([
                    'customer_id'    => $customer->id,
                    'status_id'      => 8,
                    'paket_id'       => $customer->paket_id,
                    'jatuh_tempo'    => Carbon::now()->endOfMonth(),
                    'tagihan'        => $harga,
                    'tanggal_blokir' => 10,
                ]);

                // BUAT INVOICE BULAN DEPAN (BELUM LUNAS)
                Invoice::create([
                    'customer_id'    => $customer->id,
                    'status_id'      => 7,
                    'paket_id'       => $customer->paket_id,
                    'jatuh_tempo'    => Carbon::now()->addMonth()->endOfMonth(),
                    'tagihan'        => $harga,
                    'tanggal_blokir' => 10,
                ]);
            } else {
                // BUAT INVOICE BULAN INI (BELUM LUNAS)
                Invoice::create([
                    'customer_id'    => $customer->id,
                    'status_id'      => 7,
                    'paket_id'       => $customer->paket_id,
                    'jatuh_tempo'    => $jatuhTempo,
                    'tagihan'        => $harga,
                    'tanggal_blokir' => 10,
                ]);
            }

            ModemDetail::create([
                'customer_id' => $customer->id,
                'logistik_id' => $customer->perangkat_id ?? 0,
                'mac_address' => $customer->mac_address,
                'serial_number' => $customer->seri_perangkat,
                'status_id' => 13
            ]);

        });
    }
}
