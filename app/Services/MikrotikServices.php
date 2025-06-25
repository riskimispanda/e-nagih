<?php
namespace App\Services;

use RouterOS\Client;
use RouterOS\Query;
use RouterOS\Exception;

class MikrotikServices
{
    protected $client;
    
    public function __construct()
    {
        $this->client = new Client([
            'host' => env('MIKROTIK_HOST'),
            'user' => env('MIKROTIK_USER'),
            'pass' => env('MIKROTIK_PASS'),
            'port' => (int)env('MIKROTIK_PORT', 5000)
        ]);
    }

    public function getProfile()
    {
        $query = new Query('/system/identity/print');
        $response = $this->client->query($query)->read();
        
        $services = [
            'router_name' => $response[0]['name'] ?? 'Unknown'
        ];
        return $services;
    }

    public function getProfiles()
    {
        $query = new Query('/ppp/profile/print');
        $response = $this->client->query($query)->read();
        
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

    public function getUserProfiles()
    {
        $query = new Query('/ppp/secret/print');
        $response = $this->client->query($query)->read();
        return $response;
    }

    public function getRouterDetailsByName($routerName)
    {
        $query = new Query('/system/identity/print');
        $response = $this->client->query($query)->read();
        
        $routerDetails = [];
        foreach ($response as $router) {
            if ($router['name'] == $routerName) {
                $routerDetails = [
                    'name' => $router['name'] ?? 'Unknown',
                ];
            }
        }
        
        return $routerDetails;
    }

    public function addPPPSecret($data)
    {
        // dd($data);
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
            $this->client->query($query)->read();
            return true;
        } catch (Exception $e) {
            // Handle the exception (e.g., log it)
            return false;
        }
    }

    /**
     * Get PPP secrets with a specific profile
     * 
     * @param string $profile The profile to filter by (default: 'profile-test-aplikasi')
     * @return array The list of PPP secrets
     */
    public function getPPPSecret()
    {
        try {
            $query = new \RouterOS\Query('/ppp/secret/print');
            $query->where('comment', 'Created by E-Nagih');
            $response = $this->client->query($query)->read();

            return $response;
        } catch (\Exception $e) {
            \Log::error('Gagal mengambil PPP Secret: ' . $e->getMessage());
            return null;
        }
    }
    
    public function userProfile($usersecret)
    {
        $query = new Query('/ppp/secret/print');
        $query->where('name', $usersecret);
        $response = $this->client->query($query)->read();
        
        return $response;
    }
    /**
     * Get PPP secret by username/secret name
     * 
     * @param string $usersecret The username/secret to search for
     * @return array|null The PPP secret details or null if not found
     */
    public function getPPPSecretByName($usersecret)
    {
        try {
            $query = new Query('/ppp/secret/print');
            $query->where('name', $usersecret);
            $response = $this->client->query($query)->read();
            
            if (empty($response)) {
                return null;
            }
            
            return $response;
        } catch (Exception $e) {
            \Log::error('MikrotikServices::getPPPSecretByName error: ' . $e->getMessage());
            return null;
        }
    }

