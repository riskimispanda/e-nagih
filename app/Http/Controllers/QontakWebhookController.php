<?php

namespace App\Http\Controllers;

use App\Models\WhatsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QontakWebhookController extends Controller
{
        /**
         * Handle incoming webhook from Qontak
         *
         * URL should be added to Qontak Dashboard:
         * https://yourdomain.com/qontak/webhook
         */
        public function handle(Request $request)
        {
                $payload = $request->all();

                Log::info('Qontak Webhook Received', ['payload' => $payload]);

                /**
                 * Standard Qontak Webhook Payload for Delivery Receipts usually contains:
                 * {
                 *   "id": "broadcast_id",
                 *   "status": "delivered",
                 *   "error_message": null,
                 *   "recipient_number": "628...",
                 *   ...
                 * }
                 * Note: Structure can vary depending on Qontak configuration (Contact vs Broadcast).
                 */

                $broadcastId = $payload['id'] ?? $payload['broadcast_id'] ?? null;
                $status = $payload['status'] ?? $payload['execute_status'] ?? null;
                $error = $payload['error_message'] ?? null;

                if ($broadcastId) {
                        $log = WhatsLog::where('qontak_broadcast_id', $broadcastId)->first();

                        if ($log) {
                                $log->update([
                                        'status_pengiriman' => $status,
                                        'error_message' => $error ?: $log->error_message
                                ]);

                                // Update otomatis field cek di tabel invoice
                                if ($log->jenis_pesan === 'kirim_invoice' && preg_match('/Invoice Global \((.+) (\d{4})\)/', $log->pesan, $matches)) {
                                        $namaBulan = $matches[1];
                                        $tahun = $matches[2];
                                        
                                        $bulanMap = [
                                                'Januari' => 1, 'Februari' => 2, 'Maret' => 3, 'April' => 4,
                                                'Mei' => 5, 'Juni' => 6, 'Juli' => 7, 'Agustus' => 8,
                                                'September' => 9, 'Oktober' => 10, 'November' => 11, 'Desember' => 12
                                        ];
                                        
                                        $bulanAngka = $bulanMap[$namaBulan] ?? null;
                                        
                                        if ($bulanAngka) {
                                                $cekValue = (strtolower($status) === 'failed') ? 0 : 1;
                                                \App\Models\Invoice::where('customer_id', $log->customer_id)
                                                        ->whereMonth('jatuh_tempo', $bulanAngka)
                                                        ->whereYear('jatuh_tempo', $tahun)
                                                        ->update(['cek' => $cekValue]);
                                        }
                                } elseif ($log->jenis_pesan === 'warning_bayar') {
                                        $cekValue = (strtolower($status) === 'failed') ? 0 : 1;
                                        \App\Models\Invoice::where('customer_id', $log->customer_id)
                                                ->whereMonth('jatuh_tempo', $log->created_at->month)
                                                ->whereYear('jatuh_tempo', $log->created_at->year)
                                                ->update(['cek' => $cekValue]);
                                }

                                return response()->json(['success' => true, 'message' => 'Log updated']);
                        }
                }

                return response()->json(['success' => true, 'message' => 'No matching log found or invalid payload'], 200);
        }
}
