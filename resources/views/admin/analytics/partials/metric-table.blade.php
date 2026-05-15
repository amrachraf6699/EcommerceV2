<article class="analytics-panel rounded-[2rem] border border-white/10 bg-white/5 p-4 sm:p-6">
    <h3 class="text-lg font-bold text-white">{{ $title }}</h3>

    @if (empty($rows))
        <x-admin.empty-state
            class="mt-5"
            title="لا توجد بيانات"
            description="لا توجد بيانات كافية لهذا التقرير ضمن الفترة المختارة."
        />
    @else
        <div class="admin-analytics-table-wrap mt-5">
            <table class="admin-analytics-table">
                <thead>
                    <tr>
                        @foreach ($headers as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($columns as $column)
                                @php $value = data_get($row, $column); @endphp
                                <td>
                                    @if (is_numeric($value) && ! str_contains((string) $value, '.'))
                                        {{ number_format((float) $value) }}
                                    @elseif (is_numeric($value))
                                        {{ number_format((float) $value, 2) }}
                                    @else
                                        {{ $value }}
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</article>
