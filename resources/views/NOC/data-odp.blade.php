@extends('layouts.contentNavbarLayout')
@section('title', 'Data ODP')
@section('vendor-style')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        #mapOdp {
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
            <li class="breadcrumb-item active">Data ODP</li>
        </ol>
    </nav>
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Lokasi ODP</h4>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-info btn-sm" id="btnOpenMap">
                            <i class="bx bx-map-alt me-2"></i>Peta ODP
                        </button>
                        <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#modalTambahOdp">
                            <i class="bx bxs-add-to-queue me-2"></i>Tambah ODP
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-4">
                            <form action="{{ route('odp') }}" method="GET">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="search" placeholder="Cari berdasarkan nama ODP atau nama ODC..." value="{{ request('search') }}">
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
                                    <th>Data ODP</th>
                                    <th>Lokasi ODP</th>
                                    <th>Total Pelanggan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($odp as $od)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $loop->iteration + ($odp->currentPage() - 1) * $odp->perPage() }}</td>
                                        <td class="fw-semibold">
                                            <i class="bx bx-terminal me-1 text-primary"></i>{{$od->nama_odp}}
                                        </td>
                                        @php
                                            $gps = $od->gps;
                                            $isLink = $gps && Str::startsWith($gps, ['http://', 'https://']);
                                            $url = $gps ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                        @endphp

                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-1">
                                                <a href="javascript:void(0)" class="view-map-btn" data-gps="{{ $od->gps }}"
                                                    data-nama="{{ $od->nama_odp }}" data-bs-toggle="tooltip"
                                                    title="Lihat di Peta" data-bs-placement="bottom">
                                                    <i class="bx bx-map {{ !$od->gps ? 'text-muted' : 'text-primary' }}"></i>
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
                                                {{ $od->customer_count }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <a href="#" class="edit-odp-btn" data-id="{{ $od->id }}" data-bs-toggle="tooltip" title="Edit ODP" data-bs-placement="bottom">
                                                    <i class="bx bx-edit text-warning"></i>
                                                </a>|
                                                <a href="/hapus/odp/{{ $od->id }}" data-bs-toggle="tooltip" title="Hapus ODP" data-bs-placement="bottom">
                                                    <i class="bx bx-trash text-danger"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data yang cocok dengan pencarian Anda.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        @if ($odp->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $odp->links() }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal --}}
    <div class="modal fade" id="modalTambahOdp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Tambah ODP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="/odp/add" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODC</label>
                                <select name="odc" id="" class="form-select mb-3">
                                    <option value="" selected disabled>Pilih Lokasi ODC</option>
                                    @foreach ($lokasi as $ol)
                                        <option value="{{ $ol->id }}">{{ $ol->nama_odc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Nama ODP</label>
                                <input type="text" class="form-control mb-3" name="nama_odp" id="nama_odp" placeholder="ODP Dondong 2" required />
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODP</label>
                                <input type="text" class="form-control" name="gps" id="lokasi_odp" placeholder="GPS Lokasi ODP" required />
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
    <div class="modal fade" id="modalEditOdp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <i class="bx bxs-terminal me-2"></i>
                    <h5 class="modal-title">Edit ODP</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editOdpForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <label class="form-label">Nama ODP</label>
                                <input type="text" class="form-control mb-3" name="nama_odp" id="edit_nama_odp" required>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODC</label>
                                <select name="odc" id="edit_odc" class="form-select mb-3" required>
                                    <option value="" selected disabled>Pilih ODC</option>
                                    @foreach ($lokasi as $o)
                                        <option value="{{ $o->id }}">{{ $o->nama_odc }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODP</label>
                                <input type="text" name="gps" placeholder="https://maps.google.com/... atau -1.0269916,110.48579129" class="form-control" id="edit_gps">
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
    {{-- Modal Map --}}
    <div class="modal fade" id="modalMapOdp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center">
                        <i class="bx bx-map-alt me-2 text-primary fs-4"></i>
                        <h5 class="modal-title mb-0">Peta Lokasi ODP</h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div id="mapOdp"></div>
                </div>
                <div class="modal-footer">
                    <small class="text-muted me-auto">* Klik marker untuk melihat info ODP</small>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Edit ODP Logic (keeping jQuery for compatibility)
            $('.edit-odp-btn').click(function(e) {
                e.preventDefault();
                var id = $(this).data('id');
                $.ajax({
                    url: '/edit/odp/' + id,
                    type: 'GET',
                    success: function(data) {
                        $('#edit_nama_odp').val(data.nama_odp);
                        $('#edit_odc').val(data.odc_id);
                        $('#edit_gps').val(data.gps);
                        $('#editOdpForm').attr('action', '/update/odp/' + id);
                        $('#modalEditOdp').modal('show');
                    }
                });
            });

            // MAP LOGIC
            let map;
            let markers = [];
            const modalMapElement = document.getElementById('modalMapOdp');
            const mapModal = new bootstrap.Modal(modalMapElement);

            function initMap() {
                if (!map) {
                    map = L.map('mapOdp').setView([-1.0269916, 110.48579129], 13);
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

                return null;
            }

            // Open Map for All ODPs
            document.getElementById('btnOpenMap').addEventListener('click', function() {
                mapModal.show();
                setTimeout(() => {
                    initMap();
                    clearMarkers();
                    
                    fetch('{{ route("peta.data") }}')
                        .then(res => res.json())
                        .then(data => {
                            const odpData = data.filter(d => d.jenis === 'odp');
                            const bounds = [];

                            odpData.forEach(odp => {
                                if (odp.lat && odp.lng) {
                                    const marker = L.marker([odp.lat, odp.lng])
                                        .addTo(map)
                                        .bindPopup(`<b>${odp.nama}</b><br>Tipe: ODP`);
                                    markers.push(marker);
                                    bounds.push([odp.lat, odp.lng]);
                                }
                            });

                            if (bounds.length > 0) {
                                map.fitBounds(bounds);
                            }
                            map.invalidateSize();
                        });
                }, 300);
            });

            // Open Map for specific ODP
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
                            .bindPopup(`<b>${nama}</b><br>Tipe: ODP`)
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
