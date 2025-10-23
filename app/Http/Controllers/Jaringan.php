<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use App\Models\ODP;
use App\Models\ODC;
use App\Models\Server;
use App\Models\Customer;

class Jaringan extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = Lokasi::withCount('odc')->with('odc.odp', 'server');

        if ($search) {
            $query->where('nama_lokasi', 'like', "%{$search}%")
                ->orWhereHas('server', function ($q) use ($search) {
                    $q->where('lokasi_server', 'like', "%{$search}%");
                });
        }

        $lokasi = $query->paginate($perPage)->appends($request->query());

        $lokasi->getCollection()->transform(function ($olt) {
            $olt->odp_count = $olt->odc->sum(fn($odc) => $odc->odp->count());
            return $olt;
        });
        return view('/NOC/data-olt', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'lokasi' => $lokasi,
            'server' => Server::all(),
        ]);
    }

    public function server(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = Server::query();

        if ($search) {
            $query->where('lokasi_server', 'like', "%{$search}%")
                ->orWhere('ip_address', 'like', "%{$search}%");
        }

        $servers = $query->paginate($perPage)->appends($request->query());

        return view('/NOC/data-server', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'server' => $servers,
        ]);
    }

    public function addServer(Request $request)
    {
        // dd($request->all());
        $server = new Server();
        $server->lokasi_server = $request->lokasi_server;
        $server->ip_address = $request->ip;
        $server->gps = $request->gps;
        $server->save();

        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    public function mindmap()
    {
        return view('/NOC/mind-mapping', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'lokasi' => Lokasi::all(),
            'odc' => ODC::with('lokasi')->get(),
            'odp' => ODP::with('odc')->get(),
            'mind_map' => [
                'name' => 'Network Mapping',
                'children' => Server::with(['lokasi.odc.odp.customer'])->get()->map(function($server) {
                    return [
                        'name' => $server->lokasi_server,
                        'ip_address' => $server->ip_address,
                        'children' => $server->lokasi->isEmpty() ? [['name' => 'Kosong']] : $server->lokasi->map(function($lokasi) {
                            return [
                                'name' => $lokasi->nama_lokasi,
                                'children' => $lokasi->odc->isEmpty() ? [['name' => 'Kosong']] : $lokasi->odc->map(function($odc) {
                                    return [
                                        'name' => $odc->nama_odc,
                                        'children' => $odc->odp->isEmpty() ? [['name' => 'Kosong']] : $odc->odp->map(function($odp) {
                                            return [
                                                'name' => $odp->nama_odp,
                                                'children' => $odp->customer->isEmpty() ? [['name' => 'Kosong']] : $odp->customer->map(function($customer) {
                                                    return [
                                                        'name' => $customer->nama_customer,
                                                        'alamat' => $customer->alamat,
                                                        'mac_address' => $customer->mac_address,
                                                    ];
                                                })->toArray()
                                            ];
                                        })->toArray()
                                    ];
                                })->toArray()
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ]
        ]);
    }

    public function odc(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = ODC::withCount('odp')->with('olt');

        if ($search) {
            $query->where('nama_odc', 'like', "%{$search}%")
                ->orWhereHas('olt', function ($q) use ($search) {
                    $q->where('nama_lokasi', 'like', "%{$search}%");
                });
        }

        $odcs = $query->paginate($perPage)->appends($request->query());

        return view('/NOC/data-odc', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'lokasi' => Lokasi::all(),
            'odc' => $odcs,
        ]);
    }

    public function odp(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = ODP::withCount('customer')->with('odc');

        if ($search) {
            $query->where('nama_odp', 'like', "%{$search}%")
                ->orWhereHas('odc', function ($q) use ($search) {
                    $q->where('nama_odc', 'like', "%{$search}%");
                });
        }

        $odps = $query->paginate($perPage)->appends($request->query());
        return view('/NOC/data-odp', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'odp' => $odps,
            'lokasi' => ODC::all(),
        ]);
    }

    public function addOlt(Request $request)
    {
        // dd($request->all());
        $lokasi = new Lokasi();
        $lokasi->nama_lokasi = $request->olt;
        $lokasi->id_server = $request->lokasi_server;
        $lokasi->gps = $request->gps;
        $lokasi->save();

        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    public function addOdc(Request $request)
    {
        // dd($request->all());
        $odc = new ODC();
        $odc->nama_odc = $request->nama_odc;
        $odc->lokasi_id = $request->olt;
        $odc->gps = $request->gps;
        $odc->save();

        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    public function addOdp(Request $request)
    {
        // dd($request->all());
        $odp = new ODP();
        $odp->nama_odp = $request->nama_odp;
        $odp->odc_id = $request->odc;
        $odp->gps = $request->gps;
        $odp->save();

        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
