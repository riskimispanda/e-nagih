<table class="table table-hover" style="font-size: 14px;">
    <thead class="table-dark">
        <tr class="text-center">
            <th>No</th>
            <th>Bulan</th>
            <th>Tahun</th>
            <th>Kegiatan</th>
            <th>Jumlah Anggaran</th>
            <th>Anggaran Terealisasi</th>
            <th>Sisa Anggaran</th>
            <th>Status</th>
            <th>Admin</th>
            <th>Aksi</th>
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
                {{ \Carbon\Carbon::createFromFormat('m', $item->bulan)->translatedFormat('F') }}
            </td>
            <td>{{ $item->tahun_anggaran }}</td>
            <td>{{ $item->kegiatan }}</td>
            <td>
                Rp. {{ number_format($item->jumlah_anggaran, 0, ',', '.') }}
            </td>
            <td>
                Rp. {{ number_format($item->pengeluaran->sum('jumlah_pengeluaran'), 0, ',', '.') }}
            </td>
            <td>
                Rp. {{ number_format($item->jumlah_anggaran - $item->pengeluaran->sum('jumlah_pengeluaran'), 0, ',', '.') }}
            </td>
            <td>
                @if($item->status_id == 11)
                <span class="badge bg-success bg-opacity-10 text-success">{{$item->status->nama_status}}</span>
                @elseif($item->status_id == 12)
                <span class="badge bg-danger bg-opacity-10 text-danger">{{$item->status->nama_status}}</span>
                @elseif($item->status_id == 1)
                <span class="badge bg-warning bg-opacity-10 text-warning">{{$item->status->nama_status}}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-danger bg-opacity-10 text-danger">
                    {{ $item->usr->name }}
                </span>
            </td>
            <td>
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-info btn-sm btn-detail" 
                    data-id="{{ $item->id }}" 
                    data-bs-toggle="tooltip" 
                    data-bs-placement="bottom" 
                    title="Detail RAB">
                    <i class="bx bx-show"></i>
                </button>
                <a href="/edit-rab/{{ $item->id }}" data-bs-toggle="tooltip" title="Edit RAB" data-bs-placement="bottom">
                    <button class="btn btn-warning btn-sm">
                        <i class="bx bx-pencil"></i>
                    </button>
                </a>
                <button type="button" class="btn btn-danger btn-sm btnDelete" 
                data-url="/delete-rab/{{ $item->id }}" 
                data-bs-toggle="tooltip" title="Hapus RAB" data-bs-placement="bottom">
                <i class="bx bx-trash"></i>
            </button>
        </div>
    </td>
</tr>
@empty
<tr>
    <td colspan="10" class="text-center fw-bold">Tidak ada data</td>
</tr>
@endforelse
</tbody>
</table>

<script>
    document.querySelectorAll('.btnDelete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            let url = this.getAttribute('data-url');
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: "Data RAB akan dihapus permanen!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                topLayer: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = url;
                }
            });
        });
    });
</script>