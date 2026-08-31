{{--
    Partial: partials/allocation-row.blade.php
    Static read-only row — used in view.blade.php
    Variables: $line (object), $index (int)
--}}
<tr>
    <td>{{ $index }}</td>
    <td>{{ $line->major_component_name ?? '—' }}</td>
    <td>{{ $line->sub_component_name ?? '—' }}</td>
    <td>{{ number_format((float)($line->amount ?? 0), 2) }}</td>
</tr>
