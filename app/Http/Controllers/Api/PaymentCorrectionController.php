<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentCorrectionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class PaymentCorrectionController extends Controller
{
    protected PaymentCorrectionService $correctionService;

    public function __construct(PaymentCorrectionService $correctionService)
    {
        $this->correctionService = $correctionService;
    }

    /**
     * Analisa / Preview dampak kasus salah bayar sebelum dieksekusi (Read-Only)
     *
     * GET /api/payment-correction/analyze
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function analyze(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payer_customer_id' => 'required|integer',
            'wrong_customer_id' => 'required|integer',
            'wrong_invoice_id' => 'nullable|integer',
            'target_invoice_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $analysis = $this->correctionService->analyze(
            (int) $request->payer_customer_id,
            (int) $request->wrong_customer_id,
            $request->wrong_invoice_id ? (int) $request->wrong_invoice_id : null,
            $request->target_invoice_id ? (int) $request->target_invoice_id : null
        );

        if (!$analysis['can_proceed']) {
            return response()->json([
                'success' => false,
                'message' => $analysis['message']
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Analisa kasus salah bayar berhasil.',
            'data' => $analysis
        ]);
    }

    /**
     * Eksekusi perbaikan pemindahan pembayaran secara atomik
     *
     * POST /api/payment-correction/execute
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function execute(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payer_customer_id' => 'required|integer',
            'wrong_customer_id' => 'required|integer',
            'wrong_invoice_id' => 'nullable|integer',
            'target_invoice_id' => 'nullable|integer',
            'dry_run' => 'nullable|boolean',
            'send_wa' => 'nullable|boolean',
            'revert_wrong_customer_status' => 'nullable|boolean',
            'delete_wrong_next_invoice' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors()
            ], 422);
        }

        $result = $this->correctionService->execute($request->all());

        if (!$result['success']) {
            return response()->json($result, 400);
        }

        return response()->json($result, 200);
    }
}
