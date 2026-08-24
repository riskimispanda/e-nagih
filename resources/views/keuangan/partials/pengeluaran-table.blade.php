@forelse ($pengeluarans as $key => $pengeluaran)
  <tr class="hover:bg-gray-50">
    <td class="px-4 py-3 text-center text-gray-600">{{ $pengeluarans->firstItem() + $key }}</td>
    <td class="px-4 py-3 text-center">
      <span class="inline-block px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-medium">
        {{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}
      </span>
    </td>
    <td class="px-4 py-3 text-center text-gray-700">{{ $pengeluaran->jenis_pengeluaran }}</td>
    <td class="px-4 py-3 text-center text-gray-700">{{ $pengeluaran->keterangan }}</td>
    <td class="px-4 py-3 text-center" data-amount="{{ $pengeluaran->jumlah_pengeluaran }}">
      <span class="inline-block px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-medium">
        Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}
      </span>
    </td>
    <td class="px-4 py-3 text-center">
      <span class="inline-block px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-medium">
        {{ $pengeluaran->kas->jenis_kas ?? '-'}}
      </span>
    </td>
    <td class="px-4 py-3 text-center">
      @if ($pengeluaran->status_id == 1)
        <span class="inline-block px-2.5 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-medium">Menunggu Konfirmasi Penghapusan</span>
      @elseif ($pengeluaran->status_id == 2)
        <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">Approved</span>
      @elseif ($pengeluaran->status_id == 3)
        <span class="inline-block px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-medium">Berhasil</span>
      @endif
    </td>
    <td class="px-4 py-3 text-center">
      <span class="inline-block px-2.5 py-1 rounded-full bg-red-50 text-red-700 text-xs font-medium">
        {{ $pengeluaran->user->name }}
      </span>
    </td>
    <td class="px-4 py-3">
      <div class="flex items-center justify-center gap-2">
        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-sky-600 hover:bg-sky-50 transition" data-bs-toggle="modal"
          data-bs-target="#detailPengeluaranModal" title="Detail" data-bs-placement="bottom"
          data-tanggal="{{ \Carbon\Carbon::parse($pengeluaran->tanggal_pengeluaran)->format('d-M-Y') }}"
          data-jenis="{{ $pengeluaran->jenis_pengeluaran }}"
          data-keterangan="{{ $pengeluaran->keterangan }}"
          data-jumlah="Rp {{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}"
          data-kas="{{ $pengeluaran->kas->jenis_kas ?? '-' }}"
          data-metode="{{ $pengeluaran->metode_bayar }}"
          data-status="{{ $pengeluaran->status_id == 1 ? 'Menunggu Konfirmasi Penghapusan' : ($pengeluaran->status_id == 2 ? 'Approved' : 'Berhasil') }}"
          data-admin="{{ $pengeluaran->user->name }}"
          data-rab="{{ $pengeluaran->rab->kegiatan ?? '-' }}"
          data-bukti="{{ $pengeluaran->bukti_pengeluaran ? asset('uploads/'.basename($pengeluaran->bukti_pengeluaran)) : '' }}"
          data-bukti-ext="{{ $pengeluaran->bukti_pengeluaran ? strtolower(pathinfo($pengeluaran->bukti_pengeluaran, PATHINFO_EXTENSION)) : '' }}">
          <i class="bx bx-show"></i>
        </button>
        <a href="/edit-pengeluaran/{{ $pengeluaran->id }}">
          <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-amber-600 hover:bg-amber-50 transition" title="Edit" data-bs-toggle="tooltip" data-bs-placement="bottom">
            <i class="bx bx-edit"></i>
          </button>
        </a>
        <button class="inline-flex items-center justify-center w-8 h-8 rounded-lg text-red-600 hover:bg-red-50 transition" data-bs-toggle="modal"
          data-bs-target="#deletePengeluaranModal" data-id="{{ $pengeluaran->id }}" title="Hapus"
          data-bs-placement="bottom">
          <i class="bx bx-trash"></i>
        </button>
      </div>
    </td>
  </tr>
@empty
  <tr>
    <td colspan="9" class="px-4 py-12 text-center">
      <div class="flex flex-col items-center">
        <i class="bx bx-receipt text-gray-300" style="font-size: 3rem;"></i>
        <h5 class="text-gray-700 mt-3 mb-2 m-0">Tidak ada data</h5>
        <p class="text-gray-400 m-0">Belum ada Transaksi</p>
      </div>
    </td>
  </tr>
@endforelse
