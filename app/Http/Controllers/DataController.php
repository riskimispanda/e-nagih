<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Customer;
use App\Models\Metode;
use App\Models\Pembayaran;
use App\Events\UpdateBaru;
use Illuminate\Support\Facades\Cache;

class DataController extends Controller
{
    public function pelanggan()
    {
        $customers = Customer::with([
            'status',
            'paket',
            'invoice.status',
            'invoice',
            'getServer',
            'odp.odc.olt'
        ])->whereIn('status_id', [3, 9])
        ->orderBy('created_at', 'desc')
        ->get();
        
        // $coba = Customer::where('status_id', [3, 9])->get();

        $metode = Metode::all();
        $pembayaran = Pembayaran::where('status_id', 6)->get();
        
        // Format data sesuai kebutuhan frontend
        $customerData = $customers->map(function ($customer) {
            $latestInvoice = $customer->invoice->sortByDesc('created_at')->first();
            
            return [
            'id' => $customer->id,
            'nama_customer' => $customer->nama_customer ?? 'Unknown',
            'alamat' => $customer->alamat ?? '',
            'no_hp' => $customer->no_hp ?? '',
            'status_id' => $customer->status_id,
            'status' => [
                'id' => $customer->status->id ?? null,
                'nama_status' => $customer->status->nama_status ?? 'Unknown',
            ],
            'paket' => [
                'id' => $customer->paket->id ?? null,
                'nama_paket' => $customer->paket->nama_paket ?? 'Unknown',
            ],
            'getServer' => [
                'lokasi_server' => $customer->getServer->lokasi_server ?? 'Unknown'
            ],
            'invoice' => $customer->invoice->map(function ($invoice) {
                return [
                'id' => $invoice->id,
                'status' => [
                    'id' => $invoice->status->id ?? null,
                    'nama_status' => $invoice->status->nama_status ?? 'Unknown'
                ],
                'tagihan' => $invoice->tagihan,
                'jatuh_tempo' => $invoice->jatuh_tempo
                ];
            })->values()->toArray(),
            'updated_at' => $customer->updated_at ? $customer->updated_at->format('Y-m-d H:i:s') : null,
            ];
        });
        
        // Check for data changes
        $currentUpdate = md5($customerData->toJson());
        $lastUpdate = cache('last_customer_update');
        
        if (!$lastUpdate || $lastUpdate !== $currentUpdate) {
            $message = 'Data pelanggan berhasil diperbarui';
            event(new UpdateBaru(
            $customerData->toArray(),
            'success',
            $message
            ));
            cache(['last_customer_update' => $currentUpdate], now()->addMinutes(5));
        }
        
        return view('data.data-pelanggan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $customers,
            'metode' => $metode,
            'pembayaran' => $pembayaran,
            'customerData' => $customerData->toArray(),
        ]);
    }

    public function detailAntrianPelanggan($id)
    {
        $customer = Customer::with('router')->findOrFail($id);
        // dd($customer);
        return view('/teknisi/detail-antrian-pelanggan', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'data' => $customer,
        ]);
    }

}
