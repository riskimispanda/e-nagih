@extends('layouts.contentNavbarLayout')

@section('title', 'Manajemen Barang Rusak - Super Admin')

@section('content')
<!-- Enhanced Statistics Cards -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-danger shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                            Total Barang Rusak</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $damagedItems->count() }}
                        </div>
                        <div class="text-xs text-muted">
                            {{ $damagedItems->count() }} items marked as damaged
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Siap Diperbaiki</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $categories->filter(function ($cat) { return $cat->damagedCount > 0; })->count() }}
                        </div>
                        <div class="text-xs text-muted">
                            @if($categories->filter(function ($cat) { return $cat->damagedCount > 0; })->count() > 0)
                                {{ $categories->filter(function ($cat) { return $cat->damagedCount > 0; })->first()->damagedCount }} dari total items
                            @endif
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-wrench fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                            Kategori Dengan Barang Rusak</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $categories->filter(function ($cat) { return $cat->damagedCount > 0; })->count() }}
                        </div>
                        <div class="text-xs text-muted">
                            Dari {{ $categories->count() }} total kategori
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tags fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                            Terbaru Ditambahkan</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">
                            {{ $recentlyDamaged->count() }}
                        </div>
                        <div class="text-xs text-muted">
                            5 items terbaru
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-clock fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Tools -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-danger btn-sm w-100" 
                                onclick="bulkRepairAction()"
                                {{ count($categories->filter(function ($cat) { return $cat->damagedCount > 0; })) == 0 ? 'disabled' : '' }}>
                            <i class="fas fa-wrench mr-1"></i> Perbaiki Semua yang Dipilih
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-warning btn-sm w-100" 
                                onclick="exportDamagedItems()">
                            <i class="fas fa-download mr-1"></i> Export Data
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-info btn-sm w-100" 
                                onclick="viewAnalytics()">
                            <i class="fas fa-chart-bar mr-1"></i> Lihat Analytics
                        </button>
                    </div>
                    <div class="col-md-3 mb-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm w-100" 
                                onclick="refreshData()">
                            <i class="fas fa-sync-alt mr-1"></i> Refresh Data
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Statistics Cards -->
<div class="row mb-4">
    @foreach($categories->filter(function ($cat) { return $cat->damagedCount > 0; }) as $category)
        <div class="col-md-6 col-lg-3 mb-4">
            <div class="card border-left-{{ $category->damagedCount > 10 ? 'danger' : ($category->damagedCount > 5 ? 'warning' : 'success') }} shadow h-100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-{{ $category->damagedCount > 10 ? 'danger' : ($category->damagedCount > 5 ? 'warning' : 'success') }} text-uppercase mb-1">
                                {{ $category->kategori }}</div>
                            <div class="h4 mb-0 font-weight-bold text-gray-800">
                                {{ $category->damagedCount }}
                            </div>
                            <div class="text-xs text-muted">
                                {{ $category->percentage }}% dari total kerusakan
                            </div>
                        </div>
                        <div class="col-auto">
                            @if($category->damagedCount > 10)
                                <i class="fas fa-exclamation-triangle fa-2x text-danger"></i>
                            @elseif($category->damagedCount > 5)
                                <i class="fas fa-exclamation-circle fa-2x text-warning"></i>
                            @else
                                <i class="fas fa-check-circle fa-2x text-success"></i>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Recently Damaged Items -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">
                    <i class="fas fa-clock mr-1"></i> Terbaru Ditandai sebagai Rusak
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($recentlyDamaged as $device)
                        <div class="col-md-6 col-lg-4 mb-3">
                            <div class="card border-left-danger shadow-sm h-100">
                                <div class="card-body py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="card-title mb-1">{{ $device->perangkat->nama_perangkat ?? 'Unknown' }}</h6>
                                            <p class="card-text small mb-0">
                                                <strong>Kategori:</strong> {{ $device->perangkat->kategori->nama_logistik ?? 'Unknown' }}<br>
                                                <strong>SN:</strong> {{ $device->serial_number ?? 'N/A' }}<br>
                                                <strong>MAC:</strong> {{ $device->mac_address ?? 'N/A' }}<br>
                                                <strong>Ditambahkan:</strong> {{ $device->created_at->diffForHumans() }}
                                            </p>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge bg-label-danger mb-1">Rusak</span>
                                            <div class="small text-muted">
                                                <i class="fas fa-clock mr-1"></i> 
                                                {{ $device->created_at->diffForHumans() }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Data Table -->
<div class="row">
    <div class="col-12">
        <div class="card shadow">
            <div class="card-header py-3 d-flex justify-content-between align-items-center">
                <h6 class="m-0 font-weight-bold text-primary">Daftar Lengkap Barang Rusak</h6>
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" id="damagedSearchInput" 
                           placeholder="Cari device...">
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover" id="damagedItemsTable" width="100%">
                        <thead class="table-light">
                            <tr>
                                <th width="5%">
                                    <input type="checkbox" id="selectAllItems" class="form-check-input">
                                </th>
                                <th width="10%">No.</th>
                                <th width="15%">Kategori</th>
                                <th width="20%">Nama Perangkat</th>
                                <th width="15%">MAC Address</th>
                                <th width="15%">Serial Number</th>
                                <th width="10%">Status</th>
                                <th width="10%">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $counter = 1;
                            @endphp
                            @foreach($damagedItems as $item)
                            <tr class="damaged-item-row">
                                <td>
                                    <input type="checkbox" class="item-checkbox form-check-input" value="{{ $item->id }}">
                                </td>
                                <td>{{ $counter++ }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $item->perangkat->kategori->nama_logistik == 'Modem' ? 'primary' : ($item->perangkat->kategori->nama_logistik == 'Tenda' ? 'success' : 'secondary') }}">
                                        {{ $item->perangkat->kategori->nama_logistik ?? 'N/A' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class='bx bx-chip text-muted me-1'></i>
                                        <span class="fw-medium">{{ $item->perangkat->nama_perangkat ?? 'N/A' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-monospace">{{ $item->mac_address ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="font-monospace">{{ $item->serial_number ?? 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-label-danger">Rusak</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-success btn-xs" 
                                                onclick="repairDamaged({{ $item->id }})"
                                                title="Perbaiki">
                                            <i class="bx bx-wrench"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-danger btn-xs" 
                                                onclick="confirmDeleteDamaged({{ $item->id }})"
                                                title="Hapus">
                                            <i class="bx bx-trash"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-info btn-xs" 
                                                onclick="viewDamagedDetails({{ $item->id }})"
                                                title="Detail">
                                            <i class="bx bx-info-circle"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    {{ $damagedItems->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Action Modals -->
<div id="repairModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Perbaiki Barang Rusak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menandai perangkat ini sebagai sudah diperbaiki?</p>
                <div class="alert alert-info">
                    <small><i class="bx bx-info-circle"></i> Stok akan ditambahkan kembali ke inventaris perangkat.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success" onclick="confirmRepairDamaged()">Konfirmasi Perbaikan</button>
            </div>
        </div>
    </div>
</div>

<div id="deleteModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Hapus Barang Rusak</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus perangkat rusak ini? Ini akan mengembalikan stok dan menghapus catatan.</p>
                <div class="alert alert-warning">
                    <small><i class="bx bx-error"></i> Tindakan ini tidak dapat dibatalkan.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-danger" onclick="confirmDeleteDamaged()">Hapus</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Global variables
let currentDamagedId = null;
let selectedDamagedItems = [];

// Select all functionality
document.addEventListener('DOMContentLoaded', function() {
    const selectAllInput = document.getElementById('selectAllItems');
    selectAllInput.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.damaged-item-row .item-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
            if (this.checked) {
                selectedDamagedItems.push(parseInt(checkbox.value));
            } else {
                selectedDamagedItems = selectedDamagedItems.filter(id => id !== parseInt(checkbox.value));
            }
        });
    });

    // Individual checkbox listeners
    document.querySelectorAll('.damaged-item-row .item-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            if (this.checked) {
                selectedDamagedItems.push(parseInt(this.value));
            } else {
                selectedDamagedItems = selectedDamagedItems.filter(id => id !== parseInt(this.value));
            }
            
            selectAllInput.checked = selectedDamagedItems.length === document.querySelectorAll('.damaged-item-row .item-checkbox').length;
        });
    });

    // Search functionality
    const searchInput = document.getElementById('damagedSearchInput');
    searchInput.addEventListener('keyup', function() {
        const searchTerm = this.value.toLowerCase();
        const rows = document.querySelectorAll('.damaged-item-row');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            if (searchTerm === '' || text.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });
});

