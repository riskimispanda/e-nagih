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
                    'timeout' => 5,
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

            return true;
        } catch (Exception $e) {
            Log::error('MikrotikServices::changeUserProfile error: ' . $e->getMessage());
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
        $query->equal('comment','Created by E-Nagih');

        try {
            $client->query($query)->read();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public static function getPPPSecret(Client $client)
    {
        try {
            $query = new Query('/ppp/secret/print');
            // $query->where('name', 'profile-UpTo-5');
            return $client->query($query)->read();
        } catch (\Exception $e) {
            Log::error('Gagal mengambil PPP Secret: ' . $e->getMessage());
            return null;
        }
    }

    public static function trafficPelanggan(Router $router, $usersecret)
    {
        try {
            $client = MikrotikServices::connect($router);

            $active = $client->query(
                (new Query('/ppp/active/print'))->where('name', $usersecret)
            )->read();

            if (count($active) === 0) {
                return response()->json([
                    'message' => 'PPP tidak ditemukan untuk user: ' . $usersecret,
                    'rx' => 0,
                    'tx' => 0,
                ]);
            }

            $interfaceNamePart = strtolower($usersecret);

            // Ambil semua interface
            $interfaces = $client->query(
                new Query('/interface/print')
            )->read();

            \Log::info('🔍 Interface List:', $interfaces);

            // Cari interface dengan nama yang mengandung nama PPP user
            $interface = collect($interfaces)->first(function ($iface) use ($interfaceNamePart) {
                return isset($iface['name']) && str_contains(strtolower($iface['name']), strtolower($interfaceNamePart));
            });

            if (!$interface) {
                return response()->json([
                    'message' => 'Interface tidak ditemukan untuk user: ' . $usersecret,
                    'rx' => 0,
                    'tx' => 0,
                ]);
            }

            return response()->json([
                'message' => 'Berhasil mendapatkan trafik',
                'rx' => (int) $interface['rx-byte'],
                'tx' => (int) $interface['tx-byte'],
            ]);
        } catch (\Exception $e) {
            \Log::error('Gagal ambil trafik pelanggan: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal ambil trafik pelanggan',
                'rx' => 0,
                'tx' => 0,
            ]);
        }
    }



    public static function getInterfacePelanggan(Client $client, $usersecret)
    {
        try {
            $active = $client->query(
                (new Query('/ppp/active/print'))->where('name', $usersecret)
            )->read();

            if (empty($active)) {
                return [
                    'status' => 'offline',
                    'message' => 'User tidak aktif',
                ];
            }

            // Coba ambil langsung
            $interface = $active[0]['interface'] ?? null;

            // Jika kosong, coba cocokkan berdasarkan nama PPPoE
            if (!$interface) {
                $interfaces = $client->query(
                    (new Query('/interface/print'))->where('running', 'true')
                )->read();

                foreach ($interfaces as $iface) {
                    if (
                        isset($iface['name']) &&
                        str_contains($iface['name'], str_replace('@', '-', explode('@', $usersecret)[0]))
                    ) {
                        $interface = $iface['name'];
                        break;
                    }
                }
            }

            return [
                'status' => 'online',
                'interface' => $interface ?? null,
            ];

        } catch (\Exception $e) {
            return [
                'status' => 'error',
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



}