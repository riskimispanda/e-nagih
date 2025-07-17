<?php

namespace App\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Invoice;
use Illuminate\Support\Facades\Response;
use App\Models\Pembayaran;
use App\Models\Kas;

class CallbackController extends Controller
{
    protected $privateKey = 'GrR9L-z2mOp-GHxjb-CNkbU-d3bO0';
    public function handle(Request $request)
    {
        $callbackSignature = $request->server('HTTP_X_CALLBACK_SIGNATURE');
        $json = $request->getContent();
        

        $signature = hash_hmac('sha256', $json, $this->privateKey);

        if ($signature !== (string) $callbackSignature) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid signature',
            ]);
        }

        if ('payment_status' !== (string) $request->server('HTTP_X_CALLBACK_EVENT')) {
            return Response::json([
                'success' => false,
                'message' => 'Unrecognized callback event, no action was taken',
            ]);
        }

        $data = json_decode($json);
        if (JSON_ERROR_NONE !== json_last_error()) {
            return Response::json([
                'success' => false,
                'message' => 'Invalid data sent by tripay',
            ]);
        }

        $tripayReference = $data->reference;
        $status = strtoupper((string) $data->status);

        if (!empty($data->is_closed_payment)) {
            $invoice = Invoice::where('reference', '=', $tripayReference)
                ->where('status_id', '=', 7)->first();

            if (! $invoice) {
                return Response::json([
                    'success' => false,
                    'message' => 'No invoice found or already paid: ' . $tripayReference,
                ]);
            }

            switch ($status) {
                case 'PAID':
                    $invoice->update(['status_id' => 8]);
                    $jumlahTagihan = $invoice->tagihan + $invoice->tambahan;
                    // Ambil Value Jumlah Kas
                    $jumlahKas = Kas::where('kas_id', 1)->latest('updated_at')->value('jumlah_kas') ?? 0;
                    $kasBesar = $jumlahTagihan + $jumlahKas;
                    // Simpan pembayaran
                    $pembayaran = new Pembayaran();
                    $pembayaran->invoice_id = $invoice->id;
                    $pembayaran->jumlah_bayar = $invoice->tagihan + $invoice->tambahan;
                    $pembayaran->tanggal_bayar = now();
                    $pembayaran->metode_bayar = $invoice->metode_bayar;
                    $pembayaran->status_id = 8;
                    $pembayaran->save();
                    // Tambah Kas
                    $kas = new Kas();
                    $kas->tanggal_kas = now();
                    $kas->jumlah_kas = $kasBesar;
                    $kas->debit = $invoice->tagihan + $invoice->tambahan;
                    $kas->kas_id = 1;
                    $kas->keterangan = 'Pembayaran langganan dari ' . $invoice->customer->nama_customer . ' By ' . $invoice->metode_bayar;
                    $kas->save();
                    break;

                case 'EXPIRED':
                    $invoice->update(['status_id' => 7]);
                    break;

                case 'FAILED':
                    $invoice->update(['status_id' => 10]);
                    break;

                default:
                    return Response::json([
                        'success' => false,
                        'message' => 'Unrecognized payment status',
                    ]);
            }

            return Response::json(['success' => true]);
            \Log::info('Tripay Callback Success', (array) $data);
        }
    }
}