// Repair functionality
function repairDamaged(id) {
    currentDamagedId = id;
    const modal = new bootstrap.Modal(document.getElementById('repairModal'));
    modal.show();
}

function confirmRepairDamaged() {
    if (currentDamagedId) {
        fetch(`/damaged-items/repair/${currentDamagedId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal memperbaiki item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('repairModal'));
        modal.hide();
    }
}

// Bulk repair functionality
function bulkRepairAction() {
    if (selectedDamagedItems.length === 0) {
        alert('Pilih setidaknya satu perangkat rusak');
        return;
    }
    
    if (confirm(`Perbaiki ${selectedDamagedItems.length} perangkat rusak?`)) {
        fetch('/damaged-items/bulk-repair', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                ids: selectedDamagedItems
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal memperbaiki item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
    }
}

// Export functionality
function exportDamagedItems() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'true');
    window.location.href = `/damaged-items/export?${params.toString()}`;
}

// View analytics
function viewAnalytics() {
    window.location.href = '/damaged-items/analytics';
}

// Refresh data
function refreshData() {
    location.reload();
}

// Utility functions
function confirmDeleteDamaged() {
    if (currentDamagedId) {
        fetch(`/damaged-items/permanent-delete/${currentDamagedId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Gagal menghapus item');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan');
        });
        
        const modal = bootstrap.Modal.getInstance(document.getElementById('deleteModal'));
        modal.hide();
    }
}

function viewDamagedDetails(id) {
    // Implement detailed view modal
    alert('Fitur detail view implementation needed');
}
</script>
@endpush
@endsection
