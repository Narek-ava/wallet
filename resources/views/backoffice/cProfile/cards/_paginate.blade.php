@if($count > 10)
    @php( $pageCount = floor($count/10))
    <nav>
        <ul class="pagination">
            @if(request()->get('from_record', 0) < 10)
                <li class="page-item disabled" aria-disabled="true" aria-label="« Previous">
                    <span class="page-link" aria-hidden="true">‹</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link"
                       href="{{ route('backoffice.wallester.card.details',$cardId) }}?from_record={{ request()->get('from_record', 0) -10 }}"
                       rel="prev" aria-label="« Previous">‹</a>
                </li>
            @endif
            @for($i=0; $i <= $pageCount; $i++)
                @if(request()->get('from_record', 0) == ($i * 10))
                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $i +1 }}</span></li>
                @else
                    <li class="page-item"><a class="page-link"
                                             href="{{ route('backoffice.wallester.card.details',$cardId) }}?from_record={{ $i * 10 }}">{{ $i +1 }}</a>
                    </li>
                @endif
            @endfor
                @if((request()->get('from_record', 0) + 10) > $count)
                    <li class="page-item disabled" aria-disabled="true" aria-label="Next »">
                        <span class="page-link" aria-hidden="true">›</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link"
                           href="{{ route('backoffice.wallester.card.details',$cardId) }}?from_record={{ request()->get('from_record', 0) + 10 }}"
                           rel="next"
                           aria-label="Next »">›</a>
                    </li>
                @endif
        </ul>
    </nav>
@endif
