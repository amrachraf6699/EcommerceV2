@php
  $rawWhatsappPhone = (string) ($frontendBrand['whatsapp_phone'] ?? '');
  $whatsappPhone = preg_replace('/\D+/', '', $rawWhatsappPhone);
@endphp

@if ($whatsappPhone)
  <a
    href="https://wa.me/{{ $whatsappPhone }}"
    class="whatsapp-float"
    target="_blank"
    rel="noreferrer"
    aria-label="{{ __('storefront.footer.contact') }}"
  >
    <i class="bx bxl-whatsapp text-[1.6rem]" aria-hidden="true"></i>
  </a>
@endif