    public function getActiveConnections()
    {
        try {
            $query = new Query('/ppp/active/print');
            $connections = $this->client->query($query)->read();

            return $connections ?? [];
        } catch (Exception $e) {
            Log::error('Gagal mengambil koneksi aktif Mikrotik: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Block a PPP user by setting disabled=yes
     * 
     * @param string $usersecret The username/secret of the PPP user
     * @param string|null $id The ID of the specific user (optional)
     * @return bool|array Returns true on success, array of blocked users if multiple, or false on failure
     */
    public function blokUser($usersecret, $id = null)
    {
        try {
            if ($id) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $id);
                $query->equal("disabled", "yes");
                $this->client->query($query)->read();
                return true;
            }
            $findQuery = new Query('/ppp/secret/print');
            $findQuery->where('name', $usersecret);
            $users = $this->client->query($findQuery)->read();
            if (empty($users)) {
                return false;
            }
            
            if (count($users) === 1) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $users[0]['.id']);
                $query->equal("disabled", "yes");
                $this->client->query($query)->read();
                return true;
            }
            $blockedUsers = [];
            foreach ($users as $user) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $user['.id']);
                $query->equal("disabled", "yes");
                $this->client->query($query)->read();
                $blockedUsers[] = [
                    'id' => $user['.id'],
                    'name' => $user['name']
                ];
            }
            
            return $blockedUsers;
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('MikrotikServices::blokUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Change the profile of a PPP user
     * 
     * @param string $usersecret The username/secret of the PPP user
     * @param string $profile The new profile to assign
     * @param string|null $id The ID of the specific user (optional)
     * @return bool|array Returns true on success, array of changed users if multiple, or false on failure
     */
    public function changeUserProfile($usersecret, $profile, $id = null)
    {
        try {
            // If ID is provided, use it to target the specific user
            if ($id) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $id);
                $query->equal("profile", $profile);
                $this->client->query($query)->read();
                return true;
            }
            
            // If no ID, first find all matching users with the given usersecret
            $findQuery = new Query('/ppp/secret/print');
            $findQuery->where('name', $usersecret);
            $users = $this->client->query($findQuery)->read();
            
            // If no users found, return false
            if (empty($users)) {
                return false;
            }
            
            // If only one user found, update directly
            if (count($users) === 1) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $users[0]['.id']);
                $query->equal("profile", $profile);
                $this->client->query($query)->read();
                return true;
            }
            
            // If multiple users found with the same usersecret, update all of them
            $updatedUsers = [];
            foreach ($users as $user) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $user['.id']);
                $query->equal("profile", $profile);
                $this->client->query($query)->read();
                $updatedUsers[] = [
                    'id' => $user['.id'],
                    'name' => $user['name'],
                    'profile' => $profile
                ];
            }
            
            return $updatedUsers;
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('MikrotikServices::changeUserProfile error: ' . $e->getMessage());
            return false;
        }
    }
            

    /**
     * Unblock a PPP user by setting disabled=no
     * 
     * @param string $usersecret The username/secret of the PPP user
     * @param string|null $id The ID of the specific user (optional)
     * @return bool|array Returns true on success, array of unblocked users if multiple, or false on failure
     */
    public function unblokUser($usersecret, $id = null)
    {
        try {
            // If ID is provided, use it to target the specific user
            if ($id) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $id);
                $query->equal("disabled", "no");
                $this->client->query($query)->read();
                return true;
            }
            
            // If no ID, first find all matching users with the given usersecret
            $findQuery = new Query('/ppp/secret/print');
            $findQuery->where('name', $usersecret);
            $users = $this->client->query($findQuery)->read();
            
            // If no users found, return false
            if (empty($users)) {
                return false;
            }
            
            // If only one user found, update directly
            if (count($users) === 1) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $users[0]['.id']);
                $query->equal("disabled", "no");
                $this->client->query($query)->read();
                return true;
            }
            
            // If multiple users found with the same usersecret, unblock all of them
            $unblockedUsers = [];
            foreach ($users as $user) {
                $query = new Query("/ppp/secret/set");
                $query->equal(".id", $user['.id']);
                $query->equal("disabled", "no");
                $this->client->query($query)->read();
                $unblockedUsers[] = [
                    'id' => $user['.id'],
                    'name' => $user['name']
                ];
            }
            
            return $unblockedUsers;
        } catch (Exception $e) {
            // Log the error for debugging
            \Log::error('MikrotikServices::unblokUser error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get traffic information for a specific PPP user
     * 
     * @param string $usersecret The username/secret of the PPP user
     * @return array|null Returns traffic information or null if user not found/not active
     */
    public function getUserTraffic($usersecret)
    {
        try {
            // First, check if the user is active
            $activeQuery = new Query('/ppp/active/print');
            $activeQuery->where('name', $usersecret);
            $activeUsers = $this->client->query($activeQuery)->read();
            
            if (empty($activeUsers)) {
                return [
                    'status' => 'inactive',
                    'message' => 'User is not currently active'
                ];
            }
            
            $trafficData = [];
            
            foreach ($activeUsers as $activeUser) {
                // Get the interface name for this active connection
                $interface = $activeUser['interface'] ?? null;
                
                if (!$interface) {
                    continue;
                }
                
                // Get traffic statistics for this interface
                $trafficQuery = new Query('/interface/monitor-traffic');
                $trafficQuery->equal('interface', $interface);
                $trafficQuery->equal('once', '');
                $trafficStats = $this->client->query($trafficQuery)->read();
                
                // Get additional connection details
                $uptime = $activeUser['uptime'] ?? 'Unknown';
                $address = $activeUser['address'] ?? 'Unknown';
                $service = $activeUser['service'] ?? 'Unknown';
                $caller_id = $activeUser['caller-id'] ?? 'Unknown';
                $encoding = $activeUser['encoding'] ?? 'Unknown';
                
                // Calculate traffic rates
                $rxBytesPerSecond = $trafficStats[0]['rx-bits-per-second'] ?? 0;
                $txBytesPerSecond = $trafficStats[0]['tx-bits-per-second'] ?? 0;
                
                // Convert to more readable format (Kbps, Mbps)
                $rxRate = $this->formatBitRate($rxBytesPerSecond);
                $txRate = $this->formatBitRate($txBytesPerSecond);
                
                $trafficData[] = [
                    'name' => $usersecret,
                    'status' => 'active',
                    'interface' => $interface,
                    'uptime' => $uptime,
                    'address' => $address,
                    'service' => $service,
                    'caller_id' => $caller_id,
                    'encoding' => $encoding,
                    'download_rate' => $rxRate,
                    'upload_rate' => $txRate,
                    'download_rate_raw' => $rxBytesPerSecond,
                    'upload_rate_raw' => $txBytesPerSecond
                ];
            }
            
            return $trafficData;
            
        } catch (Exception $e) {
            \Log::error('MikrotikServices::getUserTraffic error: ' . $e->getMessage());
            return [
                'status' => 'error',
                'message' => 'Failed to retrieve traffic information: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Format bit rate to human-readable format
     * 
     * @param int $bitsPerSecond Bits per second
     * @return string Formatted bit rate (bps, Kbps, Mbps, Gbps)
     */
    public function formatBitRate($bitsPerSecond)
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

    public function removeActiveConnections($usersecret)
    {
        try {
            // Find active connections for the user
            $findQuery = new Query('/ppp/active/print');
            $findQuery->where('name', $usersecret);
            $activeConnections = $this->client->query($findQuery)->read();
            
            if (empty($activeConnections)) {
                return false;
            }

            // Remove each active connection found
            foreach ($activeConnections as $connection) {
                $removeQuery = new Query('/ppp/active/remove');
                $removeQuery->equal('.id', $connection['.id']);
                $this->client->query($removeQuery)->read();
            }
            
            return true;
        } catch (Exception $e) {
            \Log::error('MikrotikServices::removeActiveConnections error: ' . $e->getMessage());
            return false;
        }
    }

}