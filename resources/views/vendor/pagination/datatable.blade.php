@if ($paginator->hasPages())
        <div class="datatable-pagination-wrapper">
                {{-- Info Text --}}
                <div class="datatable-info">
                        <span class="text-muted">
                                Menampilkan
                                <strong>{{ $paginator->firstItem() ?? 0 }}</strong>
                                sampai
                                <strong>{{ $paginator->lastItem() ?? 0 }}</strong>
                                dari
                                <strong>{{ $paginator->total() }}</strong>
                                data
                        </span>
                </div>

                {{-- Pagination Controls --}}
                <nav aria-label="Pagination">
                        <ul class="datatable-pagination">
                                {{-- Previous Button --}}
                                @if ($paginator->onFirstPage())
                                        <li class="page-item disabled" aria-disabled="true">
                                                <span class="page-link">
                                                        <i class='bx bx-chevron-left'></i>
                                                </span>
                                        </li>
                                @else
                                        <li class="page-item">
                                                <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                                                        <i class='bx bx-chevron-left'></i>
                                                </a>
                                        </li>
                                @endif

                                {{-- Pagination Elements --}}
                                @foreach ($elements as $element)
                                        {{-- "Three Dots" Separator --}}
                                        @if (is_string($element))
                                                <li class="page-item disabled" aria-disabled="true">
                                                        <span class="page-link">{{ $element }}</span>
                                                </li>
                                        @endif

                                        {{-- Array Of Links --}}
                                        @if (is_array($element))
                                                @foreach ($element as $page => $url)
                                                        @if ($page == $paginator->currentPage())
                                                                <li class="page-item active" aria-current="page">
                                                                        <span class="page-link">{{ $page }}</span>
                                                                </li>
                                                        @else
                                                                <li class="page-item">
                                                                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                                                                </li>
                                                        @endif
                                                @endforeach
                                        @endif
                                @endforeach

                                {{-- Next Button --}}
                                @if ($paginator->hasMorePages())
                                        <li class="page-item">
                                                <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                                                        <i class='bx bx-chevron-right'></i>
                                                </a>
                                        </li>
                                @else
                                        <li class="page-item disabled" aria-disabled="true">
                                                <span class="page-link">
                                                        <i class='bx bx-chevron-right'></i>
                                                </span>
                                        </li>
                                @endif
                        </ul>
                </nav>
        </div>
@endif
