<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Response;
use App\Models\Pembayaran;
use App\Models\Kas;
use App\Models\Customer;
use App\Services\MikrotikServices;

class CallbackController extends Controller
{
    protected $privateKey = 'GrR9L-z2mOp-GHxjb-CNkbU-d3bO0';
    public function handle(Request $request)
    {
        $json = $request->getContent();
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $event = $request->server('HTTP_X_CALLBACK_EVENT');

        // Validasi Signature
        $signature = hash_hmac('sha256', $json, $this->privateKey);
        if ($signature !== (string) $callbackSignature) {
            return $this->jsonError('Invalid signature');
        }

        // Validasi Event
        if ($event !== 'payment_status') {
            return $this->jsonError('Unrecognized callback event, no action was taken');
        }

        // Decode JSON
        $data = json_decode($json);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonError('Invalid JSON data sent by Tripay');
        }

        // Ambil invoice berdasarkan reference Tripay
        $invoice = Invoice::where('reference', $data->reference)->first();
        if (! $invoice || empty($data->is_closed_payment)) {
            return $this->jsonError('No invoice found or already paid: ' . $data->reference);
        }

        // Proses berdasarkan status pembayaran
        switch (strtoupper((string) $data->status)) {
            case 'PAID':
                $this->handlePaid($invoice);
                break;

            case 'EXPIRED':
                $invoice->update(['status_id' => 7]);
                break;

            case 'FAILED':
                $invoice->update(['status_id' => 10]);
                break;

            default:
                return $this->jsonError('Unrecognized payment status');
        }

        \Log::info('Tripay Callback Success', (array) $data);
        return Response::json(['success' => true]);
    }

    protected function jsonError(string $message)
    {
        return Response::json([
            'success' => false,
            'message' => $message,
        ]);
    }
    
    protected function handlePaid($invoice)
    {
        $totalBayar = $invoice->tagihan + $invoice->tambahan;

        // Update status invoice menjadi lunas
        $invoice->update(['status_id' => 8]);

        // Ambil data customer
        $customer = Customer::find($invoice->customer_id);

        // Cek apakah customer sedang diblokir, lalu buka blokir
        if ($customer->status_id == 9) {
            $mikrotik = new MikrotikServices();
            $client = MikrotikServices::connect($customer->router);
            $mikrotik->removeActiveConnections($client, $customer->usersecret);
            $mikrotik->unblokUser($client, $customer->usersecret, $customer->paket->paket_name);

            // Update status customer menjadi aktif
            $customer->update(['status_id' => 3]);
        }

        // Simpan data pembayaran
        Pembayaran::create([
            'invoice_id' => $invoice->id,
            'jumlah_bayar' => $totalBayar,
            'tanggal_bayar' => now(),
            'metode_bayar' => $invoice->metode_bayar,
            'status_id' => 8,
            'invoice_id' => $invoice->id,
        ]);

        // Simpan ke kas
        Kas::create([
            'tanggal_kas' => now(),
            'debit' => $totalBayar,
            'kas_id' => 1,
            'status_id' => 3,
            'keterangan' => 'Pembayaran langganan dari ' . $customer->nama_customer . ' via ' . $invoice->metode_bayar,
        ]);

        // Hitung bulan depan
        $bulanDepan = \Carbon\Carbon::parse($invoice->jatuh_tempo)->addMonth();
        
        // Cek apakah invoice bulan depan sudah ada
        $sudahAda = Invoice::where('customer_id', $invoice->customer_id)
            ->whereMonth('jatuh_tempo', $bulanDepan->month)
            ->whereYear('jatuh_tempo', $bulanDepan->year)
            ->exists();

        if (! $sudahAda) {
            Invoice::create([
                'customer_id' => $invoice->customer_id,
                'tagihan' => $customer->paket->harga,
                'paket_id' => $customer->paket_id,
                'tambahan' => 0,
                'status_id' => 7, // Belum bayar
                'tanggal_invoice' => $bulanDepan->copy()->startOfMonth(),
                'tanggal_jatuh_tempo' => $bulanDepan->copy()->endOfMonth()->setTime(23, 59, 59),
                'tanggal_blokir' => $invoice->tanggal_blokir, // contoh: blokir akhir bulan
                'metode_bayar' => $invoice->metode_bayar,
            ]);
        }
    }


}
