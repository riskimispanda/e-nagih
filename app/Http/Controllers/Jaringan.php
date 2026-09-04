<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lokasi;
use App\Models\ODP;
use App\Models\ODC;
use App\Models\Server;
use App\Models\Customer;
use App\Models\ModemDetail;
use App\Helpers\LogistikStatus;
use App\Models\Perangkat;

class Jaringan extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = Lokasi::withCount('odc')->with('odc.odp', 'server', 'modemDetail.perangkat');

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
            'perangkatOlt' => $this->getPerangkatByKategori('olt'),
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

    public function getInfrastrukturFormOptions()
    {
        $serverList = Server::orderBy('lokasi_server')->get();
        $oltList = Lokasi::orderBy('nama_lokasi')->get();
        $odcList = ODC::with(['splitters.perangkat', 'olt'])->orderBy('nama_odc')->get();
        $odcList->transform(function ($odc) {
            $odc->splitters_data = $odc->splitters->map(function ($s) {
                return [
                    'id' => $s->id,
                    'nama' => $s->perangkat->nama_perangkat ?? 'Splitter',
                    'rasio' => $s->rasio ?? '-',
                    'sn' => $s->no_sn ?? $s->serial_number ?? '',
                ];
            })->values()->toArray();
            return $odc;
        });

        $perangkatSplitter = $this->getPerangkatSplitter();

        $splitterList = ModemDetail::with('perangkat')
            ->whereHas('perangkat.kategori', function ($q) {
                $q->where('nama_logistik', 'splitter');
            })
            ->where('status_id', LogistikStatus::TERSEIDA)
            ->whereNull('odc_id')
            ->whereNull('odp_id')
            ->whereNull('parent_splitter_id')
            ->get();

        $ponSplitters = ModemDetail::with('perangkat')
            ->whereNotNull('lokasi_id')
            ->whereNull('odc_id')
            ->whereNull('odp_id')
            ->whereNull('parent_splitter_id')
            ->get()
            ->map(function ($sp) {
                return [
                    'id' => $sp->id,
                    'lokasi_id' => $sp->lokasi_id,
                    'pon_port' => $sp->pon_port,
                    'rasio' => $sp->rasio ?? '1:4',
                    'nama' => $sp->perangkat->nama_perangkat ?? 'Splitter',
                ];
            })
            ->values();

        $activeSplitters = ModemDetail::with(['perangkat', 'olt', 'odc', 'odp'])
            ->where('status_id', LogistikStatus::TERPAKAI)
            ->whereNotNull('rasio')
            ->get()
            ->map(function ($s) {
                $loc = '';
                if ($s->lokasi_id && $s->pon_port) {
                    $loc = ($s->olt->nama_lokasi ?? 'OLT') . ' (' . $s->pon_port . ')';
                } elseif ($s->odc_id) {
                    $loc = 'ODC: ' . ($s->odc->nama_odc ?? '');
                } elseif ($s->odp_id) {
                    $loc = 'ODP: ' . ($s->odp->nama_odp ?? '');
                }
                return [
                    'id' => $s->id,
                    'nama' => ($s->perangkat->nama_perangkat ?? 'Splitter') . ' (' . ($s->rasio ?? '1:4') . ')' . ($loc ? ' [' . $loc . ']' : ''),
                    'rasio' => $s->rasio ?? '1:4',
                    'parent_splitter_id' => $s->parent_splitter_id,
                    'parent_port_out' => $s->parent_port_out,
                    'lokasi_id' => $s->lokasi_id,
                    'pon_port' => $s->pon_port,
                    'odc_id' => $s->odc_id,
                    'odp_id' => $s->odp_id,
                ];
            });

        return [
            'serverList' => $serverList,
            'oltList' => $oltList,
            'odcList' => $odcList,
            'splitterList' => $splitterList,
            'perangkatSplitter' => $perangkatSplitter,
            'perangkatOlt' => $this->getPerangkatByKategori('olt'),
            'perangkatOdc' => $this->getPerangkatByKategori('odc'),
            'perangkatOdp' => $this->getPerangkatByKategori('odp'),
            'ponSplitters' => $ponSplitters,
            'activeSplitters' => $activeSplitters,
        ];
    }

    public function infrastruktur(Request $request)
    {
        $servers = Server::with(['lokasi.odc.splitters', 'lokasi.odc.odp'])->get();
        $options = $this->getInfrastrukturFormOptions();

        return view('/NOC/data-infrastruktur', array_merge([
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'servers' => $servers,
        ], $options));
    }

    public function infrastrukturFormOptions(Request $request)
    {
        return response()->json($this->getInfrastrukturFormOptions());
    }

    public function infrastrukturJson(Request $request)
    {
        $servers = Server::with([
            'lokasi.modemDetail.perangkat',
            'lokasi.odc.modemDetail.perangkat',
            'lokasi.odc.splitters.perangkat',
            'lokasi.odc.odp.modemDetail.perangkat',
            'lokasi.odc.odp.splitters.perangkat',
            'lokasi.odc.odp.customer',
        ])->get();

        $allSplitters = ModemDetail::with('perangkat')
            ->where('status_id', LogistikStatus::TERPAKAI)
            ->whereNotNull('rasio')
            ->get();

        $allChildSplittersByParent = $allSplitters->whereNotNull('parent_splitter_id')->groupBy('parent_splitter_id');
        $rootPonSplittersGrouped = $allSplitters
            ->whereNotNull('lokasi_id')
            ->whereNull('odc_id')
            ->whereNull('odp_id')
            ->whereNull('parent_splitter_id')
            ->groupBy('lokasi_id');

        $allOdcs = ODC::with(['modemDetail.perangkat', 'splitters.perangkat', 'odp.modemDetail.perangkat', 'odp.splitters.perangkat', 'odp.customer'])->get();
        $allOdcsBySplitter = $allOdcs->whereNotNull('splitter_id')->groupBy('splitter_id');

        $allOdps = ODP::with(['modemDetail.perangkat', 'splitters.perangkat', 'customer'])->get();
        $allOdpsBySplitter = $allOdps->whereNotNull('splitter_id')->groupBy('splitter_id');

        return response()->json(
            $servers->map(function ($server) use (
                $rootPonSplittersGrouped,
                $allChildSplittersByParent,
                $allOdcsBySplitter,
                $allOdpsBySplitter
            ) {
                return [
                    'id' => 'server-' . $server->id,
                    'db_id' => $server->id,
                    'name' => $server->lokasi_server,
                    'type' => 'Server',
                    'ip' => $server->ip_address,
                    'gps' => $server->gps,
                    'children' => $server->lokasi->map(function ($olt) use (
                        $rootPonSplittersGrouped,
                        $allChildSplittersByParent,
                        $allOdcsBySplitter,
                        $allOdpsBySplitter
                    ) {
                        $buildOdcNode = null;
                        $buildSplitterNode = null;
                        $mapOdp = null;

                        $buildSplitterNode = function ($splitter) use (
                            &$buildSplitterNode,
                            &$buildOdcNode,
                            &$mapOdp,
                            $allChildSplittersByParent,
                            $allOdcsBySplitter,
                            $allOdpsBySplitter
                        ) {
                            $rasioStr = $splitter->rasio ?? '1:4';
                            $parts = preg_split('/[:\/]/', trim($rasioStr));
                            $ratioNum = count($parts) > 1 ? (int) end($parts) : (int) $parts[0];
                            if (!$ratioNum) $ratioNum = 4;

                            $childSplitters = $allChildSplittersByParent->get($splitter->id, collect());
                            $attachedOdcs = $allOdcsBySplitter->get($splitter->id, collect());
                            $attachedOdps = $allOdpsBySplitter->get($splitter->id, collect());

                            $childrenList = [];

                            // 1. Recursive child splitters (arbitrary depth cascading)
                            foreach ($childSplitters as $childSp) {
                                $childNode = $buildSplitterNode($childSp);
                                $childNode['port_out'] = $childSp->parent_port_out;
                                $childrenList[] = $childNode;
                            }

                            // 2. Attached ODCs (if any)
                            foreach ($attachedOdcs as $odc) {
                                $odcNode = $buildOdcNode($odc);
                                $odcNode['port_out'] = $odc->port_out;
                                $childrenList[] = $odcNode;
                            }

                            // 3. Attached ODPs
                            foreach ($attachedOdps as $odp) {
                                $odpNode = $mapOdp($odp);
                                $odpNode['port_out'] = $odp->port_out;
                                $childrenList[] = $odpNode;
                            }

                            $childrenList = collect($childrenList)->sortBy(fn($c) => $c['port_out'] ?? '')->values()->toArray();

                            return [
                                'id' => 'splitter-' . $splitter->id,
                                'db_id' => $splitter->id,
                                'name' => ($splitter->perangkat->nama_perangkat ?? 'Splitter') . ' (' . ($splitter->rasio ?? ('1:' . $ratioNum)) . ')',
                                'type' => 'Splitter',
                                'rasio' => $splitter->rasio ?? ('1:' . $ratioNum),
                                'ratio' => $ratioNum,
                                'serial' => $splitter->no_sn ?? $splitter->serial_number ?? '',
                                'serial_number' => $splitter->no_sn ?? $splitter->serial_number ?? '',
                                'perangkat_id' => $splitter->logistik_id,
                                'modem_detail_id' => $splitter->id,
                                'pon_port' => $splitter->pon_port,
                                'lokasi_id' => $splitter->lokasi_id,
                                'odc_id' => $splitter->odc_id,
                                'odp_id' => $splitter->odp_id,
                                'parent_splitter_id' => $splitter->parent_splitter_id,
                                'parent_port_out' => $splitter->parent_port_out,
                                'port_out' => $splitter->parent_port_out,
                                'children' => $childrenList,
                            ];
                        };

                        $mapOdp = function ($odp) use (&$buildSplitterNode) {
                            $odpSplitters = $odp->splitters->map(function ($s) use (&$buildSplitterNode) {
                                return $buildSplitterNode($s);
                            })->values()->toArray();

                            return [
                                'id' => 'odp-' . $odp->id,
                                'db_id' => $odp->id,
                                'name' => $odp->nama_odp,
                                'port_out' => $odp->port_out,
                                'type' => 'ODP',
                                'gps' => $odp->gps,
                                'panjang_kabel' => $odp->panjang_kabel,
                                'redaman' => $odp->redaman,
                                'modem_detail_id' => $odp->modem_detail_id,
                                'serial_number' => $odp->modemDetail->serial_number ?? '',
                                'perangkat_id' => $odp->modemDetail->logistik_id ?? null,
                                'perangkat_nama' => $odp->modemDetail->perangkat->nama_perangkat ?? '',
                                'odc_id' => $odp->odc_id,
                                'splitter_id' => $odp->splitter_id,
                                'splitters' => $odpSplitters,
                                'children' => [],
                            ];
                        };

                        $buildOdcNode = function ($odc) use (&$buildSplitterNode, &$mapOdp) {
                            $topLevelSplitters = $odc->splitters->whereNull('parent_splitter_id');
                            $odpDirect = $odc->odp->whereNull('splitter_id');

                            return [
                                'id' => 'odc-' . $odc->id,
                                'db_id' => $odc->id,
                                'name' => $odc->nama_odc,
                                'pon_port' => $odc->pon_port,
                                'type' => 'ODC',
                                'gps' => $odc->gps,
                                'panjang_kabel' => $odc->panjang_kabel,
                                'redaman' => $odc->redaman,
                                'modem_detail_id' => $odc->modem_detail_id,
                                'serial_number' => $odc->modemDetail->serial_number ?? '',
                                'perangkat_id' => $odc->modemDetail->logistik_id ?? null,
                                'perangkat_nama' => $odc->modemDetail->perangkat->nama_perangkat ?? '',
                                'olt_id' => $odc->lokasi_id,
                                'splitter_id' => $odc->splitter_id,
                                'port_out' => $odc->port_out,
                                'splitters' => $topLevelSplitters->map(function ($s) use ($buildSplitterNode) {
                                    return $buildSplitterNode($s);
                                })->values()->toArray(),
                                'children' => $odpDirect->map($mapOdp)->values()->toArray(),
                            ];
                        };

                        $directOdcs = $olt->odc->whereNull('splitter_id')->sortBy('pon_port');
                        $ponSplitters = $rootPonSplittersGrouped->get($olt->id, collect());

                        $childrenList = [];

                        // 1. Root splitter per PON port (dapat menampung ODC, ODP, atau Splitter bertingkat)
                        foreach ($ponSplitters as $sp) {
                            $spNode = $buildSplitterNode($sp);
                            $spNode['olt_pon_count'] = (int) ($olt->jumlah_pon ?: 8);
                            $childrenList[] = $spNode;
                        }

                        // 2. ODC langsung di PON port (tanpa splitter level PON)
                        foreach ($directOdcs as $odc) {
                            $childrenList[] = $buildOdcNode($odc);
                        }

                        $childrenList = collect($childrenList)->sortBy(fn($n) => $n['pon_port'] ?? '')->values()->toArray();

                        return [
                            'id' => 'olt-' . $olt->id,
                            'db_id' => $olt->id,
                            'name' => $olt->nama_lokasi,
                            'jumlah_pon' => (int) ($olt->jumlah_pon ?: 8),
                            'type' => 'OLT',
                            'gps' => $olt->gps,
                            'server_id' => $olt->id_server,
                            'modem_detail_id' => $olt->modem_detail_id,
                            'serial_number' => $olt->modemDetail->serial_number ?? '',
                            'perangkat_id' => $olt->modemDetail->logistik_id ?? null,
                            'perangkat_nama' => $olt->modemDetail->perangkat->nama_perangkat ?? '',
                            'children' => $childrenList,
                        ];
                    })->values()->toArray(),
                ];
            })->values()->toArray()
        );
    }

    public function addServer(Request $request)
    {
        $request->validate([
            'lokasi_server' => 'required|string',
            'gps' => 'required|string',
        ]);
        $server = new Server();
        $server->lokasi_server = $request->lokasi_server;
        $server->ip_address = $request->ip;
        $server->gps = $request->gps;
        $server->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Server Berhasil Ditambahkan', 'data' => $server]);
        }
        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    public function updateServer(Request $request, $id)
    {
        $request->validate([
            'lokasi_server' => 'required|string',
            'gps' => 'required|string',
        ]);
        $server = Server::findOrFail($id);
        $server->lokasi_server = $request->lokasi_server;
        $server->ip_address = $request->ip;
        $server->gps = $request->gps;
        $server->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Server berhasil diperbarui', 'data' => $server]);
        }
        return redirect()->back()->with('success', 'Server berhasil diperbarui');
    }

    public function deleteServer($id)
    {
        $server = Server::findOrFail($id);
        if ($server->lokasi()->count() > 0) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak dapat menghapus Server yang masih memiliki OLT!'], 422);
            }
            return redirect()->back()->with('error', 'Tidak dapat menghapus Server yang masih memiliki OLT!');
        }
        $server->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Server berhasil dihapus']);
        }
        return redirect()->back()->with('success', 'Server berhasil dihapus');
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

    private function getPerangkatSplitter()
    {
        $query = Perangkat::with(['kategori', 'modem' => function ($q) {
            $q->where('status_id', LogistikStatus::TERSEIDA)
              ->select('id', 'logistik_id', 'serial_number', 'mac_address');
        }])
        ->where(function ($q) {
            $q->whereHas('kategori', function ($sub) {
                $sub->whereRaw('LOWER(nama_logistik) LIKE ?', ['%splitter%']);
            })
            ->orWhereRaw('LOWER(nama_perangkat) LIKE ?', ['%splitter%']);
        })
        ->withCount([
            'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
            'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
            'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
        ]);

        $perangkat = $query->get();

        $perangkat->each(function ($item) {
            $item->stok_tersedia = max(0, $item->jumlah_stok - $item->stok_terpakai - $item->stok_maintenance - $item->stok_rusak);
            if (preg_match('/1:\d+/', $item->nama_perangkat, $matches)) {
                $item->rasio_default = $matches[0];
            } else {
                $item->rasio_default = '1:4';
            }
            $item->available_serials = $item->modem->map(function ($m) {
                return [
                    'id' => $m->id,
                    'serial_number' => $m->serial_number ?: ('ID-' . $m->id . ' (Tanpa SN)'),
                    'mac_address' => $m->mac_address,
                ];
            })->values()->toArray();
        });

        return $perangkat;
    }

    public function getPerangkatByKategori(string $categoryKey)
    {
        $query = Perangkat::with(['kategori', 'modem' => function ($q) {
            $q->where('status_id', LogistikStatus::TERSEIDA)
              ->select('id', 'logistik_id', 'serial_number', 'mac_address');
        }])
        ->where(function ($q) use ($categoryKey) {
            $q->whereHas('kategori', function ($sub) use ($categoryKey) {
                $sub->whereRaw('LOWER(nama_logistik) LIKE ?', ["%{$categoryKey}%"]);
            })
            ->orWhereRaw('LOWER(nama_perangkat) LIKE ?', ["%{$categoryKey}%"]);
        })
        ->withCount([
            'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
            'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
            'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
        ])
        ->get();

        $query->each(function ($item) {
            $item->stok_tersedia = max(0, $item->jumlah_stok - $item->stok_terpakai - $item->stok_maintenance - $item->stok_rusak);
            $item->available_serials = $item->modem->map(function ($m) {
                return [
                    'id' => $m->id,
                    'serial_number' => $m->serial_number ?: ('ID-' . $m->id . ' (Tanpa SN)'),
                    'mac_address' => $m->mac_address,
                ];
            })->values()->toArray();
        });

        return $query;
    }

    public function apiPerangkatByKategori(Request $request, $category)
    {
        return response()->json($this->getPerangkatByKategori($category));
    }

    public function odc(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = ODC::withCount('odp')->with('olt', 'modemDetail.perangkat');

        if ($search) {
            $query->where('nama_odc', 'like', "%{$search}%")
                ->orWhereHas('olt', function ($q) use ($search) {
                    $q->where('nama_lokasi', 'like', "%{$search}%");
                });
        }

        $odcs = $query->paginate($perPage)->appends($request->query());

        $odcs->getCollection()->transform(function ($odc) {
            $odc->splitter_list = $odc->splitters()->whereNotNull('rasio')->get();
            $odc->splitter_count = $odc->splitter_list->count();
            $odc->splitter_capacity = $odc->splitter_list->sum(fn($s) => $s->kapasitas);
            $odc->splitter_rencana = $odc->rasio ? $odc->rasio : null;
            $odc->splitter_rencana_kapasitas = $odc->rasio ? $odc->kapasitas_rencana : 0;
            return $odc;
        });

        return view('/NOC/data-odc', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'lokasi' => Lokasi::all(),
            'odc' => $odcs,
            'perangkatSplitter' => $this->getPerangkatSplitter(),
            'perangkatOdc' => $this->getPerangkatByKategori('odc'),
        ]);
    }

    public function odp(Request $request)
    {
        $search = $request->get('search');
        $perPage = $request->get('per_page', 10);

        $query = ODP::withCount('customer')->with('odc', 'modemDetail.perangkat');

        if ($search) {
            $query->where('nama_odp', 'like', "%{$search}%")
                ->orWhereHas('odc', function ($q) use ($search) {
                    $q->where('nama_odc', 'like', "%{$search}%");
                });
        }

        $odps = $query->paginate($perPage)->appends($request->query());

        $odps->getCollection()->transform(function ($odp) {
            $odp->splitter_list = $odp->splitters()->whereNotNull('rasio')->get();
            $odp->splitter_count = $odp->splitter_list->count();
            $odp->splitter_capacity = $odp->splitter_list->sum(fn($s) => $s->kapasitas);
            $odp->splitter_rencana = $odp->rasio ? $odp->rasio : null;
            $odp->splitter_rencana_kapasitas = $odp->rasio ? $odp->kapasitas_rencana : 0;
            return $odp;
        });
        return view('/NOC/data-odp', [
            'users' => auth()->user(),
            'roles' => auth()->user()->roles,
            'odp' => $odps,
            'lokasi' => ODC::all(),
            'perangkatSplitter' => $this->getPerangkatSplitter(),
            'perangkatOdp' => $this->getPerangkatByKategori('odp'),
        ]);
    }

    public function addOlt(Request $request)
    {
        $request->validate([
            'olt' => 'required|string',
            'lokasi_server' => 'required|exists:server,id',
            'gps' => 'required|string',
            'jumlah_pon' => 'nullable|integer|min:1|max:64',
        ]);
        $lokasi = new Lokasi();
        $lokasi->nama_lokasi = $request->olt;
        $lokasi->jumlah_pon = $request->jumlah_pon ?: 8;
        $lokasi->id_server = $request->lokasi_server;
        $lokasi->gps = $request->gps;

        if ($request->filled('modem_detail_id')) {
            $md = ModemDetail::where('id', $request->modem_detail_id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->first();
            if ($md) {
                $lokasi->modem_detail_id = $md->id;
            }
        }

        $lokasi->save();

        if ($lokasi->modem_detail_id) {
            ModemDetail::where('id', $lokasi->modem_detail_id)->update([
                'status_id' => LogistikStatus::TERPAKAI,
                'lokasi_id' => $lokasi->id,
                'tanggal_terpakai' => now(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'OLT Berhasil Ditambahkan', 'data' => $lokasi]);
        }
        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    public function updateOlt(Request $request, $id)
    {
        $request->validate([
            'olt' => 'required|string',
            'lokasi_server' => 'required|exists:server,id',
            'gps' => 'required|string',
            'jumlah_pon' => 'nullable|integer|min:1|max:64',
        ]);
        $lokasi = Lokasi::findOrFail($id);
        $lokasi->nama_lokasi = $request->olt;
        if ($request->filled('jumlah_pon')) {
            $lokasi->jumlah_pon = $request->jumlah_pon;
        }
        $lokasi->id_server = $request->lokasi_server;
        $lokasi->gps = $request->gps;

        if ($request->has('modem_detail_id')) {
            $newMdId = $request->filled('modem_detail_id') ? (int) $request->modem_detail_id : null;
            if ($lokasi->modem_detail_id && $lokasi->modem_detail_id !== $newMdId) {
                ModemDetail::where('id', $lokasi->modem_detail_id)->update([
                    'status_id' => LogistikStatus::TERSEIDA,
                    'lokasi_id' => null,
                    'tanggal_terpakai' => null,
                ]);
            }
            if ($newMdId && $lokasi->modem_detail_id !== $newMdId) {
                $md = ModemDetail::where('id', $newMdId)->where('status_id', LogistikStatus::TERSEIDA)->first();
                if ($md) {
                    $md->update([
                        'status_id' => LogistikStatus::TERPAKAI,
                        'lokasi_id' => $lokasi->id,
                        'tanggal_terpakai' => now(),
                    ]);
                    $lokasi->modem_detail_id = $newMdId;
                }
            } elseif (!$newMdId) {
                $lokasi->modem_detail_id = null;
            }
        }

        $lokasi->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'OLT berhasil diperbarui', 'data' => $lokasi]);
        }
        return redirect()->back()->with('success', 'OLT berhasil diperbarui');
    }

    public function deleteOlt($id)
    {
        $olt = Lokasi::findOrFail($id);
        if ($olt->odc()->count() > 0) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak dapat menghapus OLT yang masih memiliki ODC!'], 422);
            }
            return redirect()->back()->with('error', 'Tidak dapat menghapus OLT yang masih memiliki ODC!');
        }

        if ($olt->modem_detail_id) {
            ModemDetail::where('id', $olt->modem_detail_id)->update([
                'status_id' => LogistikStatus::TERSEIDA,
                'lokasi_id' => null,
                'tanggal_terpakai' => null,
            ]);
        }
        $olt->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'OLT berhasil dihapus']);
        }
        return redirect()->back()->with('success', 'OLT berhasil dihapus');
    }

    public function addOdc(Request $request)
    {
        $rules = [
            'panjang_kabel' => 'required|numeric|min:0',
            'redaman' => 'required|string',
        ];
        if (!$request->filled('odc_id')) {
            $rules['nama_odc'] = 'required|string';
            $rules['gps'] = 'required|string';
        }
        $request->validate($rules);

        $portOut = $request->filled('port_out') ? $request->port_out : null;

        // MODE BARU: ODC sebagai child splitter level PON (PON -> Splitter -> ODC)
        if ($request->filled('pon_port') && ($request->filled('logistik_id') || $request->filled('splitter_id'))) {
            $rootSplitter = null;

            // 1. Reuse splitter induk yang sudah ada (dari klik port kosong pada node splitter)
            if ($request->filled('splitter_id')) {
                $rootSplitter = ModemDetail::where('id', $request->splitter_id)
                    ->whereNull('parent_splitter_id')
                    ->first();
            }

            // 2. Cari splitter induk yang sudah menempati PON port ini
            if (!$rootSplitter) {
                $rootSplitter = ModemDetail::where('lokasi_id', $request->olt)
                    ->where('pon_port', $request->pon_port)
                    ->whereNull('odc_id')
                    ->whereNull('odp_id')
                    ->whereNull('parent_splitter_id')
                    ->orderBy('id')
                    ->first();
            }

            // 3. Buat splitter induk baru dari logistik jika belum ada
            if (!$rootSplitter && $request->filled('logistik_id')) {
                $perangkat = Perangkat::withCount([
                    'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
                    'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
                    'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
                ])->find($request->logistik_id);

                if ($perangkat) {
                    $stokTersedia = max(0, $perangkat->jumlah_stok - $perangkat->stok_terpakai - $perangkat->stok_maintenance - $perangkat->stok_rusak);
                    if ($stokTersedia >= 1) {
                        $splitterRasio = $request->rasio;
                        if (!$splitterRasio) {
                            if (preg_match('/1:\d+/', $perangkat->nama_perangkat, $matches)) {
                                $splitterRasio = $matches[0];
                            } else {
                                $splitterRasio = '1:4';
                            }
                        }

                        $sn = trim((string)($request->serial_number ?? ''));
                        $existing = null;
                        if ($request->filled('splitter_modem_detail_id')) {
                            $existing = ModemDetail::where('id', $request->splitter_modem_detail_id)
                                ->where('status_id', LogistikStatus::TERSEIDA)
                                ->first();
                        }
                        if (!$existing) {
                            $existing = ModemDetail::where('logistik_id', $perangkat->id)
                                ->where('status_id', LogistikStatus::TERSEIDA)
                                ->first();
                        }

                        if ($existing) {
                            $existing->update([
                                'serial_number' => $sn ?: $existing->serial_number,
                                'rasio' => $splitterRasio,
                                'lokasi_id' => $request->olt,
                                'pon_port' => $request->pon_port,
                                'odc_id' => null,
                                'odp_id' => null,
                                'parent_splitter_id' => null,
                                'parent_port_out' => null,
                                'status_id' => LogistikStatus::TERPAKAI,
                                'tanggal_terpakai' => now(),
                            ]);
                            $rootSplitter = $existing;
                        } else {
                            $rootSplitter = ModemDetail::create([
                                'logistik_id' => $perangkat->id,
                                'serial_number' => $sn ?: null,
                                'rasio' => $splitterRasio,
                                'lokasi_id' => $request->olt,
                                'pon_port' => $request->pon_port,
                                'odc_id' => null,
                                'odp_id' => null,
                                'parent_splitter_id' => null,
                                'parent_port_out' => null,
                                'status_id' => LogistikStatus::TERPAKAI,
                                'tanggal_terpakai' => now(),
                            ]);
                        }
                    }
                }

                if (!$rootSplitter) {
                    $message = 'Stok Splitter ' . ($perangkat->nama_perangkat ?? '') . ' di Logistik tidak mencukupi!';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $message], 422);
                    }
                    return redirect()->back()->with('toast_error', $message);
                }
            }

            if (!$rootSplitter) {
                $message = 'Splitter induk untuk PON ini belum terpasang. Pilih splitter dari logistik terlebih dahulu!';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $message], 422);
                }
                return redirect()->back()->with('toast_error', $message);
            }

            // Tentukan port OUT: manual atau otomatis port kosong pertama
            $ratioNum = 4;
            if (preg_match('/1:(\d+)/', $rootSplitter->rasio ?: '', $m)) {
                $ratioNum = (int) $m[1];
            }
            $usedPortsQuery = ODC::where('splitter_id', $rootSplitter->id);
            if ($request->filled('odc_id')) {
                $usedPortsQuery->where('id', '!=', $request->odc_id);
            }
            $usedPorts = $usedPortsQuery->pluck('port_out')->filter()->values()->all();

            if ($portOut) {
                if (in_array($portOut, $usedPorts)) {
                    $message = 'Port ' . $portOut . ' pada splitter ini sudah terisi!';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $message], 422);
                    }
                    return redirect()->back()->with('toast_error', $message);
                }
            } else {
                for ($i = 1; $i <= $ratioNum; $i++) {
                    $candidate = 'OUT-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                    if (!in_array($candidate, $usedPorts)) {
                        $portOut = $candidate;
                        break;
                    }
                }
                if (!$portOut) {
                    $message = 'Semua port (' . $ratioNum . ' port / ' . $rootSplitter->rasio . ') pada splitter ini sudah terisi!';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $message], 422);
                    }
                    return redirect()->back()->with('toast_error', $message);
                }
            }

            if ($request->filled('odc_id')) {
                $odc = ODC::find($request->odc_id);
                if (!$odc) {
                    $message = 'ODC yang dipilih tidak ditemukan!';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $message], 404);
                    }
                    return redirect()->back()->with('toast_error', $message);
                }
                if ($request->filled('nama_odc')) {
                    $odc->nama_odc = $request->nama_odc;
                }
            } else {
                $odc = new ODC();
                $odc->nama_odc = $request->nama_odc;
            }

            $odc->lokasi_id = $request->olt ?: $odc->lokasi_id;
            $odc->pon_port = $request->pon_port;
            $odc->splitter_id = $rootSplitter->id;
            $odc->port_out = $portOut;
            if ($request->filled('gps')) {
                $odc->gps = $request->gps;
            }
            $odc->rasio = $request->rasio ?: ($odc->rasio ?: $rootSplitter->rasio);
            if ($request->filled('panjang_kabel')) {
                $odc->panjang_kabel = $request->panjang_kabel;
            }
            if ($request->filled('redaman')) {
                $odc->redaman = $request->redaman;
            }

            $targetOdcMdId = $request->filled('odc_modem_detail_id') 
                ? $request->odc_modem_detail_id 
                : ($request->filled('modem_detail_id') && $request->modem_detail_id != $request->splitter_id ? $request->modem_detail_id : null);
            if ($targetOdcMdId) {
                $md = ModemDetail::where('id', $targetOdcMdId)->where('status_id', LogistikStatus::TERSEIDA)->first();
                if ($md) {
                    $odc->modem_detail_id = $md->id;
                }
            }

            $odc->save();

            if ($odc->modem_detail_id) {
                ModemDetail::where('id', $odc->modem_detail_id)->update([
                    'status_id' => LogistikStatus::TERPAKAI,
                    'odc_id' => $odc->id,
                    'tanggal_terpakai' => now(),
                ]);
            }

            $successMsg = $request->filled('odc_id') 
                ? 'ODC ' . $odc->nama_odc . ' Berhasil Dihubungkan ke Splitter PON (' . $portOut . ')' 
                : 'ODC Berhasil Ditambahkan';

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'success', 'message' => $successMsg, 'data' => $odc]);
            }
            return redirect()->back()->with('success', $successMsg);
        }

        // MODE LAMA: ODC langsung di PON port (opsional splitter di dalam ODC)
        if ($request->filled('odc_id')) {
            $odc = ODC::find($request->odc_id);
            if (!$odc) {
                $message = 'ODC yang dipilih tidak ditemukan!';
                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json(['status' => 'error', 'message' => $message], 404);
                }
                return redirect()->back()->with('toast_error', $message);
            }
            if ($request->filled('nama_odc')) {
                $odc->nama_odc = $request->nama_odc;
            }
        } else {
            $odc = new ODC();
            $odc->nama_odc = $request->nama_odc;
        }

        $odc->lokasi_id = $request->olt ?: $odc->lokasi_id;
        $odc->pon_port = $request->pon_port ?: null;
        $odc->splitter_id = null;
        $odc->port_out = null;
        if ($request->filled('gps')) {
            $odc->gps = $request->gps;
        }
        if ($request->filled('rasio')) {
            $odc->rasio = $request->rasio;
        }
        if ($request->filled('panjang_kabel')) {
            $odc->panjang_kabel = $request->panjang_kabel;
        }
        if ($request->filled('redaman')) {
            $odc->redaman = $request->redaman;
        }

        $targetOdcMdId = $request->filled('odc_modem_detail_id') 
            ? $request->odc_modem_detail_id 
            : ($request->filled('modem_detail_id') && $request->modem_detail_id != $request->splitter_id ? $request->modem_detail_id : null);
        if ($targetOdcMdId) {
            $md = ModemDetail::where('id', $targetOdcMdId)->where('status_id', LogistikStatus::TERSEIDA)->first();
            if ($md) {
                $odc->modem_detail_id = $md->id;
            }
        }

        $odc->save();

        if ($odc->modem_detail_id) {
            ModemDetail::where('id', $odc->modem_detail_id)->update([
                'status_id' => LogistikStatus::TERPAKAI,
                'odc_id' => $odc->id,
                'tanggal_terpakai' => now(),
            ]);
        }

        // 1. If logistik_id (Perangkat) is provided
        if ($request->filled('logistik_id')) {
            $perangkat = Perangkat::withCount([
                'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
                'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
                'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
            ])->find($request->logistik_id);

            if ($perangkat) {
                $stokTersedia = max(0, $perangkat->jumlah_stok - $perangkat->stok_terpakai - $perangkat->stok_maintenance - $perangkat->stok_rusak);
                if ($stokTersedia >= 1) {
                    $splitterRasio = $request->rasio;
                    if (!$splitterRasio) {
                        if (preg_match('/1:\d+/', $perangkat->nama_perangkat, $matches)) {
                            $splitterRasio = $matches[0];
                        } else {
                            $splitterRasio = '1:4';
                        }
                    }

                    $sn = trim((string)($request->serial_number ?? ''));

                    // Find available ModemDetail or create a new unit
                    $existing = ModemDetail::where('logistik_id', $perangkat->id)
                        ->where('status_id', LogistikStatus::TERSEIDA)
                        ->first();

                    if ($existing) {
                        $existing->update([
                            'serial_number' => $sn ?: $existing->serial_number,
                            'rasio' => $splitterRasio,
                            'pon_port' => $odc->pon_port,
                            'odc_id' => $odc->id,
                            'odp_id' => null,
                            'status_id' => LogistikStatus::TERPAKAI,
                            'tanggal_terpakai' => now(),
                        ]);
                    } else {
                        ModemDetail::create([
                            'logistik_id' => $perangkat->id,
                            'serial_number' => $sn ?: null,
                            'rasio' => $splitterRasio,
                            'pon_port' => $odc->pon_port,
                            'odc_id' => $odc->id,
                            'odp_id' => null,
                            'status_id' => LogistikStatus::TERPAKAI,
                            'tanggal_terpakai' => now(),
                        ]);
                    }

                    if (!$odc->rasio) {
                        $odc->rasio = $splitterRasio;
                        $odc->save();
                    }
                }
            }
        }
        // 2. If splitter_id (single ModemDetail) is provided
        elseif ($request->filled('splitter_id')) {
            $splitter = ModemDetail::where('id', $request->splitter_id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->first();
            if ($splitter) {
                $splitter->update([
                    'odc_id' => $odc->id,
                    'odp_id' => null,
                    'pon_port' => $odc->pon_port,
                    'status_id' => LogistikStatus::TERPAKAI,
                    'tanggal_terpakai' => now(),
                ]);
                if (!$odc->rasio && $splitter->rasio) {
                    $odc->rasio = $splitter->rasio;
                    $odc->save();
                }
            }
        }
        // 3. If splitter_ids (array of ModemDetail) is provided
        elseif ($request->filled('splitter_ids')) {
            $splitterIds = (array) $request->splitter_ids;
            if (!$odc->rasio) {
                $firstSplitter = ModemDetail::whereIn('id', $splitterIds)->first();
                if ($firstSplitter && $firstSplitter->rasio) {
                    $odc->rasio = $firstSplitter->rasio;
                    $odc->save();
                }
            }
            ModemDetail::whereIn('id', $splitterIds)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->update([
                    'odc_id' => $odc->id,
                    'odp_id' => null,
                    'pon_port' => $odc->pon_port,
                    'status_id' => LogistikStatus::TERPAKAI,
                    'tanggal_terpakai' => now(),
                ]);
        }

        $successMsg = $request->filled('odc_id') 
            ? 'ODC ' . $odc->nama_odc . ' Berhasil Dihubungkan ke PON' 
            : 'Data Berhasil Ditambahkan';

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => $successMsg, 'data' => $odc]);
        }
        return redirect()->back()->with('success', $successMsg);
    }

    public function updateOdc(Request $request, $id)
    {
        $request->validate([
            'nama_odc' => 'required|string',
            'olt' => 'required|exists:lokasi,id',
            'gps' => 'required|string',
            'panjang_kabel' => 'required|numeric|min:0',
            'redaman' => 'required|string',
        ]);
        $odc = ODC::findOrFail($id);
        $odc->nama_odc = $request->nama_odc;
        $odc->lokasi_id = $request->olt;
        $odc->pon_port = $request->has('pon_port') ? $request->pon_port : $odc->pon_port;
        if ($request->has('splitter_id')) {
            $odc->splitter_id = $request->filled('splitter_id') ? $request->splitter_id : null;
        }
        if ($request->has('port_out')) {
            $odc->port_out = $request->filled('port_out') ? $request->port_out : null;
        }
        $odc->gps = $request->gps;
        if ($request->filled('rasio')) {
            $odc->rasio = $request->rasio;
        }
        if ($request->filled('panjang_kabel')) {
            $odc->panjang_kabel = $request->panjang_kabel;
        }
        if ($request->has('redaman')) {
            $odc->redaman = $request->filled('redaman') ? $request->redaman : null;
        }

        if ($request->has('odc_modem_detail_id') || $request->has('modem_detail_id')) {
            $targetOdcMdId = $request->has('odc_modem_detail_id') 
                ? $request->odc_modem_detail_id 
                : $request->modem_detail_id;
            $newMdId = !empty($targetOdcMdId) ? (int) $targetOdcMdId : null;
            if ($odc->modem_detail_id && $odc->modem_detail_id !== $newMdId) {
                ModemDetail::where('id', $odc->modem_detail_id)->update([
                    'status_id' => LogistikStatus::TERSEIDA,
                    'odc_id' => null,
                    'tanggal_terpakai' => null,
                ]);
            }
            if ($newMdId && $odc->modem_detail_id !== $newMdId) {
                $md = ModemDetail::where('id', $newMdId)->where('status_id', LogistikStatus::TERSEIDA)->first();
                if ($md) {
                    $md->update([
                        'status_id' => LogistikStatus::TERPAKAI,
                        'odc_id' => $odc->id,
                        'tanggal_terpakai' => now(),
                    ]);
                    $odc->modem_detail_id = $newMdId;
                }
            } elseif (!$newMdId) {
                $odc->modem_detail_id = null;
            }
        }

        $odc->save();

        if ($request->filled('splitter_ids')) {
            ModemDetail::whereIn('id', $request->splitter_ids)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->update([
                    'odc_id' => $odc->id,
                    'status_id' => LogistikStatus::TERPAKAI,
                    'tanggal_terpakai' => now(),
                ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'ODC berhasil diperbarui', 'data' => $odc]);
        }
        return redirect()->back()->with('success', 'ODC berhasil diperbarui');
    }

    public function deleteOdc($id)
    {
        $odc = ODC::findOrFail($id);
        if ($odc->odp()->count() > 0) {
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Tidak dapat menghapus ODC yang masih memiliki ODP!'], 422);
            }
            return redirect()->back()->with('error', 'Tidak dapat menghapus ODC yang masih memiliki ODP!');
        }
        $parentSplitterId = $odc->splitter_id;
        ModemDetail::where('odc_id', $odc->id)->update([
            'odc_id' => null,
            'status_id' => LogistikStatus::TERSEIDA,
            'tanggal_terpakai' => null,
        ]);
        if ($odc->modem_detail_id) {
            ModemDetail::where('id', $odc->modem_detail_id)->update([
                'odc_id' => null,
                'status_id' => LogistikStatus::TERSEIDA,
                'tanggal_terpakai' => null,
            ]);
        }
        $odc->delete();

        // Lepaskan port output splitter level PON (OUT-xx) ketika ODC child dihapus
        if ($parentSplitterId) {
            $parent = ModemDetail::find($parentSplitterId);
            if ($parent && $parent->odc_id === null) {
                $remainingOdcs = ODC::where('splitter_id', $parentSplitterId)->count();
                if ($remainingOdcs === 0) {
                    $parent->update([
                        'lokasi_id' => null,
                        'pon_port' => null,
                        'status_id' => LogistikStatus::TERSEIDA,
                        'tanggal_terpakai' => null,
                    ]);
                }
            }
        }

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'ODC berhasil dihapus']);
        }
        return redirect()->back()->with('success', 'ODC berhasil dihapus');
    }

    public function addOdp(Request $request)
    {
        $request->validate([
            'nama_odp' => 'required|string',
            'odc' => 'required|exists:odc,id',
            'gps' => 'required|string',
            'panjang_kabel' => 'required|numeric|min:0',
            'redaman' => 'required|string',
        ]);
        $odp = new ODP();
        $odp->nama_odp = $request->nama_odp;
        $odp->odc_id = $request->odc;
        $odp->splitter_id = $request->splitter_id ?: null;
        $odp->port_out = $request->port_out ?: null;
        $odp->gps = $request->gps;
        $odp->panjang_kabel = $request->filled('panjang_kabel') ? $request->panjang_kabel : null;
        $odp->redaman = $request->filled('redaman') ? $request->redaman : null;

        if ($request->filled('splitter_id')) {
            $splitter = ModemDetail::find($request->splitter_id);
            if ($splitter && $splitter->rasio) {
                $odp->rasio = $splitter->rasio;
            }
        } elseif ($request->filled('rasio')) {
            $odp->rasio = $request->rasio;
        }

        if ($request->filled('modem_detail_id')) {
            $md = ModemDetail::where('id', $request->modem_detail_id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->first();
            if ($md) {
                $odp->modem_detail_id = $md->id;
            }
        }

        $odp->save();

        if ($odp->modem_detail_id) {
            ModemDetail::where('id', $odp->modem_detail_id)->update([
                'status_id' => LogistikStatus::TERPAKAI,
                'odp_id' => $odp->id,
                'tanggal_terpakai' => now(),
            ]);
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'ODP Berhasil Ditambahkan', 'data' => $odp]);
        }
        return redirect()->back()->with('success', 'Data Berhasil Ditambahkan');
    }

    public function updateOdp(Request $request, $id)
    {
        $request->validate([
            'nama_odp' => 'required|string',
            'odc' => 'required|exists:odc,id',
            'gps' => 'required|string',
            'panjang_kabel' => 'required|numeric|min:0',
            'redaman' => 'required|string',
        ]);
        $odp = ODP::findOrFail($id);
        $odp->nama_odp = $request->nama_odp;
        $odp->odc_id = $request->odc;
        $odp->splitter_id = $request->splitter_id ?: null;
        $odp->port_out = $request->port_out ?: null;
        $odp->gps = $request->gps;
        $odp->panjang_kabel = $request->filled('panjang_kabel') ? $request->panjang_kabel : null;
        $odp->redaman = $request->filled('redaman') ? $request->redaman : null;

        if ($request->filled('splitter_id')) {
            $splitter = ModemDetail::find($request->splitter_id);
            if ($splitter && $splitter->rasio) {
                $odp->rasio = $splitter->rasio;
            }
        } elseif ($request->filled('rasio')) {
            $odp->rasio = $request->rasio;
        } else {
            $odp->rasio = null;
        }

        if ($request->has('modem_detail_id')) {
            $newMdId = $request->filled('modem_detail_id') ? (int) $request->modem_detail_id : null;
            if ($odp->modem_detail_id && $odp->modem_detail_id !== $newMdId) {
                ModemDetail::where('id', $odp->modem_detail_id)->update([
                    'status_id' => LogistikStatus::TERSEIDA,
                    'odp_id' => null,
                    'tanggal_terpakai' => null,
                ]);
            }
            if ($newMdId && $odp->modem_detail_id !== $newMdId) {
                $md = ModemDetail::where('id', $newMdId)->where('status_id', LogistikStatus::TERSEIDA)->first();
                if ($md) {
                    $md->update([
                        'status_id' => LogistikStatus::TERPAKAI,
                        'odp_id' => $odp->id,
                        'tanggal_terpakai' => now(),
                    ]);
                    $odp->modem_detail_id = $newMdId;
                }
            } elseif (!$newMdId) {
                $odp->modem_detail_id = null;
            }
        }

        $odp->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'ODP berhasil diperbarui', 'data' => $odp]);
        }
        return redirect()->back()->with('success', 'ODP berhasil diperbarui');
    }

    public function deleteOdp($id)
    {
        $odp = ODP::findOrFail($id);
        ModemDetail::where('odp_id', $odp->id)->update([
            'odp_id' => null,
            'status_id' => LogistikStatus::TERSEIDA,
            'tanggal_terpakai' => null,
        ]);
        if ($odp->modem_detail_id) {
            ModemDetail::where('id', $odp->modem_detail_id)->update([
                'odp_id' => null,
                'status_id' => LogistikStatus::TERSEIDA,
                'tanggal_terpakai' => null,
            ]);
        }
        $odp->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'ODP berhasil dihapus']);
        }
        return redirect()->back()->with('success', 'ODP berhasil dihapus');
    }

    public function syncOdcPonPorts(Request $request)
    {
        $odcs = ODC::all();
        $updatedCount = 0;

        foreach ($odcs as $odc) {
            if (preg_match('/PON\s*[-_]?\s*(\d+)/i', $odc->nama_odc, $matches)) {
                $ponNum = (int) $matches[1];
                $ponCode = 'PON-' . str_pad($ponNum, 2, '0', STR_PAD_LEFT);
                if ($odc->pon_port !== $ponCode) {
                    $odc->pon_port = $ponCode;
                    $odc->save();
                    $updatedCount++;
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => "Berhasil menyinkronkan {$updatedCount} ODC ke Port PON OLT berdasarkan nama perangkat.",
                'updated_count' => $updatedCount,
            ]);
        }

        return redirect()->back()->with('toast_success', "Berhasil menyinkronkan {$updatedCount} ODC ke Port PON OLT.");
    }

    public function storeSplitter(Request $request)
    {
        $request->validate([
            'logistik_id' => 'required|exists:perangkat,id',
            'rasio' => 'required|string',
            'modem_detail_id' => 'nullable|exists:ModemDetail,id',
            'parent_splitter_id' => 'nullable|exists:ModemDetail,id',
            'parent_port_out' => 'nullable|string',
        ]);

        $perangkat = Perangkat::withCount([
            'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
            'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
            'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
        ])->findOrFail($request->logistik_id);

        $stokTersedia = max(0, $perangkat->jumlah_stok - $perangkat->stok_terpakai - $perangkat->stok_maintenance - $perangkat->stok_rusak);

        if ($stokTersedia < 1) {
            $msg = 'Stok Splitter ' . $perangkat->nama_perangkat . ' di Logistik tidak mencukupi!';
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        $parentSplitterId = $request->parent_splitter_id ?: null;
        $parentPortOut = $request->parent_port_out ?: null;
        $lokasiId = $request->lokasi_id ?: ($request->olt ?: null);
        $ponPort = $request->pon_port ?: null;
        $odcId = $request->odc_id ?: ($request->odc ?: null);
        $odpId = $request->odp_id ?: ($request->odp ?: null);

        // Jika dipasang di PON tanpa parent_splitter_id, cek apakah sudah ada Root Splitter di PON ini
        if (!$parentSplitterId && $lokasiId && $ponPort && !$odcId && !$odpId) {
            $existingRootSplitter = ModemDetail::where('lokasi_id', $lokasiId)
                ->where('pon_port', $ponPort)
                ->whereNull('odc_id')
                ->whereNull('odp_id')
                ->whereNull('parent_splitter_id')
                ->where('status_id', LogistikStatus::TERPAKAI)
                ->first();
            if ($existingRootSplitter) {
                $parentSplitterId = $existingRootSplitter->id;
            }
        }

        // If parent splitter is provided, inherit context and validate port availability
        if ($parentSplitterId) {
            $parentSplitter = ModemDetail::findOrFail($parentSplitterId);
            $lokasiId = $lokasiId ?: $parentSplitter->lokasi_id;
            $ponPort = $ponPort ?: $parentSplitter->pon_port;
            $odcId = $odcId ?: $parentSplitter->odc_id;
            $odpId = $odpId ?: $parentSplitter->odp_id;

            // Ratio of parent splitter
            $parentRatioNum = 4;
            if (preg_match('/1:(\d+)/', $parentSplitter->rasio ?: '', $m)) {
                $parentRatioNum = (int) $m[1];
            }

            // Check used ports on parent splitter
            $usedPortsChildSplitters = ModemDetail::where('parent_splitter_id', $parentSplitter->id)
                ->where('status_id', LogistikStatus::TERPAKAI)
                ->pluck('parent_port_out')
                ->filter()
                ->values()
                ->all();
            $usedPortsOdcs = ODC::where('splitter_id', $parentSplitter->id)->pluck('port_out')->filter()->values()->all();
            $usedPortsOdps = ODP::where('splitter_id', $parentSplitter->id)->pluck('port_out')->filter()->values()->all();

            $allUsedPorts = array_unique(array_merge($usedPortsChildSplitters, $usedPortsOdcs, $usedPortsOdps));

            if ($parentPortOut) {
                if (in_array($parentPortOut, $allUsedPorts)) {
                    $msg = 'Port ' . $parentPortOut . ' pada splitter induk sudah terisi!';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $msg], 422);
                    }
                    return redirect()->back()->with('toast_error', $msg);
                }
            } else {
                // Find first available port
                for ($i = 1; $i <= $parentRatioNum; $i++) {
                    $candidate = 'OUT-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);
                    if (!in_array($candidate, $allUsedPorts)) {
                        $parentPortOut = $candidate;
                        break;
                    }
                }
                if (!$parentPortOut) {
                    $msg = 'Semua port (' . $parentRatioNum . ' port / ' . $parentSplitter->rasio . ') pada splitter induk sudah terisi!';
                    if ($request->wantsJson() || $request->ajax()) {
                        return response()->json(['status' => 'error', 'message' => $msg], 422);
                    }
                    return redirect()->back()->with('toast_error', $msg);
                }
            }
        }

        $createdSplitter = null;

        if ($request->filled('modem_detail_id')) {
            $existing = ModemDetail::where('id', $request->modem_detail_id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->first();
            if ($existing) {
                $existing->update([
                    'rasio' => $request->rasio,
                    'lokasi_id' => $lokasiId,
                    'pon_port' => $ponPort,
                    'odc_id' => $odcId,
                    'odp_id' => $odpId,
                    'parent_splitter_id' => $parentSplitterId,
                    'parent_port_out' => $parentPortOut,
                    'status_id' => LogistikStatus::TERPAKAI,
                    'tanggal_terpakai' => now(),
                ]);
                $createdSplitter = $existing;
            }
        }

        if (!$createdSplitter) {
            $serialNumbers = (array) ($request->serial_number ?? ['']);
            foreach ($serialNumbers as $sn) {
                $snTrim = trim((string)$sn);

                $existing = ModemDetail::where('logistik_id', $request->logistik_id)
                    ->where('status_id', LogistikStatus::TERSEIDA)
                    ->where(function($q) use ($snTrim) {
                        if ($snTrim !== '') {
                            $q->where('serial_number', $snTrim)->orWhereNull('serial_number');
                        }
                    })->first();

                if ($existing) {
                    $existing->update([
                        'serial_number' => $snTrim ?: $existing->serial_number,
                        'rasio' => $request->rasio,
                        'lokasi_id' => $lokasiId,
                        'pon_port' => $ponPort,
                        'odc_id' => $odcId,
                        'odp_id' => $odpId,
                        'parent_splitter_id' => $parentSplitterId,
                        'parent_port_out' => $parentPortOut,
                        'status_id' => LogistikStatus::TERPAKAI,
                        'tanggal_terpakai' => now(),
                    ]);
                    $createdSplitter = $existing;
                } else {
                    $createdSplitter = ModemDetail::create([
                        'logistik_id' => $request->logistik_id,
                        'serial_number' => $snTrim ?: null,
                        'rasio' => $request->rasio,
                        'lokasi_id' => $lokasiId,
                        'pon_port' => $ponPort,
                        'odc_id' => $odcId,
                        'odp_id' => $odpId,
                        'parent_splitter_id' => $parentSplitterId,
                        'parent_port_out' => $parentPortOut,
                        'status_id' => LogistikStatus::TERPAKAI,
                        'tanggal_terpakai' => now(),
                    ]);
                }
            }
        }

        $msg = 'Splitter (' . $request->rasio . ') berhasil dipasang' . ($parentPortOut ? ' pada Port ' . $parentPortOut : '') . ' dan stok Logistik terupdate.';
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => $msg, 'data' => $createdSplitter]);
        }
        return redirect()->back()->with('toast_success', $msg);
    }

    public function storeOdcSplitter(Request $request, $id)
    {
        $request->validate([
            'logistik_id' => 'required|exists:perangkat,id',
            'rasio' => 'required|string',
            'serial_number' => 'nullable',
        ]);

        $perangkat = Perangkat::withCount([
            'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
            'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
            'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
        ])->findOrFail($request->logistik_id);

        $stokTersedia = max(0, $perangkat->jumlah_stok - $perangkat->stok_terpakai - $perangkat->stok_maintenance - $perangkat->stok_rusak);

        if ($stokTersedia < 1) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Stok Splitter ' . $perangkat->nama_perangkat . ' di Logistik tidak mencukupi!'], 422);
            }
            return redirect()->back()->with('toast_error', 'Stok Splitter ' . $perangkat->nama_perangkat . ' di Logistik tidak mencukupi!');
        }

        $serialNumbers = (array) ($request->serial_number ?? ['']);
        $parentSplitterId = $request->parent_splitter_id ?: null;
        $parentPortOut = $request->parent_port_out ?: null;

        $odcId = $id;
        $odc = ODC::find($odcId);
        $lokasiId = $odc ? $odc->lokasi_id : null;
        $ponPort = $odc ? $odc->pon_port : null;

        if ($parentSplitterId) {
            $pSp = ModemDetail::find($parentSplitterId);
            if ($pSp && $pSp->id != $odcId) {
                if ($pSp->odc_id) {
                    $odcId = $pSp->odc_id;
                }
            } else {
                $parentSplitterId = null;
            }
        }

        if ($request->filled('modem_detail_id')) {
            $existing = ModemDetail::where('id', $request->modem_detail_id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->first();
            if ($existing) {
                $existing->update([
                    'rasio' => $request->rasio,
                    'lokasi_id' => $lokasiId,
                    'pon_port' => $ponPort,
                    'odc_id' => $odcId,
                    'odp_id' => null,
                    'parent_splitter_id' => $parentSplitterId,
                    'parent_port_out' => $parentPortOut,
                    'status_id' => LogistikStatus::TERPAKAI,
                    'tanggal_terpakai' => now(),
                ]);
            }
        } else {
            foreach ($serialNumbers as $sn) {
                $snTrim = trim((string)$sn);

                $existing = ModemDetail::where('logistik_id', $request->logistik_id)
                    ->where('status_id', LogistikStatus::TERSEIDA)
                    ->where(function($q) use ($snTrim) {
                        if ($snTrim !== '') {
                            $q->where('serial_number', $snTrim)->orWhereNull('serial_number');
                        }
                    })->first();

                if ($existing) {
                    $existing->update([
                        'serial_number' => $snTrim ?: $existing->serial_number,
                        'rasio' => $request->rasio,
                        'lokasi_id' => $lokasiId,
                        'pon_port' => $ponPort,
                        'odc_id' => $odcId,
                        'odp_id' => null,
                        'parent_splitter_id' => $parentSplitterId,
                        'parent_port_out' => $parentPortOut,
                        'status_id' => LogistikStatus::TERPAKAI,
                        'tanggal_terpakai' => now(),
                    ]);
                } else {
                    ModemDetail::create([
                        'logistik_id' => $request->logistik_id,
                        'serial_number' => $snTrim ?: null,
                        'rasio' => $request->rasio,
                        'lokasi_id' => $lokasiId,
                        'pon_port' => $ponPort,
                        'odc_id' => $odcId,
                        'odp_id' => null,
                        'parent_splitter_id' => $parentSplitterId,
                        'parent_port_out' => $parentPortOut,
                        'status_id' => LogistikStatus::TERPAKAI,
                        'tanggal_terpakai' => now(),
                    ]);
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Splitter (' . $request->rasio . ') berhasil dipasang pada ODC dan stok Logistik terupdate.']);
        }
        return redirect()->back()->with('toast_success', 'Splitter (' . $request->rasio . ') berhasil dipasang pada ODC dan stok Logistik terupdate.');
    }

    public function storeOdpSplitter(Request $request, $id)
    {
        $request->validate([
            'logistik_id' => 'required|exists:perangkat,id',
            'rasio' => 'required|string',
            'serial_number' => 'nullable',
        ]);

        $perangkat = Perangkat::withCount([
            'modem as stok_terpakai' => fn($q) => $q->where('status_id', LogistikStatus::TERPAKAI),
            'modem as stok_maintenance' => fn($q) => $q->where('status_id', LogistikStatus::MAINTENANCE),
            'modem as stok_rusak' => fn($q) => $q->where('status_id', LogistikStatus::RUSAK),
        ])->findOrFail($request->logistik_id);

        $stokTersedia = max(0, $perangkat->jumlah_stok - $perangkat->stok_terpakai - $perangkat->stok_maintenance - $perangkat->stok_rusak);

        if ($stokTersedia < 1) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => 'Stok Splitter ' . $perangkat->nama_perangkat . ' di Logistik tidak mencukupi!'], 422);
            }
            return redirect()->back()->with('toast_error', 'Stok Splitter ' . $perangkat->nama_perangkat . ' di Logistik tidak mencukupi!');
        }

        $odp = ODP::find($id);
        $odc = $odp && $odp->odc_id ? ODC::find($odp->odc_id) : null;
        $lokasiId = $odc ? $odc->lokasi_id : ($odp ? $odp->lokasi_id : null);
        $ponPort = $odc ? $odc->pon_port : null;

        if ($request->filled('modem_detail_id')) {
            $existing = ModemDetail::where('id', $request->modem_detail_id)
                ->where('status_id', LogistikStatus::TERSEIDA)
                ->first();
            if ($existing) {
                $existing->update([
                    'rasio' => $request->rasio,
                    'lokasi_id' => $lokasiId,
                    'pon_port' => $ponPort,
                    'odc_id' => null,
                    'odp_id' => $id,
                    'parent_splitter_id' => null,
                    'parent_port_out' => null,
                    'status_id' => LogistikStatus::TERPAKAI,
                    'tanggal_terpakai' => now(),
                ]);
            }
        } else {
            $serialNumbers = (array) ($request->serial_number ?? ['']);
            foreach ($serialNumbers as $sn) {
                $snTrim = trim((string)$sn);

                $existing = ModemDetail::where('logistik_id', $request->logistik_id)
                    ->where('status_id', LogistikStatus::TERSEIDA)
                    ->where(function($q) use ($snTrim) {
                        if ($snTrim !== '') {
                            $q->where('serial_number', $snTrim)->orWhereNull('serial_number');
                        }
                    })->first();

                if ($existing) {
                    $existing->update([
                        'serial_number' => $snTrim ?: $existing->serial_number,
                        'rasio' => $request->rasio,
                        'lokasi_id' => $lokasiId,
                        'pon_port' => $ponPort,
                        'odc_id' => null,
                        'odp_id' => $id,
                        'parent_splitter_id' => null,
                        'parent_port_out' => null,
                        'status_id' => LogistikStatus::TERPAKAI,
                        'tanggal_terpakai' => now(),
                    ]);
                } else {
                    ModemDetail::create([
                        'logistik_id' => $request->logistik_id,
                        'serial_number' => $snTrim ?: null,
                        'rasio' => $request->rasio,
                        'lokasi_id' => $lokasiId,
                        'pon_port' => $ponPort,
                        'odc_id' => null,
                        'odp_id' => $id,
                        'parent_splitter_id' => null,
                        'parent_port_out' => null,
                        'status_id' => LogistikStatus::TERPAKAI,
                        'tanggal_terpakai' => now(),
                    ]);
                }
            }
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => 'Splitter ODP berhasil dipasang dan mengurangi stok Logistik']);
        }
        return redirect()->back()->with('toast_success', 'Splitter berhasil dipasang dan mengurangi stok Logistik');
    }

    public function deleteSplitter($id, ?Request $request = null)
    {
        $splitter = ModemDetail::findOrFail($id);

        // Disconnect direct children
        ModemDetail::where('parent_splitter_id', $id)->update([
            'parent_splitter_id' => null,
            'parent_port_out' => null,
        ]);
        ODP::where('splitter_id', $id)->update([
            'splitter_id' => null,
            'port_out' => null,
        ]);
        ODC::where('splitter_id', $id)->update([
            'splitter_id' => null,
            'port_out' => null,
        ]);

        if ($splitter->logistik_id) {
            $splitter->update([
                'lokasi_id' => null,
                'pon_port' => null,
                'odc_id' => null,
                'odp_id' => null,
                'parent_splitter_id' => null,
                'parent_port_out' => null,
                'status_id' => LogistikStatus::TERSEIDA,
                'tanggal_terpakai' => null,
            ]);
        } else {
            $splitter->delete();
        }

        if (request()->wantsJson() || request()->ajax() || ($request && ($request->wantsJson() || $request->ajax()))) {
            return response()->json(['status' => 'success', 'message' => 'Splitter berhasil dilepas dan dikembalikan ke stok Logistik']);
        }
        return redirect()->back()->with('toast_success', 'Splitter berhasil dilepas dan dikembalikan ke stok Logistik');
    }

    public function updateSplitter(Request $request, $id)
    {
        $splitter = ModemDetail::with('perangkat')->findOrFail($id);

        $request->validate([
            'rasio' => 'required|string',
            'serial_number' => 'nullable|string',
            'pon_port' => 'nullable|string',
        ]);

        // Check if new ratio can accommodate currently connected children
        $childPorts = ModemDetail::where('parent_splitter_id', $splitter->id)
            ->where('status_id', LogistikStatus::TERPAKAI)
            ->pluck('parent_port_out')
            ->filter()
            ->all();
        $odcPorts = ODC::where('splitter_id', $splitter->id)->pluck('port_out')->filter()->all();
        $odpPorts = ODP::where('splitter_id', $splitter->id)->pluck('port_out')->filter()->all();

        $allUsedPorts = array_unique(array_merge($childPorts, $odcPorts, $odpPorts));
        $maxUsedPortNum = 0;
        foreach ($allUsedPorts as $p) {
            if (preg_match('/(\d+)/', (string) $p, $matches)) {
                $maxUsedPortNum = max($maxUsedPortNum, (int) $matches[1]);
            }
        }

        $newRatioNum = 4;
        if (preg_match('/1:(\d+)/', $request->rasio, $m)) {
            $newRatioNum = (int) $m[1];
        }

        if ($newRatioNum < $maxUsedPortNum) {
            $msg = "Rasio {$request->rasio} ({$newRatioNum} port) terlalu kecil karena Port OUT-" . str_pad($maxUsedPortNum, 2, '0', STR_PAD_LEFT) . " sudah terisi oleh perangkat/cabang!";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['status' => 'error', 'message' => $msg], 422);
            }
            return redirect()->back()->with('toast_error', $msg);
        }

        $splitter->rasio = $request->rasio;
        if ($request->has('serial_number')) {
            $splitter->serial_number = trim((string) $request->serial_number) ?: null;
        }
        if ($request->filled('pon_port')) {
            $splitter->pon_port = $request->pon_port;
        }
        $splitter->save();

        $msg = 'Data Splitter (' . $splitter->rasio . ') berhasil diperbarui.';
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['status' => 'success', 'message' => $msg, 'data' => $splitter]);
        }
        return redirect()->back()->with('toast_success', $msg);
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
