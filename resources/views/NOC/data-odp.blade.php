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
                                    <th>Splitter</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($odp as $od)
                                    <tr class="text-uppercase">
                                        <td class="text-center">{{ $loop->iteration + ($odp->currentPage() - 1) * $odp->perPage() }}</td>
                                        <td class="fw-semibold">
                                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                                <span><i class="bx bx-terminal me-1 text-primary"></i>{{$od->nama_odp}}</span>
                                                @if ($od->redaman)
                                                    <span class="badge bg-label-danger py-0 px-2 font-monospace" style="font-size: 10.5px;">
                                                        <i class="bx bx-broadcast me-1"></i>{{ Str::contains(strtolower($od->redaman), 'db') ? $od->redaman : $od->redaman . ' dBm' }}
                                                    </span>
                                                @endif
                                                @if ($od->modemDetail)
                                                    <span class="badge bg-label-info py-0 px-2 font-monospace" style="font-size: 10.5px;" title="Box ODP: {{ $od->modemDetail->perangkat->nama_perangkat ?? '' }}">
                                                        <i class="bx bx-barcode me-1"></i>SN: {{ $od->modemDetail->serial_number ?: ('ID-' . $od->modemDetail->id) }}
                                                    </span>
                                                @endif
                                            </div>
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
                                            @if($od->splitter_count > 0)
                                            <span class="badge bg-info">{{ $od->splitter_count }} unit{{ $od->splitter_rencana ? ' · Rasio ' . $od->splitter_rencana : '' }} ({{ $od->splitter_capacity }} port)</span>
                                            @elseif($od->splitter_rencana)
                                            <span class="badge bg-secondary">Belum ada · Rasio {{ $od->splitter_rencana }}</span>
                                            @else
                                            <span class="badge bg-secondary">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <div class="d-flex justify-content-center gap-2">
                                                <button type="button" class="btn btn-sm btn-outline-primary kelola-splitter-btn" data-id="{{ $od->id }}" data-nama="{{ $od->nama_odp }}" data-splitters='{{ json_encode($od->splitter_list->map(fn($s) => ['id' => $s->id, 'serial' => $s->serial_number, 'rasio' => $s->rasio])) }}' data-bs-toggle="tooltip" title="Kelola Splitter">
                                                    <i class="bx bx-git-branch me-1"></i>Splitter
                                                </button>
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
                                        <td colspan="6" class="text-center">Tidak ada data yang cocok dengan pencarian Anda.</td>
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
                                <label class="form-label">Rasio Splitter</label>
                                <select name="rasio" class="form-select mb-3">
                                    <option value="" selected disabled>Pilih Rasio</option>
                                    <option value="1:4">1:4 (4 port)</option>
                                    <option value="1:8">1:8 (8 port)</option>
                                    <option value="1:16">1:16 (16 port)</option>
                                </select>
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Lokasi ODP</label>
                                <input type="text" class="form-control mb-3" name="gps" id="lokasi_odp" placeholder="GPS Lokasi ODP" required />
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Redaman (dBm)</label>
                                <input type="text" class="form-control mb-3" name="redaman" placeholder="Contoh: -19.2" />
                            </div>
                            <div class="col-sm-12">
                                <div class="p-3 bg-lighter rounded border mb-3">
                                    <label class="form-label fw-bold text-primary mb-1"><i class="bx bx-package me-1"></i>Integrasi Logistik (Box ODP)</label>
                                    <select id="modal_odp_perangkat_id" class="form-select mb-2" onchange="onOdpModalPerangkatChange(this, 'modal_odp_sn')">
                                        <option value="">-- Pilih Box ODP dari Logistik (Opsional) --</option>
                                        @foreach ($perangkatOdp ?? [] as $podp)
                                            <option value="{{ $podp->id }}">📦 {{ $podp->nama_perangkat }} (Tersedia: {{ $podp->stok_tersedia }} unit)</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label mb-1">Serial Number (SN)</label>
                                    <select name="modem_detail_id" id="modal_odp_sn" class="form-select font-monospace">
                                        <option value="">-- Tanpa SN / Pilih Nanti --</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Memilih SN otomatis memotong stok di Logistik.</small>
                                </div>
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
                                <label class="form-label">Rasio Splitter</label>
                                <select name="rasio" id="edit_rasio_odp" class="form-select mb-3">
                                    <option value="" disabled>Pilih Rasio</option>
                                    <option value="1:4">1:4 (4 port)</option>
                                    <option value="1:8">1:8 (8 port)</option>
                                    <option value="1:16">1:16 (16 port)</option>
                                </select>
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
                                <input type="text" name="gps" placeholder="https://maps.google.com/... atau -1.0269916,110.48579129" class="form-control mb-3" id="edit_gps">
                            </div>
                            <div class="col-sm-12">
                                <label class="form-label">Redaman (dBm)</label>
                                <input type="text" name="redaman" placeholder="Contoh: -19.2" class="form-control mb-3" id="edit_redaman_odp">
                            </div>
                            <div class="col-sm-12">
                                <div class="p-3 bg-lighter rounded border mb-3">
                                    <label class="form-label fw-bold text-primary mb-1"><i class="bx bx-package me-1"></i>Integrasi Logistik (Box ODP)</label>
                                    <select id="edit_odp_perangkat_id" class="form-select mb-2" onchange="onOdpModalPerangkatChange(this, 'edit_odp_sn')">
                                        <option value="">-- Pilih Box ODP dari Logistik (Opsional) --</option>
                                        @foreach ($perangkatOdp ?? [] as $podp)
                                            <option value="{{ $podp->id }}">📦 {{ $podp->nama_perangkat }} (Tersedia: {{ $podp->stok_tersedia }} unit)</option>
                                        @endforeach
                                    </select>
                                    <label class="form-label mb-1">Serial Number (SN)</label>
                                    <select name="modem_detail_id" id="edit_odp_sn" class="form-select font-monospace">
                                        <option value="">-- Tanpa SN / Pilih Nanti --</option>
                                    </select>
                                    <small class="text-muted d-block mt-1">Memilih SN otomatis memotong stok di Logistik.</small>
                                </div>
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
    {{-- Modal Splitter ODP --}}
    <div class="modal fade" id="modalKelolaSplitter" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bx bx-git-branch text-primary fs-4"></i>
                        <h5 class="modal-title mb-0">
                            Kelola Splitter
                            <span id="splitterLokasiNama" class="badge bg-info-subtle text-info-emphasis ms-1"></span>
                        </h5>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="fw-semibold mb-0">Daftar Splitter Terpasang</h6>
                        <span class="badge bg-info" id="splitterTotal">0 unit</span>
                    </div>
                    <div class="table-responsive mb-4">
                        <table class="table table-hover table-sm align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Serial Number</th>
                                    <th class="text-center">Rasio</th>
                                    <th class="text-center">Kapasitas</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody id="splitterListBody">
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada splitter terpasang</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <hr>

                    <h6 class="fw-semibold mb-3">Tambah Splitter</h6>
                    <form id="tambahSplitterForm" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-12 mb-3">
                                <label class="form-label">Splitter dari Stok Logistik <span class="text-danger">*</span></label>
                                <select name="logistik_id" id="splitterLogistikId" class="form-select" required>
                                    <option value="" selected disabled>Pilih Perangkat Splitter dari Logistik</option>
                                    @foreach ($perangkatSplitter as $ps)
                                        <option value="{{ $ps->id }}" {{ $ps->stok_tersedia < 1 ? 'disabled' : '' }}>
                                            {{ $ps->nama_perangkat }} (Tersedia: {{ $ps->stok_tersedia }} {{ $ps->kategori->nama_logistik ?? 'Unit' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Rasio Splitter <span class="text-danger">*</span></label>
                                <select name="rasio" id="splitterRasio" class="form-select" required>
                                    <option value="" selected disabled>Pilih Rasio</option>
                                    <option value="1:4">1:4 (4 port)</option>
                                    <option value="1:8">1:8 (8 port)</option>
                                    <option value="1:16">1:16 (16 port)</option>
                                </select>
                            </div>
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Serial Number Per Port</label>
                                <div id="splitterSerialInputs" class="row g-2">
                                    <div class="col-12">
                                        <small class="text-muted">Pilih rasio untuk menampilkan field serial per port</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" form="tambahSplitterForm" class="btn btn-primary btn-sm">
                        <i class="bx bx-save me-1"></i>Simpan Splitter
                    </button>
                </div>
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
        const perangkatOdpData = @json($perangkatOdp ?? []);

        function onOdpModalPerangkatChange(selectEl, targetSnSelectId, preselectedMdId = null, preselectedSn = '') {
            const snSelect = document.getElementById(targetSnSelectId);
            if (!snSelect) return;
            const devId = selectEl.value;
            const dev = (perangkatOdpData || []).find(d => String(d.id) === String(devId));

            let options = '<option value="">-- Tanpa SN / Pilih Unit Nanti --</option>';
            if (dev && dev.available_serials && dev.available_serials.length > 0) {
                let found = false;
                dev.available_serials.forEach(s => {
                    const isSel = (preselectedMdId && String(s.id) === String(preselectedMdId)) ? 'selected' : '';
                    if (isSel) found = true;
                    options += `<option value="${s.id}" ${isSel}>🔹 SN: ${s.serial_number}${s.mac_address ? ` (${s.mac_address})` : ''}</option>`;
                });
                if (preselectedMdId && !found && preselectedSn) {
                    options += `<option value="${preselectedMdId}" selected>🔹 SN: ${preselectedSn} (Unit Saat Ini)</option>`;
                }
            } else if (preselectedMdId && preselectedSn) {
                options += `<option value="${preselectedMdId}" selected>🔹 SN: ${preselectedSn} (Unit Saat Ini)</option>`;
            } else if (dev) {
                options = '<option value="">⚠️ Stok fisik berseri belum tersedia</option>';
            }
            snSelect.innerHTML = options;
        }

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
                        $('#edit_rasio_odp').val(data.rasio);
                        $('#edit_redaman_odp').val(data.redaman || '');
                        $('#editOdpForm').attr('action', '/update/odp/' + id);

                        const pId = data.modem_detail?.logistik_id || '';
                        const mdId = data.modem_detail_id || '';
                        const snText = data.modem_detail?.serial_number || '';
                        const editPSelect = document.getElementById('edit_odp_perangkat_id');
                        if (editPSelect) {
                            editPSelect.value = pId;
                            onOdpModalPerangkatChange(editPSelect, 'edit_odp_sn', mdId, snText);
                        }

                        $('#modalEditOdp').modal('show');
                    }
                });
            });

            // Splitter Logic
            const modalSplitterElement = document.getElementById('modalKelolaSplitter');
            const modalSplitter = modalSplitterElement ? new bootstrap.Modal(modalSplitterElement) : null;

            function portCount(rasio) {
                return rasio ? parseInt(rasio.split(':')[1], 10) : 0;
            }

            function buildSplitterRows(splitters) {
                const tbody = document.getElementById('splitterListBody');
                const total = document.getElementById('splitterTotal');
                if (!tbody) return;
                if (!splitters || splitters.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="text-center text-muted">Belum ada splitter terpasang</td></tr>';
                    if (total) total.textContent = '0 unit';
                    return;
                }
                if (total) total.textContent = splitters.length + ' unit';
                tbody.innerHTML = splitters.map(function (s, i) {
                    return `<tr>
                        <td class="text-center">${i + 1}</td>
                        <td><code>${s.serial || '-'}</code></td>
                        <td class="text-center"><span class="badge bg-info-subtle text-info-emphasis">${s.rasio || '-'}</span></td>
                        <td class="text-center">${portCount(s.rasio) > 0 ? portCount(s.rasio) + ' port' : '-'}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-outline-danger btn-delete-splitter" data-id="${s.id}" data-serial="${s.serial || ''}" title="Hapus ${s.serial || ''}">
                                <i class="bx bx-trash"></i>
                            </button>
                        </td>
                    </tr>`;
                }).join('');
            }

            document.querySelectorAll('.kelola-splitter-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const id = btn.getAttribute('data-id');
                    const nama = btn.getAttribute('data-nama');
                    const splitters = JSON.parse(btn.getAttribute('data-splitters') || '[]');

                    document.getElementById('splitterLokasiNama').textContent = nama;
                    document.getElementById('tambahSplitterForm').action = '/odp/splitter/' + id;
                    document.getElementById('splitterRasio').value = '';
                    buildSplitterSerialInputs();
                    buildSplitterRows(splitters);

                    if (modalSplitter) modalSplitter.show();
                });
            });

            function buildSplitterSerialInputs() {
                const container = document.getElementById('splitterSerialInputs');
                if (!container) return;
                const rasio = document.getElementById('splitterRasio').value;
                const count = portCount(rasio);
                container.innerHTML = '';
                if (count === 0) {
                    container.innerHTML = '<div class="col-12"><small class="text-muted">Pilih rasio untuk menampilkan field serial per port</small></div>';
                    return;
                }
                for (let i = 1; i <= count; i++) {
                    const col = document.createElement('div');
                    col.className = 'col-md-6 col-lg-4';
                    col.innerHTML = `<div class="input-group input-group-sm">
                        <span class="input-group-text">P${i}</span>
                        <input type="text" class="form-control" name="serial_number[]" placeholder="Serial Port ${i}" required>
                    </div>`;
                    container.appendChild(col);
                }
            }

            document.getElementById('splitterRasio').addEventListener('change', buildSplitterSerialInputs);

            modalSplitterElement.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-delete-splitter');
                if (!btn) return;
                const id = btn.getAttribute('data-id');
                const serial = btn.getAttribute('data-serial');
                if (!confirm('Yakin hapus splitter ' + serial + '?')) return;
                fetch('/splitter/delete/' + id, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                }).then(function () {
                    window.location.reload();
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

                // Format DMS: 7°58'42.7"S 110°24'31.8"E or 8° 5'32.33"S110°29'52.76"E
                const dmsMatch = gps.match(/(\d+)\s*°\s*(\d+)\s*'\s*([\d.]+)\s*[^NS]*([NS])[\s,]*(\d+)\s*°\s*(\d+)\s*'\s*([\d.]+)\s*[^EW]*([EW])/i);
                if (dmsMatch) {
                    const lat = (parseInt(dmsMatch[1]) + parseInt(dmsMatch[2]) / 60 + parseFloat(dmsMatch[3]) / 3600) * (dmsMatch[4].toUpperCase() === 'S' ? -1 : 1);
                    const lng = (parseInt(dmsMatch[5]) + parseInt(dmsMatch[6]) / 60 + parseFloat(dmsMatch[7]) / 3600) * (dmsMatch[8].toUpperCase() === 'W' ? -1 : 1);
                    return [lat, lng];
                }

                return null;
            }

            // Open Map for All ODPs
            document.getElementById('btnOpenMap').addEventListener('click', function() {
                mapModal.show();
                setTimeout(() => {
                    initMap();
                    clearMarkers();
                    
                    fetch('{{ route("peta.data") }}?type=odp')
                        .then(res => res.json())
                        .then(data => {
                            const bounds = [];

                            data.forEach(odp => {
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
                        })
                        .catch(err => {
                            console.error('Error fetching map data:', err);
                            alert('Gagal mengambil data peta. Silakan coba lagi.');
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
