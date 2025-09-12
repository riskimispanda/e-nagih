@extends('layouts.contentNavbarLayout')

@section('title', 'Tracking Tools')

@section('content')

<div class="row">
    <div class="col-sm-12">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="card-title fw-bold">Tracking Tools</h5>
                <small class="card-subtitle text-muted">Halaman Untuk Tracking Modem Atau Tenda yang sudah Terpakai</small>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-start mb-5">
                    <div class="row">
                        <div class="col-sm-12">
                            <form method="GET" action="/tracking" id="searchForm">
                                <div class="input-group mb-3">
                                    <input
                                        type="text"
                                        id="searchInput"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search..."
                                        value="{{ request('search') }}"
                                        onkeyup="clientSearch()" {{-- filter instan --}}
                                    />
                                    <button class="btn btn-primary" type="submit">Cari</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover" id="dataTable">
                        <thead class="table-dark text-center">
                            <tr class="fw-bold">
                                <th>No</th>
                                <th>Nama Alat</th>
                                <th>Nama Pelanggan</th>
                                <th>Mac Address</th>
                                <th>Seri Perangkat</th>
                                <th>Status</th>
                                <th>Teknisi</th>
                                <th>Tanggal Terpakai</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                            $no = 1;
                            @endphp
                            @forelse ($data as $item)
                            <tr class="text-center">
                                <td>{{$no++}}</td>
                                <td>
                                    <span class="badge bg-label-warning">
                                        {{$item->perangkat->nama_perangkat ?? '-'}}
                                    </span>
                                </td>
                                <td>{{$item->customer->nama_customer ?? '-'}}</td>
                                <td>
                                    <span class="badge bg-label-danger">
                                        {{ $item->mac_address ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-danger">
                                        {{ $item->serial_number ?? '-' }}
                                    </span>
                                </td>
                                <td>
                                    @if($item->status_id == 13)
                                    <span class="badge bg-label-success">
                                        {{$item->status->nama_status ?? '-'}}
                                    </span>
                                    @elseif($item->status_id == 14)
                                    <span class="badge bg-label-warning">
                                        {{$item->status->nama_status ?? '-'}}
                                    </span>
                                    @else
                                    <span class="badge bg-label-danger">
                                        {{$item->status->nama_status ?? '-'}}
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-label-info fw-bold" style="text-transform: uppercase;">
                                        {{$item->customer->teknisi->name ?? '-'}}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-danger fw-bold">
                                        {{ $item->created_at->translatedFormat('F-Y-d') ?? '-' }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center">Data Tidak Ditemukan</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-4">
                    {{ $data->appends(['search' => request('search')])->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Script Javascript --}}
<script>
    function searchFunction() {
        var input, filter, table, tr, td, i, j, txtValue;
        input = document.getElementById("searchInput");
        filter = input.value.toUpperCase();
        table = document.getElementById("dataTable");
        tr = table.getElementsByTagName("tr");
        
        for (i = 1; i < tr.length; i++) { // mulai dari 1 biar header tidak ikut
            let found = false;
            td = tr[i].getElementsByTagName("td");
            for (j = 0; j < td.length; j++) {
                if (td[j]) {
                    txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(filter) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? "" : "none";
        }
    }
</script>
<script>
    function clientSearch() {
        let input = document.getElementById("searchInput").value.toUpperCase();
        let table = document.getElementById("dataTable");
        let tr = table.getElementsByTagName("tr");
    
        for (let i = 1; i < tr.length; i++) { // skip header
            let td = tr[i].getElementsByTagName("td");
            let found = false;
            for (let j = 0; j < td.length; j++) {
                if (td[j]) {
                    let txtValue = td[j].textContent || td[j].innerText;
                    if (txtValue.toUpperCase().indexOf(input) > -1) {
                        found = true;
                        break;
                    }
                }
            }
            tr[i].style.display = found ? "" : "none";
        }
    }
</script>
    
@endsection