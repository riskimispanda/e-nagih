{{-- Pagination ringkas: tanpa teks "Showing ... results" (info sudah ada di header card) --}}
@if ($paginator->hasPages())
  <nav class="d-flex justify-content-center justify-content-sm-end" aria-label="Navigasi halaman">
    {{-- Tampilan mobile: Prev / Next saja --}}
    <ul class="pagination d-flex d-sm-none mb-0">
      @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link"><i class="bx bx-chevron-left"></i> Sebelumnya</span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev"><i class="bx bx-chevron-left"></i>
            Sebelumnya</a>
        </li>
      @endif

      @if ($paginator->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Berikutnya <i
              class="bx bx-chevron-right"></i></a>
        </li>
      @else
        <li class="page-item disabled" aria-disabled="true">
          <span class="page-link">Berikutnya <i class="bx bx-chevron-right"></i></span>
        </li>
      @endif
    </ul>

    {{-- Tampilan desktop: nomor halaman --}}
    <ul class="pagination d-none d-sm-flex mb-0">
      @if ($paginator->onFirstPage())
        <li class="page-item disabled" aria-disabled="true" aria-label="Halaman sebelumnya">
          <span class="page-link" aria-hidden="true"><i class="bx bx-chevron-left"></i></span>
        </li>
      @else
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="Halaman sebelumnya"><i
              class="bx bx-chevron-left"></i></a>
        </li>
      @endif

      @foreach ($elements as $element)
        @if (is_string($element))
          <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
        @endif

        @if (is_array($element))
          @foreach ($element as $page => $url)
            @if ($page == $paginator->currentPage())
              <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
            @else
              <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
            @endif
          @endforeach
        @endif
      @endforeach

      @if ($paginator->hasMorePages())
        <li class="page-item">
          <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="Halaman berikutnya"><i
              class="bx bx-chevron-right"></i></a>
        </li>
      @else
        <li class="page-item disabled" aria-disabled="true" aria-label="Halaman berikutnya">
          <span class="page-link" aria-hidden="true"><i class="bx bx-chevron-right"></i></span>
        </li>
      @endif
    </ul>
  </nav>
@endif
