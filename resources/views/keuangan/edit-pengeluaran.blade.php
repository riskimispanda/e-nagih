@extends('layouts.contentNavbarLayout')

@section('title', 'Edit Pengeluaran')

@section('content')
<!-- Tailwind CDN -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css">

<style>
  /* Samakan TomSelect dengan input Tailwind */
  .ts-control {
    border-radius: 0.5rem !important;
    border-color: #d1d5db !important;
    padding: 0.4rem 0.75rem !important;
    min-height: 38px !important;
    box-shadow: none !important;
  }
  .ts-control:focus-within {
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 2px rgba(59,130,246,.2) !important;
  }
  .ts-wrapper.single .ts-control, .ts-wrapper.multi .ts-control { background: #fff !important; }
</style>

<div class="space-y-6">
  <!-- Header -->
  <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl shadow-sm p-6 flex items-center gap-4">
    <div class="flex items-center justify-center w-12 h-12 rounded-xl bg-white/20 text-white text-2xl">
      <i class="bx bx-edit"></i>
    </div>
    <div>
      <h5 class="text-lg font-semibold text-white m-0">Edit Pengeluaran</h5>
      <small class="text-sm text-blue-100">Kelola detail pengeluaran {{$pengeluaran->jenis_pengeluaran}}</small>
    </div>
  </div>

  <!-- Form Card -->
  <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-100">
      <h6 class="text-sm font-semibold text-gray-700 m-0">Form Pengeluaran</h6>
    </div>
    <div class="p-6">
      <form action="/update-pengeluaran/{{ $pengeluaran->id }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-x-4 gap-y-5">
          <!-- Jenis Pengeluaran -->
          <div class="lg:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Pengeluaran</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="bx bx-tag"></i>
              </span>
              <input type="text" name="jenis_pengeluaran" value="{{ $pengeluaran->jenis_pengeluaran }}"
                class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm text-gray-700 placeholder-gray-400
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition"
                placeholder="Contoh: Operasional">
            </div>
          </div>

          <!-- Jumlah Pengeluaran -->
          <div class="lg:col-span-3">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jumlah Pengeluaran</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="bx bx-money"></i>
              </span>
              <input type="text" id="jumlah_pengeluaran" value="{{ number_format($pengeluaran->jumlah_pengeluaran, 0, ',', '.') }}"
                class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm text-gray-700 placeholder-gray-400
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition"
                placeholder="Rp 0">
            </div>
            <input type="text" id="rawPengeluaran" name="jumlah_pengeluaran" hidden value="{{ $pengeluaran->jumlah_pengeluaran }}">
          </div>

          <!-- Tanggal -->
          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Tanggal Pengeluaran</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="bx bx-calendar"></i>
              </span>
              <input type="date" name="tanggal" value="{{ $pengeluaran->tanggal_pengeluaran }}"
                class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm text-gray-700
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition">
            </div>
          </div>

          <!-- Jenis Kas -->
          <div class="lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Jenis Kas</label>
            <div class="relative">
              <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                <i class="bx bx-wallet"></i>
              </span>
              <select name="jenis_kas"
                class="w-full rounded-lg border border-gray-300 pl-10 pr-3 py-2 text-sm text-gray-700 bg-white
                       focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition">
                @foreach ($kas as $k)
                  <option value="{{ $k->id }}" 
                    {{ $pengeluaran->kas?->jenis_kas == $k->id ? 'selected' : '' }}>
                    {{ $k->jenis_kas }}
                  </option>
                @endforeach
              </select>
            </div>
          </div>

          <!-- RAB -->
          <div class="sm:col-span-2 lg:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">RAB</label>
            <select name="rab_id" id="rab_id" class="w-full">
              <option value="">-</option>
              @foreach ($data as $k) {{-- Pastikan $data berisi RAB yang sesuai dengan $pengeluaran->rab_id --}}
                @php
                  $bulan = $k->bulan ? \Carbon\Carbon::create()->month((int) $k->bulan)->locale('id')->translatedFormat('F') : '';
                @endphp
                <option value="{{ $k->id }}" {{ $pengeluaran->rab_id == $k->id ? 'selected' : '' }}>
                  {{ $k->kegiatan }} 
                  {{ $k->tahun_anggaran ? "| Tahun: {$k->tahun_anggaran}" : '' }}
                  {{ $bulan ? '| Bulan: ' . $bulan : '' }}
                </option>
              @endforeach
            </select>
          </div>

          <!-- Keterangan -->
          <div class="sm:col-span-2 lg:col-span-6">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Keterangan</label>
            <textarea name="keterangan" rows="4"
              class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm text-gray-700 placeholder-gray-400
                     focus:border-blue-500 focus:ring-2 focus:ring-blue-200 focus:outline-none transition"
              placeholder="Masukkan keterangan pengeluaran...">{{$pengeluaran->keterangan}}</textarea>
          </div>

          <!-- Bukti Pengeluaran -->
          <div class="sm:col-span-2 lg:col-span-6">
            <label class="block text-sm font-medium text-gray-700 mb-1.5">Bukti Pengeluaran</label>

            @if ($pengeluaran->bukti_pengeluaran)
              @php
                $extBukti = strtolower(pathinfo($pengeluaran->bukti_pengeluaran, PATHINFO_EXTENSION));
                $isImage = in_array($extBukti, ['jpg', 'jpeg', 'png']);
              @endphp
              <div class="flex flex-wrap items-center gap-4 rounded-lg border border-gray-200 p-3 mb-3">
                @if ($isImage)
                  <img src="{{ asset('uploads/'.basename($pengeluaran->bukti_pengeluaran)) }}" alt="Bukti"
                    class="h-28 w-28 object-cover rounded-lg border border-gray-200">
                @else
                  <a href="{{ asset('uploads/'.basename($pengeluaran->bukti_pengeluaran)) }}" target="_blank"
                    class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium">
                    <i class="bx bx-file"></i> Lihat PDF
                  </a>
                @endif
                <div class="text-sm">
                  <p class="text-gray-500 m-0">Bukti saat ini:</p>
                  <p class="text-gray-700 font-medium m-0">{{ $pengeluaran->bukti_pengeluaran }}</p>
                </div>
              </div>
            @endif

            <input type="file" name="buktiPengeluaran" id="buktiPengeluaran" accept=".jpg,.jpeg,.png,.pdf"
              class="block w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-600 hover:file:bg-blue-100 transition cursor-pointer">
            <p class="mt-1 text-xs text-gray-400">Format: JPG, PNG, PDF. Maksimal 2MB. Kosongkan jika tidak diubah.</p>

            <div id="previewBukti" class="mt-3 hidden">
              <p class="text-xs text-gray-400 mb-1">Pratinjau baru:</p>
              <img id="previewBuktiImg" src="#" alt="Pratinjau" class="h-32 w-32 object-cover rounded-lg border border-gray-200 hidden">
              <a id="previewBuktiPdf" href="#" target="_blank" class="hidden inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium">
                <i class="bx bx-file"></i> Lihat PDF
              </a>
            </div>
          </div>
        </div>

        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 mt-6">
          <a href="/pengeluaran/global" class="inline-flex items-center justify-center gap-1 px-4 py-2 text-sm font-medium rounded-lg border border-gray-200 text-gray-600 hover:bg-gray-50 transition">
            <i class="bx bx-chevrons-left"></i>Kembali
          </a>
          <button type="button" id="btnUpdate" class="inline-flex items-center justify-center gap-1 px-4 py-2 text-sm font-medium rounded-lg bg-blue-500 hover:bg-blue-600 text-white transition">
            <i class="bx bx-file"></i>Update
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Preview + kompres bukti --}}
<script>
  (function () {
    var input = document.getElementById('buktiPengeluaran');
    if (!input) return;
    var wrap = document.getElementById('previewBukti');
    var img = document.getElementById('previewBuktiImg');
    var pdf = document.getElementById('previewBuktiPdf');

    input.addEventListener('change', function (e) {
      var file = e.target.files && e.target.files[0];
      if (!file) { wrap.classList.add('hidden'); return; }

      var type = file.type;
      var isPdf = (type === 'application/pdf') || /\.pdf$/i.test(file.name);

      if (isPdf) {
        img.classList.add('hidden');
        pdf.classList.remove('hidden');
        pdf.setAttribute('href', URL.createObjectURL(file));
      } else {
        pdf.classList.add('hidden');
        img.classList.remove('hidden');
        img.setAttribute('src', URL.createObjectURL(file));
      }
      wrap.classList.remove('hidden');

      // Kompres gambar agar tetap di bawah batas upload server
      if (!type.startsWith('image/') || type === 'image/heic' || type === 'image/heif') return;

      var reader = new FileReader();
      reader.onload = function (ev) {
        var image = new Image();
        image.onload = function () {
          var maxDim = 1280, w = image.width, h = image.height;
          if (w > h && w > maxDim) { h = Math.round(h * maxDim / w); w = maxDim; }
          else if (h > maxDim) { w = Math.round(w * maxDim / h); h = maxDim; }

          var canvas = document.createElement('canvas');
          canvas.width = w; canvas.height = h;
          canvas.getContext('2d').drawImage(image, 0, 0, w, h);
          canvas.toBlob(function (blob) {
            if (!blob) return;
            var dt = new DataTransfer();
            dt.items.add(new File([blob], file.name, { type: blob.type }));
            input.files = dt.files;
          }, 'image/jpeg', 0.7);
        };
        image.src = ev.target.result;
      };
      reader.readAsDataURL(file);
    });
  })();
</script>

{{-- Js --}}
<script>
    document.getElementById('jumlah_pengeluaran').addEventListener('keyup', function(e) {
        let value = this.value.replace(/\D/g, ''); // hapus semua non-angka
        value = new Intl.NumberFormat('id-ID').format(value); // format ke rupiah
        this.value = value;
    });
</script>
<script>
    document.getElementById('jumlah_pengeluaran').addEventListener('keyup', function () {
        // Ambil angka murni tanpa titik atau koma
        let raw = this.value.replace(/\D/g, '');
        
        // Update hidden input dengan angka murni
        document.getElementById('rawPengeluaran').value = raw;
        
        // Format kembali tampilan input jumlah_anggaran
        if (raw) {
            this.value = new Intl.NumberFormat('id-ID').format(raw);
        } else {
            this.value = '';
        }
    });
</script>
<script>
    document.getElementById('btnUpdate').addEventListener('click', function () {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Data Pengeluaran akan diperbarui!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, Update!',
            cancelButtonText: 'Batal',
            topLayer: true
        }).then((result) => {
            if (result.isConfirmed) {
                document.querySelector('form').submit();
            }
        });
    });
    new TomSelect('#rab_id',{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });
</script>
@endsection
