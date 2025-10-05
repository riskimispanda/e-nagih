@extends('layouts/contentNavbarLayout')

@section('title', 'Antrian Instalasi')

@section('vendor-style')
<style>
    /* Card styles */
    .card {
        border-radius: 0.75rem;
        box-shadow: 0 0.125rem 0.375rem rgba(0, 0, 0, 0.05) !important;
        margin-bottom: 1.5rem;
    }
    
    .card-header {
        background-color: transparent;
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        padding: 1.25rem 1.5rem;
    }
    
    .card-header-elements {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .card-title {
        margin-bottom: 0;
        font-weight: 600;
        color: #566a7f;
    }
    
    .card-subtitle {
        color: #a1acb8;
        font-size: 0.875rem;
        margin-top: 0.25rem;
    }
    
    /* Table styles */
    .table-modern {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
    }
    
    .table-modern th {
        background-color: #f5f5f9;
        font-weight: 600;
        color: #566a7f;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem;
        vertical-align: middle;
    }
    
    .table-modern td {
        padding: 1rem;
        vertical-align: middle;
        border-top: 1px solid rgba(0, 0, 0, 0.05);
        color: #697a8d;
    }
    
    .table-modern tbody tr {
        transition: all 0.2s ease;
    }
    
    .table-modern tbody tr:hover {
        background-color: rgba(105, 108, 255, 0.04);
    }
    
    /* Badge styles */
    .badge-status {
        padding: 0.35rem 0.7rem;
        font-size: 0.75rem;
        font-weight: 500;
        border-radius: 0.375rem;
    }
    
    .badge-waiting {
        background-color: rgba(255, 171, 0, 0.16) !important;
        color: #ffab00 !important;
    }
    
    .badge-progress {
        background-color: rgba(105, 108, 255, 0.16) !important;
        color: #696cff !important;
    }
    
    .badge-completed {
        background-color: rgba(40, 199, 111, 0.16) !important;
        color: #28c76f !important;
    }
    
    /* Button styles */
    .btn-action {
        width: 32px;
        height: 32px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
        transition: all 0.2s ease;
        margin: 0 0.125rem;
    }
    
    .btn-maps {
        background-color: #f5f5f9;
        color: #697a8d;
        border: none;
    }
    
    .btn-maps:hover {
        background-color: #e1e1e9;
        color: #566a7f;
    }
    
    .btn-process {
        background-color: rgba(255, 171, 0, 0.16);
        color: #ffab00;
        border: none;
    }
    
    .btn-process:hover {
        background-color: rgba(255, 171, 0, 0.24);
        color: #ffab00;
    }
    
    .btn-complete {
        background-color: rgba(40, 199, 111, 0.16);
        color: #28c76f;
        border: none;
    }
    
    .btn-complete:hover {
        background-color: rgba(40, 199, 111, 0.24);
        color: #28c76f;
    }
    
    /* Search and filter styles */
    .search-input {
        border-radius: 0.375rem;
        border: 1px solid #d9dee3;
        padding: 0.375rem 0.75rem;
        font-size: 0.875rem;
        width: 100%;
        max-width: 250px;
    }
    
    .search-input:focus {
        border-color: #696cff;
        box-shadow: 0 0 0 0.25rem rgba(105, 108, 255, 0.1);
        outline: none;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
        .card-header-elements {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .search-input {
            max-width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .table-modern th,
        .table-modern td {
            padding: 0.75rem 0.5rem;
            font-size: 0.875rem;
        }
        
        .table-modern th:nth-child(3),
        .table-modern td:nth-child(3) {
            display: none;
        }
    }
</style>
@endsection

@section('content')
{{-- Corp --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">Antrian Priority</h5>
                        <p class="card-subtitle">Daftar Pelanggan Priority yang menunggu untuk diproses</p>
                    </div>
                    <div class="d-flex">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..." id="searchWaiting">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class='bx bx-filter me-1'></i> Filter
                            </button>
                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                <li><a class="dropdown-item" href="#">Semua</a></li>
                                <li><a class="dropdown-item" href="#">Terbaru</a></li>
                                <li><a class="dropdown-item" href="#">Terlama</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-modern" id="waitingTable">
                        <thead class="text-center">
                            <tr>
                                <th>No</th>
                                <th>Nama Perusahaan</th>
                                <th>Alamat</th>
                                <th>Lokasi</th>
                                <th>Tanggal</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @php
                            $no = 1;
                            @endphp
                            @forelse ($corp as $item)
                            <tr>
                                <td>{{$no++}}</td>
                                <td>{{$item->nama_perusahaan}}</td>
                                <td>{{$item->alamat}}</td>
                                @php
                                    $gps = $item->gps;
                                    $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                    $url = $gps 
                                        ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) 
                                        : '#';
                                @endphp

                                <td>
                                    <a href="{{ $url }}" 
                                    target="_blank" 
                                    class="btn btn-action btn-maps {{ $gps ? '' : 'disabled' }}" 
                                    data-bs-toggle="tooltip" 
                                    data-bs-placement="bottom" 
                                    title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                        <i class="bx bx-map"></i>
                                    </a>
                                </td>

                                <td>{{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge bg-warning bg-opacity-10 text-warning">
                                        {{ $item->status->nama_status }}
                                    </span>
                                </td>
                                <td>
                                    @if (auth()->user()->roles_id == 5)
                                    <a href="/corp/proses/{{ $item->id }}" class="btn btn-action btn-process" data-bs-toggle="tooltip" data-bs-placement="top" title="Proses Instalasi">
                                        <i class="bx bx-hard-hat"></i>
                                    </a>
                                    @endif
                                    <a href="/teknisi/detail-antrian/{{ $item->id }}" class="btn btn-action btn-maps" data-bs-toggle="tooltip" data-bs-placement="top" title="Lihat Detail">
                                        <i class="bx bx-info-circle"></i>
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center py-4">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class='bx bx-calendar-check text-secondary mb-2' style="font-size: 2rem;"></i>
                                        <h6 class="mb-1">Tidak ada antrian menunggu</h6>
                                        <p class="text-muted mb-0">Semua pelanggan telah diproses</p>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Personal --}}
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">Antrian Menunggu</h5>
                        <p class="card-subtitle">Daftar pelanggan yang menunggu untuk diproses</p>
                    </div>
                    <div class="d-flex">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..."
                        id="searchWaiting">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class='bx bx-filter me-1'></i> Filter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                            <li><a class="dropdown-item" href="#">Semua</a></li>
                            <li><a class="dropdown-item" href="#">Terbaru</a></li>
                            <li><a class="dropdown-item" href="#">Terlama</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern" id="waitingTable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Nama Pelanggan</th>
                            <th width="25%">Alamat</th>
                            <th width="10%">Lokasi</th>
                            <th width="15%">Tanggal</th>
                            <th width="10%">Status</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $waitingCount = 0; @endphp
                        @foreach ($data as $key => $item)
                        @if ($item->status_id == '5')
                        @php $waitingCount++; @endphp
                        <tr>
                            <td>{{ $waitingCount }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $item->nama_customer }}</span>
                                    <small
                                    class="text-muted">{{ $item->no_hp ?? 'No. HP tidak tersedia' }}</small>
                                </div>
                            </td>
                            <td>{{ $item->alamat }}</td>
                            @php
                                $gps = $item->gps;
                                $isLink = Str::startsWith($gps, ['http://', 'https://']);
                                $url = $gps 
                                    ? ($isLink ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) 
                                    : '#';
                            @endphp

                            <td>
                                <a href="{{ $url }}" 
                                target="_blank" 
                                class="btn btn-action btn-maps {{ $gps ? '' : 'disabled' }}" 
                                data-bs-toggle="tooltip" 
                                data-bs-placement="bottom" 
                                title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                    <i class="bx bx-map"></i>
                                </a>
                            </td>

                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                <span
                                class="badge badge-status bg-label-info">{{ $item->status->nama_status }}</span>
                            </td>
                            <td>
                                @if (auth()->user()->roles->name == 'Teknisi')
                                <a href="/teknisi/selesai/{{ $item->id }}"
                                    class="btn btn-action btn-process" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Proses Instalasi">
                                    <i class="bx bx-hard-hat"></i>
                                </a>
                                @elseif(auth()->user()->roles->name == 'NOC')
                                    <a href="/edit/antrian/{{ $item->id }}/noc"
                                        class="btn btn-action btn-complete" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Detail">
                                        <i class="bx bx-pencil"></i>
                                    </a>
                                @endif
                                <a href="/teknisi/detail-antrian/{{ $item->id }}"
                                    class="btn btn-action btn-maps" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Lihat Detail">
                                    <i class="bx bx-info-circle"></i>
                                </a>
                            </td>
                        </tr>
                        @endif
                        @endforeach
                        @if ($waitingCount == 0)
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-calendar-check text-secondary mb-2'
                                    style="font-size: 2rem;"></i>
                                    <h6 class="mb-1">Tidak ada antrian menunggu</h6>
                                    <p class="text-muted mb-0">Semua pelanggan telah diproses</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="card-header-elements">
                    <div>
                        <h5 class="card-title">Instalasi Dalam Proses</h5>
                        <p class="card-subtitle">Daftar pelanggan yang sedang dalam proses instalasi</p>
                    </div>
                    <div class="d-flex">
                        <input type="text" class="search-input me-2" placeholder="Cari pelanggan..."
                        id="searchProgress">
                        <div class="dropdown">
                            <button class="btn btn-outline-secondary dropdown-toggle" type="button"
                            id="dropdownMenuButton2" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class='bx bx-filter me-1'></i> Filter
                        </button>
                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton2">
                            <li><a class="dropdown-item" href="#">Semua</a></li>
                            <li><a class="dropdown-item" href="#">Terbaru</a></li>
                            <li><a class="dropdown-item" href="#">Terlama</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-modern" id="progressTable">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="20%">Nama Pelanggan</th>
                            <th width="20%">Alamat</th>
                            <th width="10%">Lokasi</th>
                            <th width="15%">Tanggal</th>
                            <th width="10%">Status</th>
                            <th width="10%">Aksi</th>
                            <th width="10%">Teknisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $progressCount = 0; @endphp
                        @foreach ($data as $key => $item)
                        @if (auth()->user()->id == $item->teknisi_id or auth()->user()->roles->name == 'NOC' or auth()->user()->roles->name == 'Super Admin')
                        @if ($item->status_id == '2')
                        @php $progressCount++; @endphp
                        <tr>
                            <td>{{ $progressCount }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-semibold">{{ $item->nama_customer }}</span>
                                    <small
                                    class="text-muted">{{ $item->no_hp ?? 'No. HP tidak tersedia' }}</small>
                                </div>
                            </td>
                            <td>{{ $item->alamat }}</td>
                            <td>
                                <a href="{{ $item->gps }}" target="_blank"
                                    class="btn btn-action btn-maps" data-bs-toggle="tooltip"
                                    data-bs-placement="top" title="Lihat di Google Maps">
                                    <i class="bx bx-map"></i>
                                </a>
                            </td>
                            <td>{{ $item->created_at->format('d M Y') }}</td>
                            <td>
                                <span class="badge badge-status badge-progress">Proses</span>
                            </td>
                            <td>
                                <div class="d-flex justify-content-between">
                                    @if(auth()->user()->roles->name == 'Teknisi')
                                    <a href="/teknisi/selesai/{{ $item->id }}/print"
                                        class="btn btn-action btn-complete" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Selesaikan Instalasi">
                                        <i class="bx bx-check-circle"></i>
                                    </a>
                                    @elseif(auth()->user()->roles->name == 'NOC')
                                    <a href="/edit/antrian/{{ $item->id }}/noc"
                                        class="btn btn-action btn-complete" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Edit Detail">
                                        <i class="bx bx-pencil"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <span
                                class="badge bg-label-primary">{{ strtoupper($item->teknisi->name) }}</span>
                            </td>
                        </tr>
                        @endif
                        @endif
                        @endforeach
                        
                        @if ($progressCount == 0)
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center">
                                    <i class='bx bx-hard-hat text-secondary mb-2'
                                    style="font-size: 2rem;"></i>
                                    <h6 class="mb-1">Tidak ada instalasi dalam proses</h6>
                                    <p class="text-muted mb-0">Belum ada pelanggan yang sedang diproses</p>
                                </div>
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</div>

<script>
    // Initialize tooltips
    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function(tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
        
        // Simple search functionality
        document.getElementById('searchWaiting').addEventListener('keyup', function() {
            searchTable('waitingTable', this.value);
        });
        
        document.getElementById('searchProgress').addEventListener('keyup', function() {
            searchTable('progressTable', this.value);
        });
        
        function searchTable(tableId, query) {
            var table = document.getElementById(tableId);
            var rows = table.getElementsByTagName('tr');
            
            query = query.toLowerCase();
            
            for (var i = 1; i < rows.length; i++) {
                var found = false;
                var cells = rows[i].getElementsByTagName('td');
                
                for (var j = 0; j < cells.length; j++) {
                    var cellText = cells[j].innerText.toLowerCase();
                    if (cellText.indexOf(query) > -1) {
                        found = true;
                        break;
                    }
                }
                
                if (found) {
                    rows[i].style.display = '';
                } else {
                    rows[i].style.display = 'none';
                }
            }
        }
    });
</script>

@endsection
