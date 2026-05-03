@php
    $tabs = [
        'basic' => 'البيانات الأساسية',
        'seo' => 'SEO',
        'variants' => 'النسخ',
        'images' => 'الصور',
    ];
@endphp

<div class="admin-tabs" role="tablist" aria-label="أقسام المنتج">
    @foreach ($tabs as $tabKey => $tabLabel)
        <button
            class="admin-tab {{ $loop->first ? 'is-active' : '' }}"
            type="button"
            role="tab"
            data-admin-tab-trigger="{{ $tabKey }}"
            aria-selected="{{ $loop->first ? 'true' : 'false' }}"
        >
            {{ $tabLabel }}
        </button>
    @endforeach
</div>
