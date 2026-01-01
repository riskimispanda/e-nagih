<?php

namespace App\Http\Controllers;

use App\Services\QontakServices;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class QontakController extends Controller
{
    protected $qontakService;

    public function __construct(QontakServices $qontakService)
    {
        $this->qontakService = $qontakService;
    }

    /**
     * Test Qontak API connection
     */
    public function testConnection(): JsonResponse
    {
        try {
            $result = $this->qontakService->testConnection();

            return response()->json([
                'success' => $result['status'] === 'success',
                'message' => $result['message'],
                'data' => $result['account'] ?? null
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Connection test failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message to customer
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'message' => 'required|string',
            'channel_id' => 'nullable|string',
            'template_id' => 'nullable|string',
            'template_name' => 'nullable|string',
            'template_language' => 'nullable|string'
        ]);

        try {
            $customer = Customer::findOrFail($request->customer_id);

            $parameters = [];
            $templateId = $request->template_id;

            if ($templateId) {
                $parameters['body'] = [
                    [
                        'key' => '1',
                        'value' => 'message',
                        'value_text' => $request->message
                    ]
                ];

                if ($request->has('template_params')) {
                    $templateParams = $request->input('template_params');
                    if (is_array($templateParams)) {
                        foreach ($templateParams as $param) {
                            $parameters['body'][] = [
                                'key' => $param['key'] ?? '',
                                'value_text' => $param['value_text'] ?? '',
                                'value' => $param['value'] ?? ''
                            ];
                        }
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Template selection is required for sending messages'
                ], 400);
            }

            $result = $this->qontakService->sendToCustomer(
                $customer,
                $templateId,
                $parameters,
                $request->channel_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully using template: ' . $request->template_name,
                'data' => $result,
                'template_used' => [
                    'id' => $templateId,
                    'name' => $request->template_name,
                    'language' => $request->template_language,
                    'parameters' => $parameters
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send message to custom number
     */
    public function sendToNumber(Request $request): JsonResponse
    {
        $request->validate([
            'to_number' => 'required|string',
            'message' => 'required|string',
            'channel_id' => 'nullable|string',
            'template_id' => 'nullable|string',
            'template_name' => 'nullable|string',
            'template_language' => 'nullable|string',
            'template_params' => 'nullable|array'
        ]);

        try {
            $parameters = [];
            $templateId = $request->template_id;

            if ($templateId) {
                $parameters['body'] = [
                    [
                        'key' => '1',
                        'value' => 'message',
                        'value_text' => $request->message
                    ]
                ];

                if ($request->has('template_params')) {
                    $templateParams = $request->input('template_params');
                    if (is_array($templateParams)) {
                        foreach ($templateParams as $param) {
                            $parameters['body'][] = [
                                'key' => $param['key'] ?? '',
                                'value_text' => $param['value_text'] ?? '',
                                'value' => $param['value'] ?? ''
                            ];
                        }
                    }
                }
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Template selection is required for sending messages'
                ], 400);
            }

            $result = $this->qontakService->sendMessage(
                $request->to_number,
                $templateId,
                $parameters,
                $request->channel_id
            );

            return response()->json([
                'success' => true,
                'message' => 'Message sent successfully using template: ' . $request->template_name,
                'data' => $result,
                'template_used' => [
                    'id' => $templateId,
                    'name' => $request->template_name,
                    'language' => $request->template_language,
                    'parameters' => $parameters
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send broadcast message to multiple recipients
     */
    public function sendBroadcast(Request $request): JsonResponse
    {
        $request->validate([
            'recipients' => 'required|array',
            'recipients.*.name' => 'required|string',
            'recipients.*.number' => 'required|string',
            'template_id' => 'required|string',
            'channel_integration_id' => 'required|string',
            'template_params' => 'required|array',
            'language_code' => 'nullable|string'
        ]);

        try {
            $results = [];
            $templateId = $request->template_id;
            $channelId = $request->channel_integration_id;
            $languageCode = $request->language_code ?? 'id';
            $templateParams = $request->template_params;

            foreach ($request->recipients as $recipient) {
                $parameters = [
                    'body' => []
                ];

                foreach ($templateParams as $index => $param) {
                    $parameters['body'][] = [
                        'key' => (string)($index + 1),
                        'value' => $param['key'] ?? 'message',
                        'value_text' => $param['value_text'] ?? $param['value'] ?? ''
                    ];
                }

                $result = $this->qontakService->sendMessage(
                    $recipient['number'],
                    $templateId,
                    $parameters,
                    $channelId
                );

                $results[] = [
                    'recipient' => $recipient['name'],
                    'number' => $recipient['number'],
                    'success' => $result['success'] ?? true,
                    'data' => $result['data'] ?? $result
                ];
            }

            return response()->json([
                'success' => true,
                'message' => 'Broadcast sent to ' . count($results) . ' recipients',
                'data' => [
                    'total_recipients' => count($results),
                    'successful' => count(array_filter($results, fn($r) => $r['success'])),
                    'failed' => count(array_filter($results, fn($r) => !$r['success'])),
                    'results' => $results
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send broadcast: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get account information
     */
    public function getAccountInfo(): JsonResponse
    {
        try {
            $result = $this->qontakService->getAccountInfo();

            return response()->json([
                'success' => true,
                'message' => 'Account information retrieved',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get account info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get WhatsApp templates
     */
    public function getTemplates(Request $request): JsonResponse
    {
        try {
            $forceRefresh = $request->input('refresh', false);
            $filters = $request->only(['status', 'language', 'category', 'name']);

            if (!empty($filters) || $forceRefresh) {
                if ($forceRefresh) {
                    $result = $this->qontakService->refreshTemplates();
                } else {
                    $result = $this->qontakService->getTemplatesByFilter($filters);
                    return response()->json([
                        'success' => true,
                        'message' => 'Templates retrieved with filters',
                        'data' => $result,
                        'filters' => $filters
                    ]);
                }
            } else {
                $result = $this->qontakService->getListTemplates();
            }

            return response()->json([
                'success' => true,
                'message' => 'Templates retrieved',
                'data' => $result['data'] ?? $result,
                'cached' => $result['cached'] ?? false
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh templates cache
     */
    public function refreshTemplates(): JsonResponse
    {
        try {
            $result = $this->qontakService->refreshTemplates();

            return response()->json($result);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refresh templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get active templates
     */
    public function getActiveTemplates(): JsonResponse
    {
        try {
            $result = $this->qontakService->getActiveTemplates();

            return response()->json([
                'success' => true,
                'message' => 'Active templates retrieved',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get active templates: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Debug authentication endpoints
     */
    public function debugAuth(): JsonResponse
    {
        try {
            $results = $this->qontakService->debugAuth();

            return response()->json([
                'success' => true,
                'message' => 'Debug results',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Debug failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broadcast history
     */
    public function getBroadcastHistory(Request $request): JsonResponse
    {
        try {
            $params = $request->only(['limit', 'offset', 'status', 'channel']);
            $result = $this->qontakService->getBroadcastHistory($params);

            return response()->json([
                'success' => true,
                'message' => 'Broadcast history retrieved',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get broadcast history: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broadcast detail
     */
    public function getBroadcastDetail($broadcastId): JsonResponse
    {
        try {
            $result = $this->qontakService->getBroadcastLog($broadcastId);

            return response()->json([
                'success' => true,
                'message' => 'Broadcast log retrieved',
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get broadcast log: ' . $e->getMessage()
            ], 500);
        }
    }

/**
     * Show broadcast logs page
     */
    public function showBroadcastLogs()
    {
        return view('qontak.broadcast-logs',[
          'users' => auth()->user(),
          'roles' => auth()->user()->roles
        ]);
    }

    /**
     * Show broadcast detail page
     */
    public function showBroadcastDetail($broadcastId)
    {
        try {
            $result = $this->qontakService->getBroadcastLog($broadcastId);
            
            return view('qontak.broadcast-detail', [
                'users' => auth()->user(),
                'roles' => auth()->user()->roles,
                'broadcastId' => $broadcastId,
                'broadcastData' => $result
            ]);
        } catch (\Exception $e) {
            return redirect()->route('qontak.broadcast-logs')
                ->with('error', 'Failed to load broadcast details: ' . $e->getMessage());
        }
    }

    /**
     * Test broadcast endpoints
     */
    public function testBroadcastEndpoints(): JsonResponse
    {
        try {
            $results = $this->qontakService->testBroadcastEndpoints();

            return response()->json([
                'success' => true,
                'message' => 'Endpoint testing completed',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Endpoint testing failed: ' . $e->getMessage()
            ], 500);
        }
}
}
