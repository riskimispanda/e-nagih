<?php 

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\Lokasi;
use App\Models\ODC;
use App\Models\ODP;
use App\Models\Customer;

class MapController extends Controller
{
    public function index()
    {
        return view('map.map', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles
        ]);
    }

    private function parseGps($gps)
    {
        if (!$gps) return ['lat' => null, 'lng' => null];

        // Format: "-8.044889109411237, 110.4827779828878"
        if (preg_match('/^-?\d+\.\d+,\s*-?\d+\.\d+$/', $gps)) {
            [$lat, $lng] = explode(',', $gps);
            return ['lat' => trim($lat), 'lng' => trim($lng)];
        }

        // Format: "...?q=-8.04488,110.48277"
        if (preg_match('/q=(-?\d+\.\d+),(-?\d+\.\d+)/', $gps, $matches)) {
            return ['lat' => $matches[1], 'lng' => $matches[2]];
        }

        // Format DMS: 7°58'42.7"S 110°24'31.8"E
        if (preg_match('/(\d+)°(\d+)\'([\d.]+)"([NS]),\s*(\d+)°(\d+)\'([\d.]+)"([EW])/', $gps, $m)) {
            $lat = $m[1] + $m[2] / 60 + $m[3] / 3600;
            if ($m[4] === 'S') $lat *= -1;

            $lng = $m[5] + $m[6] / 60 + $m[7] / 3600;
            if ($m[8] === 'W') $lng *= -1;

            return ['lat' => $lat, 'lng' => $lng];
        }

        // Format tidak dikenali
        return ['lat' => null, 'lng' => null];
    }


    public function data()
    {
        $server = Server::all()->map(function ($item) {
            $coord = $this->parseGps($item->gps);
            return [
                'id' => $item->id,
                'nama' => $item->lokasi_server,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'server'
            ];
        });

        $olt = Lokasi::all()->map(function ($item) {
            $coord = $this->parseGps($item->gps);
            return [
                'id' => $item->id,
                'nama' => $item->nama_lokasi,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'olt',
                'server_id' => $item->id_server
            ];
        });

        $odc = ODC::all()->map(function ($item) {
            $coord = $this->parseGps($item->gps);
            return [
                'id' => $item->id,
                'nama' => $item->nama_odc,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'odc',
                'olt_id' => $item->lokasi_id
            ];
        });

        $odp = ODP::all()->map(function ($item) {
            $coord = $this->parseGps($item->gps);
            return [
                'id' => $item->id,
                'nama' => $item->nama_odp,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'odp',
                'odc_id' => $item->odc_id
            ];
        });

        $customer = Customer::all()->map(function ($item) {
            $coord = $this->parseGps($item->gps);
            return [
                'id' => $item->id,
                'nama' => $item->nama_customer,
                'lat' => $coord['lat'],
                'lng' => $coord['lng'],
                'jenis' => 'customer',
                'odp_id' => $item->lokasi_id
            ];
        });

        return response()->json(
            $server
                ->merge($olt)
                ->merge($odc)
                ->merge($odp)
                ->merge($customer)
                ->filter(fn($item) => $item['lat'] && $item['lng']) // Hanya koordinat valid
                ->values()
        );
    }
}
