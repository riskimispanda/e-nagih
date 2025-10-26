@extends('layouts.contentNavbarLayout')

@section('title', 'Tiket Closed')

@section('content')
    <style>
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .card-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e9ecef;
            padding: 1.5rem;
        }

        .card-title {
            font-weight: 600;
            color: #343a40;
        }

        .card-subtitle {
            color: #6c757d;
        }

        .table-responsive {
            padding: 0;
        }

        .table {
            border-collapse: separate;
            border-spacing: 0;
        }

        .table thead th {
            background-color: #696cff; /* Warna solid pengganti gradien */
            color: white;
            font-weight: 600;
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border: none;
            padding: 1rem 1.25rem;
        }

        .table tbody tr {
            transition: all 0.3s ease;
        }

        .table tbody tr:hover {
            background-color: #f8f9ff;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(105, 108, 255, 0.1);
        }

        .table td {
            padding: 1.25rem;
            vertical-align: middle;
            border-top: 1px solid #f1f3f5;
        }

        .table tbody tr:first-child td {
            border-top: none;
        }

        .customer-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background-color: rgba(105, 108, 255, 0.1);
            color: #696cff;
        }

        .customer-name {
            font-weight: 600;
            color: #343a40;
            margin-bottom: 0.1rem;
        }

        .customer-address {
            font-size: 0.85rem;
            color: #6c757d;
            margin: 0;
        }

        .btn-action {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
            background-color: white;
        }

        .btn-action:hover {
            transform: scale(1.1);
        }

        .btn-action.btn-maps {
            color: #03c3ec;
        }

        .btn-action.btn-maps:hover {
            background-color: #03c3ec;
            color: white;
            border-color: #03c3ec;
        }

        .btn-action.btn-process {
            color: #ffab00;
        }

        .btn-action.btn-process:hover {
            background-color: #ffab00;
            color: white;
            border-color: #ffab00;
        }

        .btn-action.btn-done {
            color: #a0aec0;
            background-color: #f7fafc;
        }

        .search-form {
            max-width: 400px;
        }

        .search-form .input-group-text {
            background-color: transparent;
            border-right: none;
        }

        .search-form .form-control {
            border-left: none;
        }

        .pagination-wrapper {
            padding: 1.5rem;
            background-color: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
    </style>

    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-center">
                        <div>
                            <h4 class="card-title">Tiket Closed</h4>
                            <p class="card-subtitle mb-0">Daftar tiket yang sedang dalam proses atau telah selesai</p>
                        </div>
                        <form class="search-form mt-3 mt-md-0" method="GET" action="{{ url()->current() }}">
                            <div class="input-group input-group-merge">
                                <span class="input-group-text"><i class="bx bx-search"></i></span>
                                <input type="text" class="form-control" name="search" value="{{ $search ?? '' }}"
                                    placeholder="Cari nama atau alamat...">
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-dark">
                                <tr class="fw-bold">
                                    <th scope="col">No</th>
                                    <th>Pelanggan</th>
                                    <th>No HP</th>
                                    <th class="text-center">Lokasi</th>
                                    <th>Keterangan</th>
                                    <th class="text-center">Status</th>
                                    <th>Kategori</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($customer as $item)
                                    <tr>
                                        <td>{{ $customer->firstItem() + $loop->index }}</td>
                                        <td>
                                            <div class="customer-info">
                                                <div>
                                                    <h6 class="customer-name">{{ $item->customer->nama_customer ?? '-' }}
                                                    </h6>
                                                    <p class="customer-address">{{ Str::limit($item->customer->alamat ?? '-', 30) }}</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->customer->no_hp ?? '-' }}</td>
                                        <td class="text-center">
                                            @php
                                                $gps = $item->customer->gps ?? null;
                                                $url = $gps ? (Str::startsWith($gps, ['http://', 'https://']) ? $gps : 'https://www.google.com/maps?q=' . urlencode($gps)) : '#';
                                            @endphp
                                            <a href="{{ $url }}" target="_blank"
                                                class="btn btn-action btn-maps {{ !$gps ? 'disabled' : '' }}"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                title="{{ $gps ? 'Lihat di Google Maps' : 'Lokasi tidak tersedia' }}">
                                                <i class="bx bx-map"></i>
                                            </a>
                                        </td>
                                        <td>{{ Str::limit($item->keterangan, 40) }}</td>
                                        <td class="text-center">
                                            @if ($item->status_id == 6)
                                                <span class="badge bg-label-warning">Menunggu</span>
                                            @elseif($item->status_id == 3)
                                                <span class="badge bg-label-success">Selesai</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-label-danger">
                                                {{ $item->kategori->nama_kategori }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @if ($item->status_id == 3)
                                                <!-- Button Disabled untuk status selesai -->
                                                <button class="btn btn-action btn-done" disabled data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Tiket sudah selesai">
                                                    <i class="bx bx-check-double"></i>
                                                </button>
                                            @else
                                                <!-- Button Aktif untuk status menunggu -->
                                                <a href="/tiket-open/{{ $item->id }}"
                                                    class="btn btn-action btn-process" data-bs-toggle="tooltip"
                                                    data-bs-placement="top" title="Proses & Tutup Tiket">
                                                    <i class="bx bx-wrench"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="bx bx-info-circle fs-1 text-muted mb-3"></i>
                                            <h5 class="text-muted">Tidak ada data tiket</h5>
                                            <p class="text-muted">Tidak ada tiket yang cocok dengan pencarian Anda.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if ($customer->hasPages())
                    <div class="d-flex justify-content-start mt-3">
                        <div class="row">
                            {{ $customer->links() }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('page-script')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const searchInput = document.querySelector('input[name="search"]');
        const searchForm = document.querySelector('.search-form');
        let searchTimeout;

        if (searchInput && searchForm) {
            searchInput.addEventListener('input', function () {
                // Hapus timeout sebelumnya jika ada
                clearTimeout(searchTimeout);

                // Atur timeout baru
                searchTimeout = setTimeout(() => {
                    searchForm.submit();
                }, 500); // Kirim form setelah 500ms tidak ada ketikan baru
            });
        }

        // Inisialisasi tooltip
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });
    });
</script>
@endpush