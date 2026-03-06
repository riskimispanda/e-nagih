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

                                return response()->json(['success' => true, 'message' => 'Log updated']);
                        }
                }

                return response()->json(['success' => true, 'message' => 'No matching log found or invalid payload'], 200);
        }
}
