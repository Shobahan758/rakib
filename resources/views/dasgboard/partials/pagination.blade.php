@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Pagination">
        <p class="pagination-summary">Showing {{ $paginator->firstItem() }}–{{ $paginator->lastItem() }} of {{ $paginator->total() }}</p>
        <div class="pagination-links">
            @if ($paginator->onFirstPage())
                <span class="pagination-link disabled" aria-disabled="true" title="Previous page"><i class="fa-solid fa-chevron-left"></i></span>
            @else
                <a class="pagination-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" title="Previous page"><i class="fa-solid fa-chevron-left"></i></a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="pagination-link disabled" aria-hidden="true">{{ $element }}</span>
                @endif
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="pagination-link active" aria-current="page">{{ $page }}</span>
                        @else
                            <a class="pagination-link" href="{{ $url }}">{{ $page }}</a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a class="pagination-link" href="{{ $paginator->nextPageUrl() }}" rel="next" title="Next page"><i class="fa-solid fa-chevron-right"></i></a>
            @else
                <span class="pagination-link disabled" aria-disabled="true" title="Next page"><i class="fa-solid fa-chevron-right"></i></span>
            @endif
        </div>
    </nav>
@endif
