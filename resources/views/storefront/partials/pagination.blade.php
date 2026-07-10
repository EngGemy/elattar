@if ($paginator->hasPages())
    @if ($paginator->onFirstPage())
        <span class="disabled">« السابق</span>
    @else
        <a href="{{ $paginator->previousPageUrl() }}">« السابق</a>
    @endif

    @foreach ($elements as $element)
        @if (is_string($element))
            <span>{{ $element }}</span>
        @endif
        @if (is_array($element))
            @foreach ($element as $page => $url)
                @if ($page == $paginator->currentPage())
                    <span class="active"><span>{{ $page }}</span></span>
                @else
                    <a href="{{ $url }}">{{ $page }}</a>
                @endif
            @endforeach
        @endif
    @endforeach

    @if ($paginator->hasMorePages())
        <a href="{{ $paginator->nextPageUrl() }}">التالي »</a>
    @else
        <span class="disabled">التالي »</span>
    @endif
@endif
