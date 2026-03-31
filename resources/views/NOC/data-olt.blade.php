@extends('layouts.contentNavbarLayout')
@section('title', 'OLT Data')
@section('vendor-style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #mapOlt {
            height: 500px;
            width: 100%;
            border-radius: 0 0 0.5rem 0.5rem;
        }

        .leaflet-container {
            z-index: 1;
        }
    </style>
@endsection
@section('vendor-script')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
@endsection
@section('content')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('dashboard') }}">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Data OLT</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Lokasi OLT</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info btn-sm" id="btnOpenMap">
                            <i class="bx bx-map-alt me-2"></i>Peta OLT
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahOlt">
                            <i class="bx bxs-add-to-queue me-2"></i>Tambah OLT
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-4">
                            <form action="{{ route('olt') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan nama OLT atau nama Server..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i></button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>No</th>
                                    <th>Data OLT</th>
                                    <th>Lokasi OLT</th>
                                    <th>Total ODC</th>
                                    <th>Total ODP</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($lokasi as $olt)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $loop->iteration + ($lokasi->currentPage() - 1) * $lokasi->perPage() }}</td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-terminal me-1 text-primary"></i>{{ $olt->nama_lokasi }}
                                        </td>
                                        @php
                                            $gps = $olt->gps;
                                            $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                            $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="javascript:void(0)" class="view-map-btn" data-gps="{{ $olt->gps }}"
                                                    data-nama="{{ $olt->nama_lokasi }}" data-bs-toggle="tooltip"
                                                    title="Lihat di Peta" data-bs-placement="bottom">
                                                    <i class="bx bx-map {{ !$olt->gps ? 'text-muted' : 'text-primary' }}"></i>
                                                </a>
                                                <a href="{{ $url }}" {{ $url != '#' ? 'target=_blank' : '' }}
                                                    data-bs-toggle="tooltip" title="Google Maps" data-bs-placement="bottom">
                                                    <i
                                                        class="bx bxl-google {{ $url == '#' ? 'text-muted' : 'text-success' }}"></i>
                                                </a>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ $olt->odc_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-warning">
                                                {{ $olt->odp_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" class="edit-olt-btn" data-id="{{ $olt->id }}" data-bs-toggle="tooltip" title="Edit OLT" data-bs-placement="bottom">
                                                    <i class="bx bx-edit text-warning"></i>
                                                </a>|
                                                <a href="/hapus/olt/{{ $olt->id }}" data-bs-toggle="tooltip" title="Hapus OLT" data-bs-placement="bottom">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data yang cocok dengan pencarian Anda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if ($lokasi->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $lokasi->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modalTambahOlt" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Tambah OLT
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/olt/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama OLT</label>
                                <input type="text" class="form-control mb-3" name="olt" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Server</label>
                                <select name="lokasi_server" id="" class="form-select mb-3">
                                    <option value="" selected disabled>Pilih Server</option>
                                    @foreach ($server as $s)
                                        <option value="{{ $s->id }}">{{ $s->lokasi_server }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi OLT</label>
                                <input type="text" class="form-control" name="gps" required placeholder="https://maps.google.com/... atau -1.0269916,110.48579129">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Edit --}}
    <div class="modal fade" id="modalEditOlt" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Edit OLT</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOltForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama OLT</label>
                                <input type="text" class="form-control mb-3" name="nama_lokasi" id="edit_nama_lokasi" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Server</label>
                                <select name="id_server" id="edit_id_server" class="form-select mb-3" required>
                                    <option value="" selected disabled>Pilih Server</option>
                                    @foreach ($server as $s)
                                        <option value="{{ $s->id }}">{{ $s->lokasi_server }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi OLT</label>
                                <input type="text" name="gps" placeholder="https://maps.google.com/... atau -1.0269916,110.48579129" class="form-control" id="edit_gps">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary btn-sm">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Modal Map --}}
    <div class="modal fade" id="modalMapOlt" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-map-alt me-2 text-primary fs-4"></i>
                        <h5 class="modal-title mb-0">Peta Lokasi OLT</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="mapOlt"></div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">* Klik marker untuk melihat info OLT</small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit OLT Logic
            document.querySelectorAll('.edit-olt-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const id = this.getAttribute('data-id');
                    fetch(`/edit/olt/${id}`)
                        .then(res => res.json())
                        .then(data => {
                            document.getElementById('edit_nama_lokasi').value = data.nama_lokasi || '';
                            document.getElementById('edit_id_server').value = data.id_server || '';
                            document.getElementById('edit_gps').value = data.gps || 'Lokasi belum di atur';
                            document.getElementById('editOltForm').action = `/update/olt/${id}`;
                            var modal = new bootstrap.Modal(document.getElementById('modalEditOlt'));
                            modal.show();
                        });
                });
            });

            // MAP LOGIC
            let map;
            let markers = [];
            const modalMapElement = document.getElementById('modalMapOlt');
            const mapModal = new bootstrap.Modal(modalMapElement);

            function initMap() {
                if (!map) {
                    map = L.map('mapOlt').setView([-1.0269916, 110.48579129], 13);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors'
                    }).addTo(map);
                }
            }

            function clearMarkers() {
                markers.forEach(m => map.removeLayer(m));
                markers = [];
            }

            function parseGps(gps) {
                if (!gps) return null;
                gps = gps.trim();

                // Format: "-8.04488, 110.48277"
                const simpleMatch = gps.match(/^(-?\d+\.\d+),\s*(-?\d+\.\d+)$/);
                if (simpleMatch) {
                    return [parseFloat(simpleMatch[1]), parseFloat(simpleMatch[2])];
                }

                // Format: "...?q=-8.04488,110.48277" or "q=loc:-8.04488+110.48277"
                const qMatch = gps.match(/q=(-?\d+\.\d+)(?:,|[+])(-?\d+\.\d+)/) || 
                               gps.match(/q=loc:(-?\d+\.\d+)(?:,|[+])(-?\d+\.\d+)/);
                if (qMatch) {
                    return [parseFloat(qMatch[1]), parseFloat(qMatch[2])];
                }
                
                // Format: "@-8.04488,110.48277" (Google Maps URL part)
                const atMatch = gps.match(/@(-?\d+\.\d+),(-?\d+\.\d+)/);
                if (atMatch) {
                    return [parseFloat(atMatch[1]), parseFloat(atMatch[2])];
                }

                // Format DMS: 7°58'42.7"S 110°24'31.8"E
                const dmsMatch = gps.match(/(\d+)°(\d+)'([\d.]+)"([NS])\s*,?\s*(\d+)°(\d+)'([\d.]+)"([EW])/i);
                if (dmsMatch) {
                    const lat = (parseInt(dmsMatch[1]) + parseInt(dmsMatch[2]) / 60 + parseFloat(dmsMatch[3]) / 3600) * (dmsMatch[4].toUpperCase() === 'S' ? -1 : 1);
                    const lng = (parseInt(dmsMatch[5]) + parseInt(dmsMatch[6]) / 60 + parseFloat(dmsMatch[7]) / 3600) * (dmsMatch[8].toUpperCase() === 'W' ? -1 : 1);
                    return [lat, lng];
                }

                return null;
            }

            // Open Map for All OLTs
            document.getElementById('btnOpenMap').addEventListener('click', function() {
                mapModal.show();
                setTimeout(() => {
                    initMap();
                    clearMarkers();
                    
                    fetch('{{ route("peta.data") }}?type=olt')
                        .then(res => res.json())
                        .then(data => {
                            const bounds = [];

                            data.forEach(olt => {
                                if (olt.lat && olt.lng) {
                                    const marker = L.marker([olt.lat, olt.lng])
                                        .addTo(map)
                                        .bindPopup(`<b>${olt.nama}</b><br>Tipe: OLT`);
                                    markers.push(marker);
                                    bounds.push([olt.lat, olt.lng]);
                                }
                            });

                            if (bounds.length > 0) {
                                map.fitBounds(bounds);
                            }
                            map.invalidateSize();
                        })
                        .catch(err => {
                            console.error('Error fetching map data:', err);
                            alert('Gagal mengambil data peta. Silakan coba lagi.');
                        });
                }, 300);
            });

            // Open Map for specific OLT
            document.querySelectorAll('.view-map-btn').forEach(btn => {
                btn.addEventListener('click', function() {
                    const gps = this.getAttribute('data-gps');
                    const nama = this.getAttribute('data-nama');
                    const coords = parseGps(gps);

                    if (!coords) {
                        alert('Koordinat tidak valid atau belum diatur.');
                        return;
                    }

                    mapModal.show();
                    setTimeout(() => {
                        initMap();
                        clearMarkers();

                        const marker = L.marker(coords)
                            .addTo(map)
                            .bindPopup(`<b>${nama}</b><br>Tipe: OLT`)
                            .openPopup();
                        markers.push(marker);

                        map.setView(coords, 16);
                        map.invalidateSize();
                    }, 300);
                });
            });

            // Adjust map size when modal is fully shown
            modalMapElement.addEventListener('shown.bs.modal', function () {
                if (map) {
                    map.invalidateSize();
                }
            });
        });
    </script>
@endsection
