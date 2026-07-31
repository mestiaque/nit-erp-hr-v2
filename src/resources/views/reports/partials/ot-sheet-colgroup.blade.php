<colgroup>
    @foreach($columns as $key => $col)
        @continue(!$col['show'])
        @if($key === 'date_wise_ot')
            @foreach($dates as $d)
                <col style="width:{{ $dayColWidth }}%;">
            @endforeach
        @else
            <col style="width:{{ $col['width'] }}%;">
        @endif
    @endforeach
</colgroup>
