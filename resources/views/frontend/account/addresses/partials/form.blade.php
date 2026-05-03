<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.address_label') }}</label>
  <input type="text" name="label" class="input-field" value="{{ old('label', $address?->label) }}">
</div>

<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.recipient_name') }}</label>
  <input type="text" name="recipient_name" class="input-field" value="{{ old('recipient_name', $address?->recipient_name) }}">
</div>

<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.phone') }}</label>
  <input type="text" name="phone" class="input-field" value="{{ old('phone', $address?->phone) }}">
</div>

<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.country') }}</label>
  <select name="country" class="sort-select country-select">
    @include('frontend.partials.country-options', ['selectedCountry' => old('country', $address?->country)])
  </select>
</div>

<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.state') }}</label>
  <input type="text" name="state" class="input-field" value="{{ old('state', $address?->state) }}">
</div>

<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.city') }}</label>
  <input type="text" name="city" class="input-field" value="{{ old('city', $address?->city) }}">
</div>

<div class="md:col-span-2">
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.address_line_1') }}</label>
  <input type="text" name="address_line_1" class="input-field" value="{{ old('address_line_1', $address?->address_line_1) }}" required>
  @error('address_line_1')<p class="mt-2 text-sm" style="color:#ffb2b2">{{ $message }}</p>@enderror
</div>

<div class="md:col-span-2">
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.address_line_2') }}</label>
  <input type="text" name="address_line_2" class="input-field" value="{{ old('address_line_2', $address?->address_line_2) }}">
</div>

<div>
  <label class="text-xs font-bold mb-2 block" style="letter-spacing:0.1em;color:var(--gray-light)">{{ __('storefront.account.postal_code') }}</label>
  <input type="text" name="postal_code" class="input-field" value="{{ old('postal_code', $address?->postal_code) }}">
</div>

<div class="flex flex-col gap-3 justify-end">
  <label class="flex items-center gap-3 text-sm" style="color:var(--gray-light)">
    <input type="checkbox" class="checkbox-custom" name="is_default_shipping" value="1" @checked(old('is_default_shipping', $address?->is_default_shipping))>
    {{ __('storefront.account.default_shipping') }}
  </label>
  <label class="flex items-center gap-3 text-sm" style="color:var(--gray-light)">
    <input type="checkbox" class="checkbox-custom" name="is_default_billing" value="1" @checked(old('is_default_billing', $address?->is_default_billing))>
    {{ __('storefront.account.default_billing') }}
  </label>
</div>

<div class="md:col-span-2">
  <button type="submit" class="btn-primary"><span>{{ $submitLabel }}</span></button>
</div>
