<?php

namespace App\Services;

use App\Models\Customer;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class QontakServices
{
  protected $customer;
  protected $baseUrl;
  protected $hmacUsername;
  protected $hmacSecret;
  protected $channelId;

  public function __construct(?Customer $customer = null)
  {
    $this->customer = $customer;
    $this->hmacUsername = config('qontak.MEKARI_CLIENT_ID');
    $this->hmacSecret = config('qontak.MEKARI_SECRET_ID');
    $this->baseUrl = rtrim(config('qontak.MEKARI_API_BASE_URL'), '/');
    $this->channelId = config('qontak.CHANNEL_ID');
  }

  /**
   * Generate HMAC signature (exact match with Postman implementation)
   */
  private function generateHmacSignature($method, $url)
  {
    // Parse URL to get path and query
    $parsedUrl = parse_url($url);
    $path = $parsedUrl['path'] ?? '/';

    // Add query string if exists
    if (isset($parsedUrl['query']) && !empty($parsedUrl['query'])) {
      $path .= '?' . $parsedUrl['query'];
    }

    // Create request line exactly like Postman: "GET /path HTTP/1.1"
    $requestLine = strtoupper($method) . ' ' . $path . ' HTTP/1.1';

    // Create date string in GMT format
    $dateString = gmdate('D, d M Y H:i:s') . ' GMT';

    // Create string to sign: "date: <date>\n<request-line>"
    $stringToSign = 'date: ' . $dateString . "\n" . $requestLine;

    // Generate HMAC-SHA256 signature
    $digest = hash_hmac('sha256', $stringToSign, $this->hmacSecret, true);
    $signature = base64_encode($digest);

    return [
      'signature' => $signature,
      'date_string' => $dateString,
      'request_line' => $requestLine,
      'string_to_sign' => $stringToSign
    ];
  }

  /**
   * Get HMAC authentication headers
   */
  private function getHmacHeaders($method, $url)
  {
    $hmacData = $this->generateHmacSignature($method, $url);

    // Build HMAC header exactly like Postman
    $hmacHeader = sprintf(
      'hmac username="%s", algorithm="hmac-sha256", headers="date request-line", signature="%s"',
      $this->hmacUsername,
      $hmacData['signature']
    );

    return [
      'Authorization' => $hmacHeader,
      'Date' => $hmacData['date_string'],
      'Content-Type' => 'application/json',
      'Accept' => 'application/json'
    ];
  }

  /**
   * Make HTTP request with HMAC authentication
   */
  private function makeRequest($method, $endpoint, $data = [])
  {
    $url = $this->baseUrl . $endpoint;
    $headers = $this->getHmacHeaders($method, $url);

    try {
      $request = Http::withHeaders($headers);

      switch (strtoupper($method)) {
        case 'GET':
          if (!empty($data)) {
            $url .= '?' . http_build_query($data);
            // Regenerate headers with query string
            $headers = $this->getHmacHeaders($method, $url);
            $request = Http::withHeaders($headers);
          }
          $response = $request->get($url);
          break;

        case 'POST':
          $response = $request->post($url, $data);
          break;

        case 'PUT':
          $response = $request->put($url, $data);
          break;

        case 'PATCH':
          $response = $request->patch($url, $data);
          break;

        case 'DELETE':
          $response = $request->delete($url, $data);
          break;

        default:
          throw new Exception("Unsupported HTTP method: {$method}");
      }

      // Log request details for debugging
      Log::info('Qontak API Request', [
        'method' => $method,
        'url' => $url,
        'status' => $response->status(),
        'headers' => $headers
      ]);

      if (!$response->successful()) {
        Log::error('Qontak API Request Failed', [
          'method' => $method,
          'url' => $url,
          'status' => $response->status(),
          'response' => $response->body(),
          'data' => $data
        ]);

        $errorMessage = "Qontak API request failed with status {$response->status()}: " . $response->body();

        // Handle specific authentication errors
        if ($response->status() === 401) {
          $errorMessage = "Authentication failed: Invalid or expired access token. Please check your Qontak API credentials in the configuration.";
        }

        throw new Exception($errorMessage);
      }

      $responseData = $response->json();

      // Pastikan data adalah array, bukan string
      if (!is_array($responseData)) {
        Log::warning('Qontak API response is not an array', [
          'method' => $method,
          'url' => $url,
          'response' => $responseData
        ]);
        $responseData = [];
      }

      return [
        'success' => true,
        'status' => $response->status(),
        'data' => isset($responseData['data']) ? $responseData['data'] : $responseData
      ];

    } catch (Exception $e) {
      Log::error('Qontak API Request Exception', [
        'method' => $method,
        'url' => $url,
        'error' => $e->getMessage(),
        'data' => $data
      ]);

      return [
        'success' => false,
        'status' => $e->getCode(),
        'message' => $e->getMessage()
      ];
    }
  }

  /**
   * Test API connection with HMAC authentication
   */
  public function testConnection()
  {
    try {
      $result = $this->makeRequest('GET', '/qontak/chat/v1/templates/whatsapp');

      if ($result['success']) {
        return [
          'status' => 'success',
          'message' => 'Qontak API connection successful',
          'data' => $result['data']
        ];
      }

      return [
        'status' => 'error',
        'message' => $result['message'] ?? 'Connection failed'
      ];

    } catch (Exception $e) {
      return [
        'status' => 'error',
        'message' => 'Connection test failed: ' . $e->getMessage()
      ];
    }
  }

  /**
   * Get list of templates from Qontak API with caching support
   */
  public function getListTemplates($cacheKey = 'qontak_templates', $cacheDuration = 3600)
  {
    try {
      $cache = Cache::get($cacheKey);

      if ($cache) {
        return [
          'success' => true,
          'data' => $cache,
          'cached' => true
        ];
      }

      $result = $this->makeRequest('GET', '/qontak/chat/v1/templates/whatsapp');

      if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Failed to get templates');
      }

      $templates = $result['data'];

      Cache::put($cacheKey, $templates, $cacheDuration);

      return [
        'success' => true,
        'data' => $templates,
        'cached' => false
      ];
    } catch (Exception $e) {
      Log::error('Failed to get list templates: ' . $e->getMessage());

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
      ];
    }
  }

  private function getTemplateByName($name)
  {
    $result = $this->makeRequest('GET', '/qontak/chat/v1/templates/whatsapp');

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get templates');
    }

    $templates = is_array($result['data']) ? $result['data'] : [];

    $filteredTemplates = array_filter($templates, function ($template) use ($name) {
      return is_array($template) && isset($template['name']) && $template['name'] === $name;
    });

    return !empty($filteredTemplates) ? reset($filteredTemplates) : null;
  }

  public function konfirmasiPembayaran($to, $pembayaran)
  {
    try {
      // Load relationships jika belum diload
      if (!$pembayaran->relationLoaded('invoice')) {
        $pembayaran->load('invoice.customer');
      }
      if (!$pembayaran->invoice->relationLoaded('customer')) {
        $pembayaran->invoice->load('customer');
      }
      if (!$pembayaran->relationLoaded('user')) {
        $pembayaran->load('user');
      }

      // Validasi data yang diperlukan
      if (!$pembayaran->invoice) {
        throw new Exception('Invoice tidak ditemukan untuk pembayaran ini');
      }
      if (!$pembayaran->invoice->customer) {
        throw new Exception('Customer tidak ditemukan untuk invoice ini');
      }

      // 1. Cari template berdasarkan nama
      $template = $this->getTemplateByName('confirm_payment'); // atau nama template Anda

      if (!$template || !is_array($template) || !isset($template['id'])) {
        throw new Exception('Template konfirmasi pembayaran tidak ditemukan atau format tidak valid');
      }
      $url = url('/print-kwitansi/' . $pembayaran->invoice->id);


      // 2. Siapkan parameter untuk template
      $templateParams = [
        [
          'key' => '1', // sesuaikan dengan parameter di template
          'value' => 'tanggal_bayar',
          'value_text' => Carbon::parse($pembayaran->tanggal_bayar)->isoFormat('D MMMM Y')
        ],
        [
          'key' => '2',
          'value' => 'jumlah',
          'value_text' => number_format($pembayaran->jumlah_bayar ?? 0, 0, ',', '.')
        ],
        [
          'key' => '3',
          'value' => 'tunggakan',
          'value_text' => number_format($pembayaran->invoice->tunggakan ?? 0, 0, ',', '.')
        ],
        [
          'key' => '4',
          'value' => 'tipe',
          'value_text' => $pembayaran->metode_bayar
        ],
        [
          'key' => '5',
          'value' => 'nama',
          'value_text' => $pembayaran->invoice->customer->nama_customer ?? 'Pelanggan'
        ],
        [
          'key' => '6',
          'value' => 'admin',
          'value_text' => $pembayaran->user->name ?? 'Tripay'
        ],
        [
          'key' => '7',
          'value' => 'link',
          'value_text' => $url
        ]
      ];

      $formattedPhone = $this->formatNomor($to);

      // 3. Siapkan payload untuk mengirim pesan
      $messageData = [
        'to_number' => $formattedPhone,
        'to_name' => $pembayaran->invoice->customer->nama_customer ?? 'Pelanggan',
        'message_template_id' => $template['id'],
        'channel_integration_id' => $this->channelId,
        'language' => [
          'code' => 'id'
        ],
        'parameters' => [
          'body' => $templateParams
        ]
      ];

      // 4. Kirim pesan
      $result = $this->makeRequest(
        'POST',
        '/qontak/chat/v1/broadcasts/whatsapp/direct',
        $messageData
      );

      if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Gagal mengirim konfirmasi pembayaran');
      }

      // Pastikan data adalah array sebelum mengakses element
      $responseData = is_array($result['data']) ? $result['data'] : [];

      return [
        'success' => true,
        'message_id' => $responseData['id'] ?? null,
        'status' => $responseData['execute_status'] ?? null
      ];

    } catch (Exception $e) {
      // Log error
      error_log('Error konfirmasi pembayaran: ' . $e->getMessage());
      Log::info('Error Konfirmasi Pembayaran: ' . $e->getMessage());

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  public function notifProrate($to, $invoice)
  {
    try {
      // Load relationships jika belum diload
      if (!$invoice->relationLoaded('customer')) {
        $invoice->load('customer');
      }

      // 1. Cari template berdasarkan nama
      $template = $this->getTemplateByName('notif_prorate'); // atau nama template Anda

      if (!$template || !is_array($template) || !isset($template['id'])) {
        throw new Exception('Template konfirmasi pembayaran tidak ditemukan atau format tidak valid');
      }

      $url = url('/payment/invoice/' . encrypt($invoice->customer_id));


      $time = now()->format('dmY');

      // 2. Siapkan parameter untuk template
      $templateParams = [
        [
          'key' => '1', // sesuaikan dengan parameter di template
          'value' => 'nama',
          'value_text' => $invoice->customer->nama_customer
        ],
        [
          'key' => '2',
          'value' => 'tanggal',
          'value_text' => now()->format('d-m-Y')
        ],
        [
          'key' => '3',
          'value' => 'jumlah',
          'value_text' => $invoice->tagihan + $invoice->tunggakan + $invoice->tambahan
        ],
        [
          'key' => '4',
          'value' => 'no_invoice',
          'value_text' => $invoice->customer->nama_customer . '--' . $time
        ],
        [
          'key' => '5',
          'value' => 'link',
          'value_text' => $url
        ]
      ];

      $formattedPhone = $this->formatNomor($to);


      // 3. Siapkan payload untuk mengirim pesan
      $messageData = [
        'to_number' => $formattedPhone,
        'to_name' => $invoice->customer->nama_customer,
        'message_template_id' => $template['id'],
        'channel_integration_id' => $this->channelId,
        'language' => [
          'code' => 'id'
        ],
        'parameters' => [
          'body' => $templateParams
        ]
      ];

      // 4. Kirim pesan
      $result = $this->makeRequest(
        'POST',
        '/qontak/chat/v1/broadcasts/whatsapp/direct',
        $messageData
      );

      if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Gagal mengirim konfirmasi pembayaran');
      }

      // Pastikan data adalah array sebelum mengakses element
      $responseData = is_array($result['data']) ? $result['data'] : [];

      return [
        'success' => true,
        'message_id' => $responseData['id'] ?? null,
        'status' => $responseData['execute_status'] ?? null
      ];

    } catch (Exception $e) {
      // Log error
      error_log('Error konfirmasi pembayaran: ' . $e->getMessage());
      Log::info('Error Konfirmasi Pembayaran: ' . $e->getMessage());

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  public function notifTagihan($to, $invoice)
  {
    try {
      // Load relationships jika belum diload
      if (!$invoice->relationLoaded('customer')) {
        $invoice->load('customer');
      }

      // 1. Cari template berdasarkan nama
      $template = $this->getTemplateByName('payment_invoice'); // atau nama template Anda

      if (!$template || !is_array($template) || !isset($template['id'])) {
        throw new Exception('Template konfirmasi pembayaran tidak ditemukan atau format tidak valid');
      }

      $url = url('/payment/invoice/' . encrypt($invoice->customer_id));


      $time = now()->format('dmY');

      // 2. Siapkan parameter untuk template
      $templateParams = [
        [
          'key' => '1', // sesuaikan dengan parameter di template
          'value' => 'nama',
          'value_text' => $invoice->customer->nama_customer
        ],
        [
          'key' => '2',
          'value' => 'tanggal',
          'value_text' => Carbon::now()->format('d-F-Y')
        ],

        [
          'key' => '3',
          'value' => 'tagihan',
          'value_text' => number_format($invoice->tagihan + $invoice->tunggakan + $invoice->tambahan ?? 0, 0, ',', '.')
        ],

        [
          'key' => '4',
          'value' => 'tunggakan',
          'value_text' => number_format($invoice->tunggakan ?? 0, 0, ',', '.')
        ],
        [
          'key' => '5',
          'value' => 'no_invoice',
          'value_text' => $invoice->customer->nama_customer . '-' . $time
        ],
        [
          'key' => '6',
          'value' => 'link',
          'value_text' => $url
        ]
      ];

      $formattedPhone = $this->formatNomor($to);

      // 3. Siapkan payload untuk mengirim pesan
      $messageData = [
        'to_number' => $formattedPhone,
        'to_name' => $invoice->customer->nama_customer,
        'message_template_id' => $template['id'],
        'channel_integration_id' => $this->channelId,
        'language' => [
          'code' => 'id'
        ],
        'parameters' => [
          'body' => $templateParams
        ]
      ];

      // 4. Kirim pesan
      $result = $this->makeRequest(
        'POST',
        '/qontak/chat/v1/broadcasts/whatsapp/direct',
        $messageData
      );

      if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Gagal mengirim konfirmasi pembayaran');
      }

      // Pastikan data adalah array sebelum mengakses element
      $responseData = is_array($result['data']) ? $result['data'] : [];

      return [
        'success' => true,
        'message_id' => $responseData['id'] ?? null,
        'status' => $responseData['execute_status'] ?? null
      ];

    } catch (Exception $e) {
      // Log error
      error_log('Error konfirmasi pembayaran: ' . $e->getMessage());
      Log::info('Error Konfirmasi Pembayaran: ' . $e->getMessage());

      return [
        'success' => false,
        'error' => $e->getMessage()
      ];
    }
  }

  /**
   * Format nomor telepon ke format internasional (62) dengan validasi
   */
  private function formatNomor($phoneNumber)
  {
    // Jika null atau empty, return as-is
    if (empty($phoneNumber)) {
      return $phoneNumber;
    }

    // Hapus semua karakter non-digit
    $cleanNumber = preg_replace('/[^0-9]/', '', (string) $phoneNumber);

    // Jika kosong setelah dibersihkan
    if (empty($cleanNumber)) {
      return $phoneNumber;
    }

    // Cek panjang minimum (8-15 digit setelah 62)
    if (strlen($cleanNumber) < 10 || strlen($cleanNumber) > 16) {
      return $phoneNumber; // Return as-is jika tidak valid
    }

    // Format berdasarkan pola
    $patterns = [
      '/^62/' => '62',           // Sudah format 62
      '/^0/' => '62',           // Dimulai dengan 0
      '/^8/' => '62',           // Dimulai dengan 8 (tanpa 0)
      '/^\+62/' => '62',         // Dimulai dengan +62
    ];

    foreach ($patterns as $pattern => $replacement) {
      if (preg_match($pattern, $phoneNumber)) {
        $cleanNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Jika sudah 62, return as-is
        if (substr($cleanNumber, 0, 2) === '62') {
          return $cleanNumber;
        }

        // Jika dimulai dengan 0, ganti dengan 62
        if (substr($cleanNumber, 0, 1) === '0') {
          return '62' . substr($cleanNumber, 1);
        }

        // Jika dimulai dengan 8, tambahkan 62
        if (substr($cleanNumber, 0, 1) === '8') {
          return '62' . $cleanNumber;
        }
      }
    }

    // Default: return cleaned number
    return $cleanNumber;
  }

  /**
   * Get WhatsApp templates
   */
  public function getTemplates($params = [])
  {
    $result = $this->makeRequest('GET', '/qontak/chat/v1/templates/whatsapp', $params);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get templates');
    }

    return $result['data'];
  }

  /**
   * Get specific template by ID
   */
  public function getTemplate($templateId)
  {
    $result = $this->makeRequest('GET', "/qontak/chat/v1/templates/whatsapp/{$templateId}");

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get template');
    }

    return $result['data'];
  }

  /**
   * Send direct message/broadcast via Qontak
   */
  public function sendMessage($toNumber, $templateId, $parameters = [], $channelId = null)
  {
    // Format phone number (remove leading 0 and add 62 if needed)
    $toNumber = $this->formatPhoneNumber($toNumber);

    $payload = [
      'to_name' => 'Customer',
      'to_number' => $toNumber,
      'message_template_id' => $templateId,
      'channel_integration_id' => $channelId,
      'language' => [
        'code' => 'id'
      ]
    ];

    // Add parameters if provided
    if (!empty($parameters)) {
      $payload['parameters'] = $parameters;
    }

    $result = $this->makeRequest('POST', '/qontak/chat/v1/broadcasts/direct', $payload);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to send message');
    }

    return $result['data'];
  }

  /**
   * Send message to customer
   */
  public function sendToCustomer(Customer $customer, $templateId, $parameters = [], $channelId = null)
  {
    $phoneNumber = $customer->no_hp ?? $customer->no_telepon;

    if (!$phoneNumber) {
      throw new Exception('Customer phone number not found');
    }

    return $this->sendMessage($phoneNumber, $templateId, $parameters, $channelId);
  }

  /**
   * Send template message with body parameters
   */
  public function sendTemplateMessage($toNumber, $templateId, $bodyParams = [], $channelId = null)
  {
    $parameters = [
      'body' => []
    ];

    // Format body parameters sesuai format Qontak API
    // Format: key (1,2,3...), value (nama parameter), value_text (nilai parameter)
    foreach ($bodyParams as $index => $param) {
      if (is_array($param)) {
        // Jika parameter dalam format ['key' => 'nama', 'value_text' => 'Paijo']
        $parameters['body'][] = [
          'key' => $param['key'] ?? (string) ($index + 1),
          'value' => $param['value'] ?? 'message',
          'value_text' => $param['value_text'] ?? ''
        ];
      } else {
        // Jika parameter dalam format sederhana ['Paijo', 'INV-123']
        $parameters['body'][] = [
          'key' => (string) ($index + 1),
          'value' => 'message',
          'value_text' => $param
        ];
      }
    }

    return $this->sendMessage($toNumber, $templateId, $parameters, $channelId);
  }

  /**
   * Get broadcast history
   */
  public function getBroadcastHistory($params = [])
  {
    // Use the correct endpoint for WhatsApp broadcast logs
    $result = $this->makeRequest('GET', '/qontak/chat/v1/broadcasts/whatsapp', $params);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get broadcast history');
    }

    return $result['data'];
  }

  public function getDetail($params = [])
  {
    // Use the correct endpoint for WhatsApp broadcast logs
    $result = $this->makeRequest('GET', '/qontak/chat/v1/broadcasts/whatsapp', $params);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get broadcast history');
    }

    return $result['data'];
  }

  /**
   * Get broadcast log details by broadcast ID
   * Using the correct endpoint: /qontak/chat/v1/broadcasts/{broadcast_id}/whatsapp/log
   */
  public function getBroadcastLog($broadcastId)
  {
    $result = $this->makeRequest('GET', "/qontak/chat/v1/broadcasts/{$broadcastId}/whatsapp/log");

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get broadcast log details');
    }

    return $result['data'];
  }



  /**
   * Get channel integrations
   */
  public function getChannels()
  {
    $result = $this->makeRequest('GET', '/qontak/chat/v1/channels');

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get channels');
    }

    return $result['data'];
  }

  /**
   * Get contacts/customers from CRM
   */
  public function getContacts($params = [])
  {
    $result = $this->makeRequest('GET', '/qontak/crm/v1/contacts', $params);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get contacts');
    }

    return $result['data'];
  }

  /**
   * Create contact in CRM
   */
  public function createContact($contactData)
  {
    $result = $this->makeRequest('POST', '/qontak/crm/v1/contacts', $contactData);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to create contact');
    }

    return $result['data'];
  }

  /**
   * Format phone number to international format
   */
  private function formatPhoneNumber($phone)
  {
    // Remove all non-numeric characters
    $phone = preg_replace('/[^0-9]/', '', $phone);

    // Remove leading 0
    if (substr($phone, 0, 1) === '0') {
      $phone = substr($phone, 1);
    }

    // Add country code if not present
    if (substr($phone, 0, 2) !== '62') {
      $phone = '62' . $phone;
    }

    return $phone;
  }

  /**
   * Refresh templates cache and get fresh data from API
   */
  public function refreshTemplates()
  {
    try {
      Cache::forget('qontak_templates');

      $result = $this->makeRequest('GET', '/qontak/chat/v1/templates/whatsapp');

      if (!$result['success']) {
        throw new Exception($result['message'] ?? 'Failed to refresh templates');
      }

      $templates = $result['data'];

      Cache::put('qontak_templates', $templates, 3600);

      return [
        'success' => true,
        'data' => $templates,
        'message' => 'Templates refreshed successfully'
      ];
    } catch (Exception $e) {
      Log::error('Failed to refresh templates: ' . $e->getMessage());

      return [
        'success' => false,
        'message' => $e->getMessage(),
        'data' => null
      ];
    }
  }

  /**
   * Get templates by category or status
   */
  public function getTemplatesByFilter($filters = [])
  {
    $params = [];

    if (isset($filters['status']) && !empty($filters['status'])) {
      $params['status'] = $filters['status'];
    }

    if (isset($filters['language']) && !empty($filters['language'])) {
      $params['language'] = $filters['language'];
    }

    if (isset($filters['category']) && !empty($filters['category'])) {
      $params['category'] = $filters['category'];
    }

    $result = $this->makeRequest('GET', '/qontak/chat/v1/templates/whatsapp', $params);

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get templates');
    }

    $templates = $result['data'];

    if (isset($templates['data']) && is_array($templates['data'])) {
      $filtered = array_filter($templates['data'], function ($template) use ($filters) {
        if (isset($filters['name']) && !empty($filters['name'])) {
          $name = $template['name'] ?? $template['template_name'] ?? '';
          if (stripos($name, $filters['name']) === false) {
            return false;
          }
        }
        return true;
      });

      $templates['data'] = array_values($filtered);
    }

    return $templates;
  }

  /**
   * Clear templates cache
   */
  public function clearTemplatesCache()
  {
    Cache::forget('qontak_templates');

    return [
      'success' => true,
      'message' => 'Templates cache cleared'
    ];
  }

  /**
   * Debug HMAC authentication
   */
  public function debugAuth($endpoint = '/qontak/chat/v1/templates/whatsapp')
  {
    $url = $this->baseUrl . $endpoint;
    $method = 'GET';

    $hmacData = $this->generateHmacSignature($method, $url);
    $headers = $this->getHmacHeaders($method, $url);

    return [
      'credentials' => [
        'hmac_username' => $this->hmacUsername,
        'hmac_secret' => substr($this->hmacSecret, 0, 8) . '...' // Masked for security
      ],
      'signature_data' => [
        'method' => $method,
        'url' => $url,
        'date_string' => $hmacData['date_string'],
        'request_line' => $hmacData['request_line'],
        'string_to_sign' => $hmacData['string_to_sign'],
        'signature' => $hmacData['signature']
      ],
      'headers' => $headers,
      'test_result' => $this->testConnection()
    ];
  }

  /**
   * Get account information
   */
  public function getAccountInfo()
  {
    $result = $this->makeRequest('GET', '/qontak/public/v1/users/me');

    if (!$result['success']) {
      throw new Exception($result['message'] ?? 'Failed to get account info');
    }

    return $result['data'];
  }

  /**
   * Test broadcast endpoints
   */
  public function testBroadcastEndpoints()
  {
    $endpoints = [
      'list' => [
        '/qontak/chat/v1/broadcasts',
        '/qontak/chat/v1/whatsapp/broadcasts',
        '/chat/v1/broadcasts',
        '/v1/broadcasts',
        '/broadcasts',
        '/qontak/chat/v1/messages/broadcasts',
        '/qontak/v1/broadcasts'
      ],
      'detail' => [
        '/qontak/chat/v1/broadcasts/{id}/whatsapp/log',
        '/qontak/chat/v1/whatsapp/broadcasts/{id}/log',
        '/chat/v1/broadcasts/{id}/log',
        '/v1/broadcasts/{id}/log',
        '/broadcasts/{id}/log'
      ]
    ];

    $results = [];

    // Test list endpoints
    foreach ($endpoints['list'] as $endpoint) {
      try {
        $result = $this->makeRequest('GET', $endpoint, ['limit' => 1]);
        $results['list'][$endpoint] = [
          'success' => $result['success'],
          'status' => $result['status'] ?? 'unknown',
          'message' => $result['message'] ?? ($result['success'] ? 'Success' : 'Failed')
        ];
      } catch (Exception $e) {
        $results['list'][$endpoint] = [
          'success' => false,
          'status' => 'error',
          'message' => $e->getMessage()
        ];
      }
    }

    return $results;
  }

  /**
   * Test specific broadcast log endpoint
   */
  public function testBroadcastLogEndpoint($broadcastId, $endpoint)
  {
    try {
      $result = $this->makeRequest('GET', str_replace('{id}', $broadcastId, $endpoint));
      return [
        'success' => true,
        'endpoint' => $endpoint,
        'data' => $result['data'] ?? []
      ];
    } catch (Exception $e) {
      return [
        'success' => false,
        'endpoint' => $endpoint,
        'error' => $e->getMessage()
      ];
    }
  }
}
