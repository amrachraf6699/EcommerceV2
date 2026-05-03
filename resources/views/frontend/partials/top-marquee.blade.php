@php
$headerText = setting('brand.header_text_' . app()->getLocale())
@endphp
@if($headerText)
<div class="top-marquee" aria-label="{{ __('storefront.common.page') }}">
  <div class="top-marquee__track">
    <div class="top-marquee__content">
      <span>{{ $headerText }}</span>
    </div>
  </div>
</div>
@endif
