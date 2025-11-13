<?php
namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Exception;
use App\Models\Router;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Customer;

class MikrotikServices
{
    protected static array $klien = [];

    public static function connect(Router $router): Client
    {
        // Buat key unik berdasarkan ID atau kombinasi IP+Port
        $key = $router->id;

        if (!isset(self::$klien[$key])) {
            try {
                Log::info("🔌 Login pertama ke router: {$router->nama_router} ({$router->ip_address}:{$router->port})");

                self::$klien[$key] = new Client([
                    'host' => $router->ip_address,
                    'user' => $router->username,
                    'pass' => $router->password,
                    'port' => (int) $router->port,
                    'timeout' => 15,
                ]);

                // Cek koneksi langsung setelah connect
                self::$klien[$key]->connect();

            } catch (\Throwable $e) {
                Log::error("❌ Gagal konek ke router {$router->nama_router} ({$router->ip_address}): " . $e->getMessage());
                throw new \Exception("Tidak bisa konek ke router {$router->nama_router}");
            }
        } else {
            Log::info("♻️ Pakai koneksi cache untuk router: {$router->nama_router}");
        }

        return self::$klien[$key];
    }

    public static function checkPPPSecret($client, $usersecret)
    {
        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $response = $client->query($query)->read();

            return !empty($response) ? $response[0] : null;
        } catch (\Exception $e) {
            Log::error('Error checking PPP Secret: ' . $e->getMessage());
            return null;
        }
    }

    public static function updatePPPSecret($client, $secretId, $data)
    {
        try {
            Log::info("Updating PPP Secret - ID: {$secretId}, Data: " . json_encode($data));

            $query = new Query('/ppp/secret/set');
            $query->equal('.id', $secretId);

            foreach ($data as $key => $value) {
                if (!empty($value)) {
                    $query->equal($key, $value);
                    Log::info("Setting {$key} = {$value}");
                }
            }

            $response = $client->query($query)->read();
            Log::info("PPP Secret update response: " . json_encode($response));

            Log::info("PPP Secret updated successfully: {$data['name']}");
            return true;
        } catch (\Exception $e) {
            Log::error('Error updating PPP Secret: ' . $e->getMessage());
            return false;
        }
    }

    public static function changeProfileUpgrade(Client $client, $usersecret, $profileBaru, $localAddress = null, $remoteAddress = null)
    {
        try {
            // Cari PPP secret berdasarkan usersecret
            $query = (new Query('/ppp/secret/print'))
                ->where('name', $usersecret);
            $users = $client->query($query)->read();

            if (empty($users)) {
                Log::warning("PPP Secret tidak ditemukan untuk usersecret: {$usersecret}");
                return false;
            }

            foreach ($users as $user) {
                $setQuery = (new Query('/ppp/secret/set'))
                    ->equal('.id', $user['.id'])
                    ->equal('profile', $profileBaru);

                // kalau ada local_address isi juga
                if ($localAddress) {
                    $setQuery->equal('local-address', $localAddress);
                }

                // kalau ada remote_address isi juga
                if ($remoteAddress) {
                    $setQuery->equal('remote-address', $remoteAddress);
                }

                $client->query($setQuery)->read();
            }

            Log::info("✅ Berhasil ubah profile {$usersecret} ke {$profileBaru}, local={$localAddress}, remote={$remoteAddress}");
            return true;
        } catch (\Exception $e) {
            Log::error("❌ Gagal ubah profile {$usersecret}: {$e->getMessage()}");
            return false;
        }
    }

    /**
     * Mengaktifkan semua pelanggan dengan opsi tambahan
     */
    public static function activateAllCustomersAdvanced(Client $client, $options = [])
    {
        try {
            $defaultOptions = [
                'only_disabled' => true, // Hanya yang disabled = false
                'profile_filter' => null, // Filter by profile tertentu
                'limit' => null, // Batasi jumlah yang diaktifkan
                'delay_between_activation' => 0, // Delay antara aktivasi (detik)
            ];

            $options = array_merge($defaultOptions, $options);

            // Build query dasar
            $query = new Query('/ppp/secret/print');

            if ($options['only_disabled']) {
                $query->where('disabled', 'false');
            }

            if ($options['profile_filter']) {
                $query->where('profile', $options['profile_filter']);
            }

            $secrets = $client->query($query)->read();

            if (empty($secrets)) {
                Log::warning("Tidak ada PPP Secret yang ditemukan dengan filter yang diberikan");
                return [
                    'success' => false,
                    'message' => 'Tidak ada PPP Secret yang ditemukan',
                    'activated' => 0,
                    'total' => 0
                ];
            }

            // Apply limit jika ada
            if ($options['limit'] && $options['limit'] > 0) {
                $secrets = array_slice($secrets, 0, $options['limit']);
            }

            $activatedCount = 0;
            $alreadyActiveCount = 0;
            $failedCount = 0;
            $results = [];

            foreach ($secrets as $index => $secret) {
                $username = $secret['name'];
                $profile = $secret['profile'] ?? 'default';

                // Delay antara aktivasi jika di-set
                if ($options['delay_between_activation'] > 0 && $index > 0) {
                    sleep($options['delay_between_activation']);
                }

                // Cek apakah user sudah aktif
                if (self::isUserActive($client, $username)) {
                    $alreadyActiveCount++;
                    $results[] = [
                        'success' => true,
                        'username' => $username,
                        'message' => 'Already active',
                        'profile' => $profile
                    ];
                    continue;
                }

                // Aktifkan user
                $activationResult = self::activateSingleCustomer($client, $username, $profile);

                if ($activationResult['success']) {
                    $activatedCount++;
                } else {
                    $failedCount++;
                }

                $results[] = $activationResult;
            }

            $logMessage = "✅ Aktivasi selesai: {$activatedCount} berhasil, {$alreadyActiveCount} sudah aktif, {$failedCount} gagal dari total " . count($secrets) . " pelanggan";
            Log::info($logMessage);

            return [
                'success' => true,
                'message' => $logMessage,
                'activated' => $activatedCount,
                'already_active' => $alreadyActiveCount,
                'failed' => $failedCount,
                'total' => count($secrets),
                'details' => $results
            ];
        } catch (\Exception $e) {
            Log::error("❌ Gagal mengaktifkan semua pelanggan: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'activated' => 0,
                'total' => 0
            ];
        }
    }

    public static function isUserActive(Client $client, $username)
    {
        try {
            $query = (new Query('/ppp/active/print'))
                ->where('name', $username);
            $activeUsers = $client->query($query)->read();

            return !empty($activeUsers);
        } catch (\Exception $e) {
            Log::error("❌ Gagal cek status aktif user {$username}: {$e->getMessage()}");
            return false;
        }
    }

    public static function activateSingleCustomer(Client $client, $username, $profile = 'default')
    {
        try {
            // Cek apakah PPP secret exists
            $query = (new Query('/ppp/secret/print'))
                ->where('name', $username)
                ->where('disabled', 'false');
            $secrets = $client->query($query)->read();

            if (empty($secrets)) {
                Log::warning("PPP Secret tidak ditemukan atau disabled untuk username: {$username}");
                return [
                    'success' => false,
                    'username' => $username,
                    'message' => 'PPP Secret tidak ditemukan atau disabled',
                    'profile' => $profile
                ];
            }

            // Cek apakah sudah aktif
            if (self::isUserActive($client, $username)) {
                return [
                    'success' => true,
                    'username' => $username,
                    'message' => 'Already active',
                    'profile' => $profile
                ];
            }

            // Aktifkan user dengan profile yang sesuai
            $activateQuery = (new Query('/ppp/active/add'))
                ->equal('name', $username)
                ->equal('profile', $profile)
                ->equal('service', 'pppoe');

            $response = $client->query($activateQuery)->read();

            Log::info("✅ Berhasil mengaktifkan pelanggan: {$username} dengan profile: {$profile}");

            return [
                'success' => true,
                'username' => $username,
                'message' => 'Successfully activated',
                'profile' => $profile,
                'response' => $response
            ];
        } catch (\Exception $e) {
            Log::error("❌ Gagal mengaktifkan pelanggan {$username}: {$e->getMessage()}");
            return [
                'success' => false,
                'username' => $username,
                'message' => 'Error: ' . $e->getMessage(),
                'profile' => $profile
            ];
        }
    }

    public static function getFirewallRules(Router $router)
    {
        $client = new Client([
            'host' => $router->ip_address,
            'user' => $router->username,
            'pass' => $router->password,
            'port' => (int) $router->port,
        ]);

        // 1. cek ip service untuk api / api-ssl
        $q1 = new Query('/ip/service/print');
        $services = $client->query($q1)->read();

        // filter service api / api-ssl
        $apiServices = array_filter($services, function ($s) {
            return isset($s['name']) && in_array($s['name'], ['api', 'api-ssl']);
        });

        // 2. cek firewall input rule yang menyentuh port 8728/8729
        $q2 = new Query('/ip/firewall/filter/print');
        $filters = $client->query($q2)->read();

        $apiFilters = array_filter($filters, function ($f) {
            return (isset($f['dst-port']) && in_array($f['dst-port'], ['8728', '8729']))
                || (isset($f['comment']) && stripos($f['comment'], 'api') !== false);
        });

        // 3. ambil semua address-list yang dipakai oleh rule di atas
        $lists = [];
        foreach ($apiFilters as $f) {
            if (isset($f['src-address-list'])) $lists[] = $f['src-address-list'];
            if (isset($f['dst-address-list'])) $lists[] = $f['dst-address-list'];
        }
        $lists = array_values(array_unique(array_filter($lists)));

        // 4. ambil isi address-list
        $addressLists = [];
        foreach ($lists as $listName) {
            $q = (new Query('/ip/firewall/address-list/print'))->where('list', $listName);
            $addressLists[$listName] = $client->query($q)->read();
        }

        return response()->json([
            'api_services' => array_values($apiServices),
            'firewall_rules_touching_api_ports' => array_values($apiFilters),
            'address_list_entries' => $addressLists,
        ]);
    }


    public static function logInformation(Client $client, string $message = ''): void
    {
        try {
            // Ambil log dari MikroTik
            $query = new Query('/log/print');
            $logs = $client->query($query)->read();

            // Kalau mau filter pesan tertentu
            if (!empty($message)) {
                $logs = array_filter($logs, function ($log) use ($message) {
                    return stripos($log['message'] ?? '', $message) !== false;
                });
            }

            // Tampilkan di log Laravel
            foreach ($logs as $log) {
                $time = $log['time'] ?? 'unknown time';
                $topics = $log['topics'] ?? 'no topics';
                $msg = $log['message'] ?? 'no message';
                Log::info("[Mikrotik Log] {$time} [{$topics}] {$msg}");
            }

        } catch (\Exception $e) {
            Log::error("Gagal mengambil log dari MikroTik: " . $e->getMessage());
        }
    }

    public static function getUserSpeed($client, $usersecret)
    {
        // Cari PPP active
        $query = (new Query('/ppp/active/print'))
            ->where('name', $usersecret);

        $active = $client->query($query)->read();

        if (empty($active)) {
            return ['error' => "User {$usersecret} tidak aktif di PPPoE"];
        }

        // Bangun nama interface
        $iface = "pppoe-{$usersecret}";

        // Query monitor traffic (gunakan =once=true)
        $query = new Query('/interface/monitor-traffic');
        $query->equal('interface', $iface);
        $query->equal('once', 'true');

        $traffic = $client->query($query)->read();

        if (empty($traffic)) {
            return ['error' => "Data traffic kosong untuk {$iface}"];
        }

        $result = [
            'user'      => $usersecret,
            'interface' => $iface,
            'rx_bps'    => (int)($traffic[0]['rx-bits-per-second'] ?? 0),
            'tx_bps'    => (int)($traffic[0]['tx-bits-per-second'] ?? 0),
        ];

        Log::info("Speed user", $result);

        return $result;
    }

    public static function status(Router $router): array
    {
        try {
            $client = new Client([
                'host' => $router->ip_address,
                'user' => $router->username,
                'pass' => $router->password,
                'port' => (int)$router->port ?? 8728,
                'timeout' => 5,
            ]);

            $query = new Query('/system/resource/print');
            $response = $client->query($query)->read();

            return [
                'connected' => true,
                'uptime' => $response[0]['uptime'] ?? null,
                'version' => $response[0]['version'] ?? null,
                'cpu_load' => $response[0]['cpu-load'] ?? null,
                'platform' => $response[0]['platform'] ?? null,
            ];
        } catch (\Throwable $e) {
            return [
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }


    public static function detectMainInterface(Client $client)
    {
        $query = new Query('/interface/print');
        $interfaces = $client->query($query)->read();
    
        $filtered = collect($interfaces)->filter(function ($intf) {
            return isset($intf['running']) && $intf['running'] === 'true'
                && isset($intf['rx-byte']) && isset($intf['tx-byte'])
                && (!isset($intf['type']) || !str_contains($intf['type'], 'pppoe'))
                && (!isset($intf['name']) || !str_contains($intf['name'], '<'));
        });
    
        $sorted = $filtered->sortByDesc(function ($intf) {
            return ($intf['rx-byte'] ?? 0) + ($intf['tx-byte'] ?? 0);
        });
    
        return $sorted->first()['name'] ?? null;
    }
    
    public static function testKoneksi($ip, $port, $username, $password)
    {
        try {
            $client = new Client([
                'host'    => $ip,
                'user'    => $username,
                'pass'    => $password,
                'port'    => (int) $port,
                'timeout' => 5,
            ]);

            // Coba ambil data identitas router
            $query = new Query('/system/identity/print');
            $response = $client->query($query)->read();

            return [
                'success' => true,
                'message' => '✅ Berhasil konek ke router',
                'identity' => $response[0]['name'] ?? 'Tidak diketahui'
            ];
        } catch (ConnectException $e) {
            return [
                'success' => false,
                'message' => '❌ Tidak bisa konek ke router: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => '❌ Error lain: ' . $e->getMessage()
            ];
        }
    }

    public static function listInterfaces(Client $client)
    {
        $query = new Query('/interface/print');
        return $client->query($query)->read();
    }

    public static function testConnection(Client $client)
    {
        try {
            $query = new Query('/system/resource/print');
            return $client->query($query)->read();
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    public static function getProfile(Client $client)
    {
        $query = new Query('/system/identity/print');
        $response = $client->query($query)->read();

        return [
            'router_name' => $response[0]['name'] ?? 'Unknown'
        ];
    }

    /**
     * Mengganti profile semua customer dari ISOLIR ke profile paket sesuai database
     */
    public static function changeAllProfilesFromIsolirToPackage(Client $client)
    {
        try {
            // Dapatkan semua customer yang mungkin memiliki profile ISOLIR
            $customers = \App\Models\Customer::with(['paket', 'router'])
                ->whereNotNull('usersecret')
                ->where('usersecret', '!=', '')
                ->whereHas('paket') // Pastikan memiliki relasi paket
                ->get();

            if ($customers->isEmpty()) {
                Log::warning("Tidak ada customer yang ditemukan di database");
                return [
                    'success' => false,
                    'message' => 'Tidak ada customer yang ditemukan',
                    'updated' => 0,
                    'total' => 0
                ];
            }

            $updatedCount = 0;
            $failedCount = 0;
            $results = [];

            foreach ($customers as $customer) {
                $usersecret = $customer->usersecret;

                // Ambil profile name dari database relasi paket
                $profileName = self::getProfileNameFromPackage($customer->paket);

                // Ganti profile dari ISOLIR ke profile paket
                $updateResult = self::changeProfileFromIsolirToPackage($client, $usersecret, $profileName, $customer);

                if ($updateResult['success']) {
                    $updatedCount++;
                } else {
                    $failedCount++;
                }

                $results[] = $updateResult;
            }

            Log::info("✅ Berhasil mengupdate {$updatedCount} profile, {$failedCount} gagal dari total " . $customers->count() . " pelanggan");

            return [
                'success' => true,
                'message' => "Berhasil mengupdate {$updatedCount} profile, {$failedCount} gagal dari total " . $customers->count() . " pelanggan",
                'updated' => $updatedCount,
                'failed' => $failedCount,
                'total' => $customers->count(),
                'details' => $results
            ];
        } catch (\Exception $e) {
            Log::error("❌ Gagal mengupdate profile pelanggan: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'updated' => 0,
                'total' => 0
            ];
        }
    }

    /**
     * Mengganti profile single customer dari ISOLIR ke profile paket
     */
    public static function changeProfileFromIsolirToPackage(Client $client, $usersecret, $profileName, $customer = null)
    {
        try {
            // Cek apakah PPP secret exists di Mikrotik
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $users = $client->query($query)->read();

            if (empty($users)) {
                Log::warning("PPP Secret tidak ditemukan untuk usersecret: {$usersecret}");
                return [
                    'success' => false,
                    'usersecret' => $usersecret,
                    'message' => 'PPP Secret tidak ditemukan di Mikrotik',
                    'profile' => $profileName
                ];
            }

            $customerName = $customer ? $customer->nama_customer : 'Unknown';

            // Update profile PPP secret dari ISOLIR ke profile paket
            foreach ($users as $user) {
                $currentProfile = $user['profile'] ?? 'unknown';

                // Only update if current profile is ISOLIR/ISOLIREBILLING
                if ($currentProfile === 'ISOLIR' || $currentProfile === 'ISOLIREBILLING') {
                    $setQuery = new Query('/ppp/secret/set');
                    $setQuery->equal('.id', $user['.id']);
                    $setQuery->equal('profile', $profileName);
                    $client->query($setQuery)->read();

                    Log::info("✅ Berhasil ganti profile {$usersecret} dari {$currentProfile} ke {$profileName} - {$customerName}");
                } else {
                    Log::info("⏭️ Profile sudah bukan ISOLIR: {$usersecret} - Current: {$currentProfile} - {$customerName}");
                    return [
                        'success' => true,
                        'usersecret' => $usersecret,
                        'customer_name' => $customerName,
                        'message' => 'Profile sudah bukan ISOLIR, tidak perlu update',
                        'current_profile' => $currentProfile,
                        'target_profile' => $profileName,
                        'skipped' => true
                    ];
                }
            }

            return [
                'success' => true,
                'usersecret' => $usersecret,
                'customer_name' => $customerName,
                'message' => 'Successfully updated profile from ISOLIR to package profile',
                'previous_profile' => 'ISOLIR',
                'new_profile' => $profileName,
                'skipped' => false
            ];
        } catch (\Exception $e) {
            $customerName = $customer ? $customer->nama_customer : 'Unknown';
            Log::error("❌ Gagal update profile {$usersecret}: {$e->getMessage()}");
            return [
                'success' => false,
                'usersecret' => $usersecret,
                'customer_name' => $customerName,
                'message' => 'Error: ' . $e->getMessage(),
                'profile' => $profileName
            ];
        }
    }

    /**
     * Mendapatkan nama profile berdasarkan paket customer dari database
     */
    private static function getProfileNameFromPackage($paket)
    {
        if (!$paket) {
            Log::warning("Paket tidak ditemukan, menggunakan profile default");
            return 'default';
        }

        // Ambil langsung dari field paket_name di database
        // Asumsi: nama profile di Mikrotik sama dengan nama paket di database
        $profileName = $paket->paket_name;

        if (!$profileName) {
            Log::warning("Paket name kosong untuk paket ID: {$paket->id}, menggunakan default");
            return 'default';
        }

        return $profileName;
    }

    /**
     * Method untuk mengganti profile dengan filter tertentu
     */
    public static function changeProfilesFromIsolirToPackageWithFilter(Client $client, $options = [])
    {
        try {
            $defaultOptions = [
                'router_id' => null, // Filter by router tertentu
                'paket_id' => null, // Filter by paket tertentu
                'limit' => null, // Batasi jumlah
            ];

            $options = array_merge($defaultOptions, $options);

            // Query customers dengan filter
            $query = \App\Models\Customer::with(['paket', 'router'])
                ->whereNotNull('usersecret')
                ->where('usersecret', '!=', '')
                ->whereHas('paket');

            if ($options['router_id']) {
                $query->where('router_id', $options['router_id']);
            }

            if ($options['paket_id']) {
                $query->where('paket_id', $options['paket_id']);
            }

            if ($options['limit']) {
                $customers = $query->limit($options['limit'])->get();
            } else {
                $customers = $query->get();
            }

            if ($customers->isEmpty()) {
                Log::warning("Tidak ada customer yang ditemukan dengan filter yang diberikan");
                return [
                    'success' => false,
                    'message' => 'Tidak ada customer yang ditemukan',
                    'updated' => 0,
                    'total' => 0
                ];
            }

            $updatedCount = 0;
            $failedCount = 0;
            $skippedCount = 0;
            $results = [];

            foreach ($customers as $customer) {
                $usersecret = $customer->usersecret;
                $profileName = self::getProfileNameFromPackage($customer->paket);

                $updateResult = self::changeProfileFromIsolirToPackage($client, $usersecret, $profileName, $customer);

                if ($updateResult['success']) {
                    if (isset($updateResult['skipped']) && $updateResult['skipped']) {
                        $skippedCount++;
                    } else {
                        $updatedCount++;
                    }
                } else {
                    $failedCount++;
                }

                $results[] = $updateResult;
            }

            $logMessage = "✅ Update profile selesai: {$updatedCount} diupdate, {$skippedCount} dilewati, {$failedCount} gagal dari total " . $customers->count() . " pelanggan";
            Log::info($logMessage);

            return [
                'success' => true,
                'message' => $logMessage,
                'updated' => $updatedCount,
                'skipped' => $skippedCount,
                'failed' => $failedCount,
                'total' => $customers->count(),
                'details' => $results
            ];
        } catch (\Exception $e) {
            Log::error("❌ Gagal mengupdate profile pelanggan: {$e->getMessage()}");
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'updated' => 0,
                'total' => 0
            ];
        }
    }


    public static function changeUserProfile(Client $client, $usersecret)
    {
        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $users = $client->query($query)->read();

            if (empty($users)) return false;

            foreach ($users as $user) {
                $setQuery = new Query('/ppp/secret/set');
                $setQuery->equal('.id', $user['.id']);
                $setQuery->equal('profile', 'ISOLIREBILLING');
                $client->query($setQuery)->read();
            }
            Log::info('MikrotikServices::changeUserProfile Success: ' . $usersecret);
            return true;
        } catch (Exception $e) {
            Log::info('MikrotikServices::changeUserProfile error: ' . $e->getMessage());
            return false;
        }
    }

    public static function changeUserProfileSingle(Client $client, $usersecret)
    {
        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $users = $client->query($query)->read();

            if (empty($users)) {
                Log::warning('User not found: ' . $usersecret);
                return false;
            }

            // Jika ada multiple users, return false untuk mencegah perubahan yang tidak diinginkan
            if (count($users) > 1) {
                Log::error('Multiple users found for: ' . $usersecret . '. Operation aborted.');
                return false;
            }

            // Hanya ada 1 user, lanjutkan proses
            $user = $users[0];

            $setQuery = new Query('/ppp/secret/set');
            $setQuery->equal('.id', $user['.id']);
            $setQuery->equal('profile', 'ISOLIREBILLING');
            $client->query($setQuery)->read();

            Log::info('MikrotikServices::changeUserProfile Success - User: ' . $usersecret . ', ID: ' . $user['.id']);
            return true;
        } catch (Exception $e) {
            Log::error('MikrotikServices::changeUserProfile error: ' . $e->getMessage());
            return false;
        }
    }

    public static function UpgradeDowngrade(Client $client, string $usersecret, string $newProfile): bool
    {
        try {
            if (empty($usersecret) || empty($newProfile)) {
                Log::warning("UpgradeDowngrade: usersecret atau profile kosong", [
                    'usersecret' => $usersecret,
                    'newProfile' => $newProfile
                ]);
                return false;
            }

            // Cari user dengan approach yang lebih safe
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $users = $client->query($query)->read();

            // Debug: Log response dari Mikrotik
            Log::info("Mikrotik User Search Response", [
                'usersecret' => $usersecret,
                'users_count' => count($users),
                'users_data' => $users
            ]);

            if (empty($users)) {
                Log::warning("UpgradeDowngrade: user tidak ditemukan", [
                    'usersecret' => $usersecret
                ]);
                return false;
            }

            $user = $users[0] ?? null;

            if (!$user) {
                Log::warning("UpgradeDowngrade: user data kosong", [
                    'usersecret' => $usersecret
                ]);
                return false;
            }

            // Cek jika .id exists dalam response
            if (!isset($user['.id'])) {
                Log::error("UpgradeDowngrade: .id tidak ditemukan dalam response", [
                    'usersecret' => $usersecret,
                    'user_data' => $user
                ]);
                return false;
            }

            $userId = $user['.id'];

            // ✅ Check jika profile sudah sama
            $currentProfile = $user['profile'] ?? null;
            if ($currentProfile === $newProfile) {
                Log::info("UpgradeDowngrade: profile sama, skip update", [
                    'usersecret' => $usersecret,
                    'profile' => $newProfile
                ]);
                return true;
            }

            // Update profile dengan approach yang lebih robust
            $setQuery = new Query('/ppp/secret/set');
            $setQuery->equal('.id', $userId);
            $setQuery->equal('profile', $newProfile);

            $result = $client->query($setQuery)->read();

            Log::info("UpgradeDowngrade: profile updated successfully", [
                'usersecret' => $usersecret,
                'oldProfile' => $currentProfile,
                'newProfile' => $newProfile,
                'user_id' => $userId,
                'result' => $result
            ]);

            return true;
        } catch (Exception $e) {
            Log::error("UpgradeDowngrade ERROR", [
                'usersecret' => $usersecret,
                'newProfile' => $newProfile,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public static function getProfiles(Client $client)
    {
        $query = new Query('/ppp/profile/print');
        $response = $client->query($query)->read();

        $profiles = [];
        foreach ($response as $profile) {
            $profiles[] = [
                'name' => $profile['name'] ?? 'Unknown',
                'local_address' => $profile['local-address'] ?? 'N/A',
                'remote_address' => $profile['remote-address'] ?? 'N/A',
                'rate_limit' => $profile['rate-limit'] ?? 'N/A'
            ];
        }

        return $profiles;
    }

    public static function getUserProfiles(Client $client)
    {
        $query = new Query('/ppp/secret/print');
        return $client->query($query)->read();
    }

    public static function getRouterDetailsByName(Client $client, $routerName)
    {
        $query = new Query('/system/identity/print');
        $response = $client->query($query)->read();

        foreach ($response as $router) {
            if ($router['name'] == $routerName) {
                return [
                    'name' => $router['name'] ?? 'Unknown',
                ];
            }
        }

        return [];
    }

    public static function addPPPSecret(Client $client, $data)
    {
        // dd($client);
        $query = new Query('/ppp/secret/add');
        $query->equal('name', $data['name']);
        $query->equal('password', $data['password']);
        $query->equal('profile', $data['profile']);
        $query->equal('service', $data['service']);
        if (!empty($data['localAddress'])) {
            $query->equal('local-address', $data['localAddress']);
        }
        if (!empty($data['remoteAddress'])) {
            $query->equal('remote-address', $data['remoteAddress']);
        }
        $query->equal('comment', 'Created by NBilling');

        try {
            $client->query($query)->read();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    // Di MikrotikServices
    public static function checkUserExists($client, $usersecret)
    {
        try {
            $client->write('/ppp/secret/print', [
                '?name' => $usersecret
            ]);
            $users = $client->read();

            return !empty($users);
        } catch (\Exception $e) {
            Log::error('Check User Error: ' . $e->getMessage());
            return false;
        }
    }
    public static function getPPPSecret(Client $client)
    {
        try {
            $query = new Query('/ppp/secret/print');
            // $query->where('comment', 'Created by NBilling');
            $query->where('name', '011010520245.Siti-Cotimah-Pacar@megaroute.net.id');
            return $client->query($query)->read();
        } catch (\Exception $e) {
            Log::error('Gagal mengambil PPP Secret: ' . $e->getMessage());
            return null;
        }
    }

    public static function editUserSecret(Client $client, string $id, array $data)
    {
        // Pastikan data wajib ada
        if (!$id || empty($data)) {
            throw new \Exception("ID atau data tidak boleh kosong.");
        }

        // Buat query set dengan .id
        $query = new Query('/ppp/secret/set');
        $query->equal('.id', $id); // filter berdasarkan .id

        // Set semua field yang ingin diupdate
        if (isset($data['name']))           $query->equal('name', $data['name']);
        if (isset($data['password']))       $query->equal('password', $data['password']);
        if (isset($data['remoteAddress']))  $query->equal('remote-address', $data['remoteAddress']);
        if (isset($data['localAddress']))   $query->equal('local-address', $data['localAddress']);
        if (isset($data['profile']))        $query->equal('profile', $data['profile']);
        if (isset($data['service']))        $query->equal('service', $data['service']);

        // Eksekusi query
        return $client->query($query)->read();
    }



    public static function trafficPelanggan(Router $router, string $usersecret): array
    {
        try {
            $client = self::connect($router);

            // Ambil data aktif PPPoE user
            $active = $client->query(
                (new Query('/ppp/active/print'))->where('name', $usersecret)
            )->read();

            if (count($active) === 0) {
                return [
                    'message' => 'PPP tidak ditemukan untuk user: ' . $usersecret,
                    'rx' => 0,
                    'tx' => 0,
                    'total_rx' => 0,
                    'total_tx' => 0,
                    'uptime' => null,
                    'ip_remote' => null,
                    'ip_local' => null,
                    'mac_address' => null,
                    'profile' => null,
                    'status' => 'offline',
                ];
            }

            $interfaceName = $active[0]['interface'] ?? '<pppoe-' . $usersecret . '>';

            // Ambil trafik realtime (upload/download)
            $trafficData = $client->query(
                (new Query('/interface/monitor-traffic'))
                    ->equal('interface', $interfaceName)
                    ->equal('once', 'true')
            )->read();

            // Ambil profile dari secret
            $secret = $client->query(
                (new Query('/ppp/secret/print'))->where('name', $usersecret)
            )->read();

            $profile = $secret[0]['profile'] ?? null;

            return [
                'message'     => 'Berhasil mendapatkan trafik',
                'rx'          => (int) ($trafficData[0]['rx-bits-per-second'] ?? 0), // Download realtime
                'tx'          => (int) ($trafficData[0]['tx-bits-per-second'] ?? 0), // Upload realtime
                'total_rx'    => (int) ($active[0]['bytes-in'] ?? 0),  // Total download selama sesi
                'total_tx'    => (int) ($active[0]['bytes-out'] ?? 0), // Total upload selama sesi
                'uptime'      => $active[0]['uptime'] ?? null,
                'ip_remote'   => $active[0]['address'] ?? null,        // IP pelanggan
                'ip_local'    => $active[0]['local-address'] ?? null,  // IP Mikrotik
                'mac_address' => $active[0]['caller-id'] ?? null,      // biasanya MAC modem/router pelanggan
                'profile'     => $profile ?? null,        // paket/profile pelanggan
                'service'     => $active[0]['service'] ?? null,        // service type (pppoe/ovpn/l2tp)
                'encoding'    => $active[0]['encoding'] ?? null,       // metode enkripsi/auth
                'status'      => 'online',
            ];
        } catch (\Throwable $e) {
            Log::error('Gagal ambil trafik pelanggan: ' . $e->getMessage());
            return [
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
                'rx' => 0,
                'tx' => 0,
                'total_rx' => 0,
                'total_tx' => 0,
                'uptime' => null,
                'ip_remote' => null,
                'ip_local' => null,
                'mac_address' => null,
                'profile' => null,
                'service' => null,
                'encoding' => null,
                'status' => 'error',
            ];
        }
    }

    public static function getInterfacePelanggan(Client $client, string $usersecret = null): array
    {
        try {
            $result = [];

            // Ambil semua interface
            $interfaces = $client->query(
                new Query('/interface/print')
            )->read();

            foreach ($interfaces as $iface) {
                $name = $iface['name'] ?? null;
                if (!$name) {
                    continue;
            }

                // Monitor traffic per interface
                $trafficData = $client->query(
                    (new Query('/interface/monitor-traffic'))
                        ->equal('interface', $name)
                        ->equal('once', 'true')
                )->read();

                $traffic = [
                    'rx' => $trafficData[0]['rx-bits-per-second'] ?? 0,
                    'tx' => $trafficData[0]['tx-bits-per-second'] ?? 0,
                ];

                $result[] = [
                    'interface' => $name,
                    'running'   => $iface['running'] ?? 'false',
                    'type'      => $iface['type'] ?? null,
                    'traffic'   => $traffic,
                ];
            }

            return [
                'status'    => 'success',
                'message'   => 'Daftar semua interface berhasil diambil',
                'interfaces' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }



    public static function getPPPSecretByName(Client $client, $usersecret)
    {
        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $response = $client->query($query)->read();

            return $response ?: null;
        } catch (Exception $e) {
            Log::error('MikrotikServices::getPPPSecretByName error: ' . $e->getMessage());
            return null;
        }
    }

    public static function blokUser(Client $client, $usersecret, $id = null)
    {
        try {
            if ($id) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $id);
                $query->equal("disabled", "yes");
                $client->query($query)->read();
                return true;
            }

            $findQuery = new Query('/ppp/secret/print');
            $findQuery->where('name', $usersecret);
            $users = $client->query($findQuery)->read();
            if (empty($users)) return false;

            foreach ($users as $user) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $user['.id']);
                $query->equal("disabled", "yes");
                $client->query($query)->read();
            }

            return true;
        } catch (Exception $e) {
            Log::error('MikrotikServices::blokUser error: ' . $e->getMessage());
            return false;
        }
    }

    public static function activeConnections($router)
    {
        try {
            $client = MikrotikServices::connect($router);

            $query = new \RouterOS\Query('/ppp/active/print');
            $query->where('comment', 'Created by E-Nagih');

            $response = $client->query($query)->read();

            return $response;
        } catch (\Exception $e) {
            \Log::error('Gagal ambil koneksi aktif: ' . $e->getMessage());
            return [];
        }
    }



    public static function unblokUser(Client $client, $usersecret, $originalProfile , $id = null)
    {
        try {
            if ($id) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $id);
                $query->equal("disabled", "no");
                if ($originalProfile) {
                    $query->equal("profile", $originalProfile);
                }
                $client->query($query)->read();
                return true;
            }

            $findQuery = new Query('/ppp/secret/print');
            $findQuery->where('name', $usersecret);
            $users = $client->query($findQuery)->read();
            if (empty($users)) return false;

            foreach ($users as $user) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $user['.id']);
                $query->equal("disabled", "no");
                if ($originalProfile) {
                    $query->equal("profile", $originalProfile);
                }
                $client->query($query)->read();
            }

            return true;
        } catch (Exception $e) {
            Log::error('MikrotikServices::unblokUser error: ' . $e->getMessage());
            return false;
        }
    }


    public static function getUserTraffic(Client $client, $usersecret)
    {
        try {
            $activeQuery = new Query('/ppp/active/print');
            $activeQuery->where('name', $usersecret);
            $activeUsers = $client->query($activeQuery)->read();

            if (empty($activeUsers)) {
                return ['status' => 'inactive', 'message' => 'User is not currently active'];
            }

            $trafficData = [];
            foreach ($activeUsers as $activeUser) {
                $interface = $activeUser['interface'] ?? null;
                if (!$interface) continue;

                $trafficQuery = new Query('/interface/monitor-traffic');
                $trafficQuery->equal('interface', $interface);
                $trafficQuery->equal('once', '');
                $trafficStats = $client->query($trafficQuery)->read();

                $rx = $trafficStats[0]['rx-bits-per-second'] ?? 0;
                $tx = $trafficStats[0]['tx-bits-per-second'] ?? 0;

                $trafficData[] = [
                    'name' => $usersecret,
                    'status' => 'active',
                    'interface' => $interface,
                    'uptime' => $activeUser['uptime'] ?? 'Unknown',
                    'address' => $activeUser['address'] ?? 'Unknown',
                    'service' => $activeUser['service'] ?? 'Unknown',
                    'caller_id' => $activeUser['caller-id'] ?? 'Unknown',
                    'encoding' => $activeUser['encoding'] ?? 'Unknown',
                    'download_rate' => self::formatBitRate($rx),
                    'upload_rate' => self::formatBitRate($tx),
                    'download_rate_raw' => $rx,
                    'upload_rate_raw' => $tx
                ];
            }

            return $trafficData;
        } catch (Exception $e) {
            Log::error('MikrotikServices::getUserTraffic error: ' . $e->getMessage());
            return ['status' => 'error', 'message' => 'Failed to retrieve traffic information: ' . $e->getMessage()];
        }
    }

    public static function formatBitRate($bitsPerSecond)
    {
        if ($bitsPerSecond < 1000) {
            return round($bitsPerSecond, 2) . ' bps';
        } elseif ($bitsPerSecond < 1000000) {
            return round($bitsPerSecond / 1000, 2) . ' Kbps';
        } elseif ($bitsPerSecond < 1000000000) {
            return round($bitsPerSecond / 1000000, 2) . ' Mbps';
        } else {
            return round($bitsPerSecond / 1000000000, 2) . ' Gbps';
        }
    }

    public static function removeActiveConnections(Client $client, $usersecret)
    {
        try {
            $findQuery = new Query('/ppp/active/print');
            $findQuery->where('name', $usersecret);
            $activeConnections = $client->query($findQuery)->read();

            if (empty($activeConnections)) return false;

            foreach ($activeConnections as $connection) {
                $removeQuery = new Query('/ppp/active/remove');
                $removeQuery->equal('.id', $connection['.id']);
                $client->query($removeQuery)->read();
            }

            return true;
        } catch (Exception $e) {
            Log::error('MikrotikServices::removeActiveConnections error: ' . $e->getMessage());
            return false;
        }
    }

    public static function getRouterLoginLogs(Client $client, $limit = 20)
    {
        try {
            $query = new Query('/log/print');
            $query->where('topics', 'system,info');
            $logs = $client->query($query)->read();

            if (empty($logs)) {
                return collect([]);
            }

            $filteredLogs = collect($logs)
                ->filter(function ($log) {
                    return isset($log['topics'], $log['message']) &&
                        Str::contains($log['topics'], 'system,info') &&
                        (
                            Str::contains(Str::lower($log['message']), 'logged in') ||
                            Str::contains(Str::lower($log['message']), 'login failure') ||
                            Str::contains(Str::lower($log['message']), 'logged out')
                        );
                })
                ->sortByDesc(function ($log) {
                    return strtotime($log['time'] ?? now());
                })
                ->take($limit)
                ->values()
                ->map(function ($log) {
                    return [
                        'time' => $log['time'] ?? '-',
                        'message' => $log['message'] ?? '-',
                        'topics' => $log['topics'] ?? '-',
                    ];
                });

            return $filteredLogs;
        } catch (Exception $e) {
            \Log::error('MikrotikServices::getRouterLoginLogs error: ' . $e->getMessage());
            return collect([]);
        }
    }

    public static function getInterfaceTraffic(Client $client, $interface)
    {
        try {
            $query = new Query('/interface/monitor-traffic');
            $query->equal('interface', $interface);
            $query->equal('once', '');
            $stats = $client->query($query)->read();

            return [
                'rx' => (int) ($stats[0]['rx-bits-per-second'] ?? 0),
                'tx' => (int) ($stats[0]['tx-bits-per-second'] ?? 0),
            ];
        } catch (Exception $e) {
            \Log::error("Gagal ambil traffic: " . $e->getMessage());
            return ['rx' => 0, 'tx' => 0];
        }
    }

    public static function getCustomerWifiClients(Router $router, string $usersecret): array
    {
        try {
            $client = self::connect($router);

            // Get active PPPoE connection
            $active = $client->query(
                (new Query('/ppp/active/print'))->where('name', $usersecret)
            )->read();

            if (empty($active)) {
                return [
                    'status' => 'offline',
                    'message' => 'Customer not connected',
                    'customer_ip' => null,
                    'wifi_clients' => 0,
                    'method' => 'pppoe_check'
                ];
            }

            $customerIP = $active[0]['address'] ?? null;

            if (!$customerIP) {
                return [
                    'status' => 'error',
                    'message' => 'Customer IP not found',
                    'customer_ip' => null,
                    'wifi_clients' => 0,
                    'method' => 'pppoe_check'
                ];
            }

            // Try to get DHCP leases from customer's network
            $dhcpQuery = new Query('/ip/dhcp-server/lease/print');
            $leases = $client->query($dhcpQuery)->read();

            $wifiClients = 0;
            $customerNetwork = substr($customerIP, 0, strrpos($customerIP, '.'));

            foreach ($leases as $lease) {
                $leaseIP = $lease['address'] ?? '';
                if (strpos($leaseIP, $customerNetwork) === 0 && $leaseIP !== $customerIP) {
                    $wifiClients++;
                }
            }

            return [
                'status' => 'online',
                'message' => 'WiFi clients detected',
                'customer_ip' => $customerIP,
                'wifi_clients' => $wifiClients,
                'method' => 'dhcp_lease_scan'
            ];
        } catch (\Throwable $e) {
            Log::error('Error getting WiFi clients: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'customer_ip' => null,
                'wifi_clients' => 0,
                'method' => 'error'
            ];
        }
    }

    public static function getCustomerConnectionLogs(Client $client, string $usersecret, int $limit = 10): array
    {
        try {
            $query = new Query('/log/print');
            $logs = $client->query($query)->read();

            $customerLogs = collect($logs)
                ->filter(function ($log) use ($usersecret) {
                    $message = $log['message'] ?? '';
                    return stripos($message, $usersecret) !== false;
                })
                ->sortByDesc(function ($log) {
                    return $log['time'] ?? '';
                })
                ->take($limit)
                ->values()
                ->toArray();

            return $customerLogs;
        } catch (\Throwable $e) {
            Log::error('Error getting customer logs: ' . $e->getMessage());
            return [];
        }
    }

    public static function getCustomerNetworkInfo(Router $router, string $usersecret): array
    {
        try {
            $client = self::connect($router);

            // Get traffic data
            $trafficData = self::trafficPelanggan($router, $usersecret);

            // Get WiFi clients
            $wifiData = self::getCustomerWifiClients($router, $usersecret);

            // Get connection history/logs
            $logs = self::getCustomerConnectionLogs($client, $usersecret);

            return [
                'status' => 'success',
                'traffic' => $trafficData,
                'wifi_clients' => $wifiData,
                'connection_logs' => $logs,
                'timestamp' => now()->toISOString()
            ];
        } catch (\Throwable $e) {
            Log::error('Error getting network info: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage(),
                'traffic' => null,
                'wifi_clients' => null,
                'connection_logs' => null,
                'timestamp' => now()->toISOString()
            ];
        }
    }

}