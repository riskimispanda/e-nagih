<table class="table table-hover" style="font-size: 14px;">
    <thead class="table-dark">
        <tr class="text-center">
            <th>No</th>
            <th>Bulan</th>
            <th>Tahun</th>
            <th>Kegiatan</th>
            <th>Jumlah Anggaran</th>
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
            <td>Rp. {{ number_format($item->jumlah_anggaran, 0, ',', '.') }}</td>
            <td>
                @if($item->status_id == 11)
                    <span class="badge bg-success bg-opacity-10 text-success">{{$item->status->nama_status}}</span>
                @elseif($item->status_id == 12)
                    <span class="badge bg-warning bg-opacity-10 text-dark">{{$item->status->nama_status}}</span>
                @endif
            </td>
            <td>
                <span class="badge bg-danger bg-opacity-10 text-danger">
                    {{ $item->usr->name }}
                </span>
            </td>
            <td>
                <a href="" class="btn btn-info btn-sm" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Detail Kegiatan">
                    <i class="bx bx-info-circle"></i>
                </a>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="8" class="text-center fw-bold">Tidak ada data</td>
        </tr>
        @endforelse
    </tbody>
</table>