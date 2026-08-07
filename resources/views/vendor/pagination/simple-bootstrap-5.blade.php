@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center mt-3 mb-2">
        <ul class="pagination pagination-sm m-0 gap-2 w-100 justify-content-between">
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
    </nav>
@endif
