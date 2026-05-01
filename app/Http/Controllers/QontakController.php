<?php

namespace App\Http\Controllers;

use App\Services\QontakServices;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Carbon\Carbon;

class QontakController extends Controller
{
  protected $qontakService;

  public function __construct(QontakServices $qontakService)
  {
    $this->qontakService = $qontakService;
  }

  public function maintenance()
  {
    return view('qontak.maintenance', ['users' => auth()->user(), 'roles' => auth()->user()->roles]);
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
            'key' => (string) ($index + 1),
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
    return view('qontak.broadcast-logs', [
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

  /**
   * Get maintenance customers for UI
   * Data sourced from the customer table
   */
  public function getMaintenanceCustomers(): JsonResponse
  {
    try {
      // Get all customers from the customer table
      $customers = Customer::where('status_id', [3, 4])->orderBy('nama_customer', 'asc')
        ->get(['id', 'nama_customer', 'no_hp']);

      $list = [];
      foreach ($customers as $customer) {
        // Get latest maintenance log status if exists
        $log = DB::table('whats_log')
          ->where('customer_id', $customer->id)
          ->where('jenis_pesan', 'maintenance')
          ->orderBy('created_at', 'desc')
          ->first();

        $list[] = [
          'id' => $customer->id,
          'name' => $customer->nama_customer,
          'number' => $customer->no_hp,
          'status' => $log->status_pengiriman ?? 'never_sent',
        ];
      }

      return response()->json(['success' => true, 'data' => $list]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * Broadcast maintenance to selected recipients (UI-trigger)
   */
  public function broadcastMaintenance(Request $request): JsonResponse
  {
    try {
      $recipients = $request->input('recipients', []);

      if (!is_array($recipients) || empty($recipients)) {
        return response()->json(['success' => false, 'message' => 'Invalid recipients'], 400);
      }

      $results = [];
      $time = now()->format('d-m-Y');

      foreach ($recipients as $r) {
        $toNumber = $r['number'] ?? '';
        $name = $r['name'] ?? '';
        if (!$toNumber)
          continue;

        // Resolusi data customer
        $customer = Customer::where('no_hp', $toNumber)->first();
        if (!$customer) {
          $customer = new Customer(['nama_customer' => $name, 'no_hp' => $toNumber]);
        }

        // Cek apakah sudah pernah dikirim hari ini (mencegah duplikasi)
        if ($customer->id) {
          $alreadySent = DB::table('whats_log')
            ->where('customer_id', $customer->id)
            ->where('jenis_pesan', 'maintenance')
            ->whereDate('created_at', now()->toDateString())
            ->exists();

          if ($alreadySent) {
            $results[] = [
              'name' => $name,
              'number' => $toNumber,
              'sent' => false,
              'status' => 'already_sent'
            ];
            continue;
          }
        }

        $sent = false;
        $broadcastId = null;
        $status = 'failed';
        $errorMessage = null;

        try {
          // Memanggil fungsi maintenanceBroadcast yang sudah diperbaiki di Service
          $result = $this->qontakService->maintenanceBroadcast($toNumber, $customer);
          $sent = $result['success'] ?? false;
          $broadcastId = $result['message_id'] ?? null;
          $status = $sent ? ($result['status'] ?? 'pending') : 'failed';
          $errorMessage = $result['error'] ?? null;
        } catch (\Throwable $e) {
          \Log::error('Maintenance Broadcast Loop Error: ' . $e->getMessage());
          $errorMessage = $e->getMessage();
        }

        // Simpan ke log database
        try {
          DB::table('whats_log')->insert([
            'customer_id' => $customer->id ?? null,
            'no_tujuan' => $toNumber,
            'jenis_pesan' => 'maintenance',
            'pesan' => "Informasi Maintenance untuk {$name} pada {$time}",
            'status_pengiriman' => $status,
            'qontak_broadcast_id' => $broadcastId,
            'error_message' => $errorMessage,
            'created_at' => now(),
            'updated_at' => now(),
          ]);
        } catch (\Exception $e) {
          \Log::error('Failed to log maintenance broadcast: ' . $e->getMessage());
        }

        $results[] = [
          'name' => $name,
          'number' => $toNumber,
          'sent' => $sent,
          'status' => $status
        ];
      }

      return response()->json(['success' => true, 'data' => $results]);
    } catch (\Exception $e) {
      return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
  }

  /**
   * Helper untuk menghitung statistik log
   */
  private function calculateStats($statusFilter = null, $startDate = null, $endDate = null): array
  {
    $query = DB::table('whats_log');

    if ($statusFilter && $statusFilter !== 'all') {
      $query->where('status_pengiriman', $statusFilter);
    }

    if ($startDate) {
      $query->whereDate('created_at', '>=', $startDate);
    }

    if ($endDate) {
      $query->whereDate('created_at', '<=', $endDate);
    }

    $stats = $query->select('status_pengiriman', DB::raw('count(*) as total'))
      ->groupBy('status_pengiriman')
      ->get();

    $counts = [
      'total' => 0,
      'sent' => 0,
      'delivered' => 0,
      'read' => 0,
      'failed' => 0,
      'pending' => 0,
    ];

    foreach ($stats as $stat) {
      $status = $stat->status_pengiriman;
      $total = (int) $stat->total;
      $counts['total'] += $total;

      // Mapping status sesuai dengan UI (lihat whats_log.blade.php)
      if ($status === 'sent' || $status === 'done') {
        $counts['sent'] += $total;
      } elseif ($status === 'delivered') {
        $counts['delivered'] += $total;
      } elseif ($status === 'read') {
        $counts['read'] += $total;
      } elseif ($status === 'failed' || $status === 'error') {
        $counts['failed'] += $total;
      } elseif ($status === 'pending' || $status === 'todo') {
        $counts['pending'] += $total;
      }
    }

    return $counts;
  }

  /**
   * View DataTables WhatsLog
   */
  public function whatsLogView(Request $request)
  {
    $statusFilter = $request->input('status');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $counts = $this->calculateStats($statusFilter, $startDate, $endDate);

    return view('qontak.whats_log', [
      'users' => auth()->user(),
      'roles' => auth()->user()->roles,
      'counts' => $counts,
      'filters' => [
        'status' => $statusFilter,
        'start_date' => $startDate,
        'end_date' => $endDate
      ]
    ]);
  }

  /**
   * Get Data WhatsLog endpoint API untuk Datatables
   */
  public function whatsLogData(Request $request): JsonResponse
  {
    try {
      $statusFilter = $request->input('status');
      $startDate = $request->input('start_date');
      $endDate = $request->input('end_date');

      // Memulai query builder logs dengan join ke tabel customer
      $query = DB::table('whats_log')
        ->join('customer', 'whats_log.customer_id', '=', 'customer.id')
        ->select(
          'whats_log.id',
          'customer.nama_customer',
          'whats_log.no_tujuan',
          'whats_log.jenis_pesan',
          'whats_log.pesan',
          'whats_log.status_pengiriman',
          'whats_log.error_message',
          'whats_log.created_at',
          'whats_log.qontak_broadcast_id'
        );

      // Aplikasikan klausa WHERE kondisional jika status ada dan isinya bukan 'all'
      if ($statusFilter && $statusFilter !== 'all') {
        $query->where('whats_log.status_pengiriman', $statusFilter);
      }

      if ($startDate) {
        $query->whereDate('whats_log.created_at', '>=', $startDate);
      }

      if ($endDate) {
        $query->whereDate('whats_log.created_at', '<=', $endDate);
      }

      // Finalisasi query, order dan pelindung batas 5k agar ram tidak kepenuhan
      $logs = $query->orderBy('whats_log.created_at', 'desc')
        ->limit(5000)
        ->get();

      return response()->json([
        'data' => $logs,
        'stats' => $this->calculateStats($statusFilter, $startDate, $endDate)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to fetch logs: ' . $e->getMessage()
      ], 500);
    }
  }

  /**
   * Sync Logs Status Manual
   */
  public function syncLogs(Request $request): JsonResponse
  {
    try {
      $limit = $request->input('limit', 50); // Default to 50 as used in Blade
      $updatedCount = $this->qontakService->syncAllPendingLogs($limit);

      // Ambil filter agar statistik yang dikembalikan sesuai dengan filter aktif di UI
      $statusFilter = $request->input('status');
      $startDate = $request->input('start_date');
      $endDate = $request->input('end_date');

      return response()->json([
        'success' => true,
        'message' => "Sinkronisasi selesai. {$updatedCount} status berhasil diperbarui.",
        'updated_count' => $updatedCount,
        'stats' => $this->calculateStats($statusFilter, $startDate, $endDate)
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal sinkronisasi: ' . $e->getMessage()
      ], 500);
    }
  }
}
