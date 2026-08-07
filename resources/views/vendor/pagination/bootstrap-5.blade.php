@if ($paginator->hasPages())
    <nav class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 mt-3 mb-2">
        {{-- Mobile & Desktop Results Counter --}}
        <div class="small text-muted text-center text-sm-start">
            Menampilkan <span class="fw-bold text-dark">{{ $paginator->firstItem() ?? 0 }}</span> - <span class="fw-bold text-dark">{{ $paginator->lastItem() ?? 0 }}</span> dari <span class="fw-bold text-dark">{{ $paginator->total() }}</span> data
        </div>

        <div>
            <ul class="pagination pagination-sm m-0 gap-1 flex-wrap justify-content-center">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link px-3 py-2 border-0 shadow-xs" style="border-radius: var(--radius-sm); font-weight: 600;">
                            <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                        </span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link px-3 py-2 border-0 shadow-xs" href="{{ $paginator->previousPageUrl() }}" rel="prev" style="border-radius: var(--radius-sm); font-weight: 600;">
                            <i class="fas fa-chevron-left me-1"></i> Sebelumnya
                        </a>
                    </li>
                @endif

                {{-- Pagination Elements (Desktop & Tablet) --}}
                <div class="d-none d-md-flex gap-1">
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link border-0">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page">
                                        <span class="page-link border-0 fw-bold px-3 py-2" style="background: var(--primary); color: #FFFFFF; border-radius: var(--radius-sm);">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link border-0 px-3 py-2" href="{{ $url }}" style="border-radius: var(--radius-sm); font-weight: 600;">{{ $page }}</a>
                                    </li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach
                </div>

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link px-3 py-2 border-0 shadow-xs" href="{{ $paginator->nextPageUrl() }}" rel="next" style="border-radius: var(--radius-sm); font-weight: 600;">
                            Berikutnya <i class="fas fa-chevron-right ms-1"></i>
                        </a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link px-3 py-2 border-0 shadow-xs" style="border-radius: var(--radius-sm); font-weight: 600;">
                            Berikutnya <i class="fas fa-chevron-right ms-1"></i>
                        </span>
                    </li>
                @endif
            </ul>
        </div>
    </nav>
@endif
