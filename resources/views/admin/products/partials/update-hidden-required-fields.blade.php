<input type="hidden" name="name[ar]" value="{{ $product?->getTranslation('name', 'ar', false) }}">
<input type="hidden" name="name[en]" value="{{ $product?->getTranslation('name', 'en', false) }}">
<input type="hidden" name="slug" value="{{ $product?->slug }}">

@foreach ($product?->categories?->pluck('id') ?? [] as $categoryId)
    <input type="hidden" name="categories[]" value="{{ $categoryId }}">
@endforeach

<input type="hidden" name="short_description[ar]" value="{{ $product?->getTranslation('short_description', 'ar', false) }}">
<input type="hidden" name="short_description[en]" value="{{ $product?->getTranslation('short_description', 'en', false) }}">
<input type="hidden" name="description[ar]" value="{{ $product?->getTranslation('description', 'ar', false) }}">
<input type="hidden" name="description[en]" value="{{ $product?->getTranslation('description', 'en', false) }}">
<input type="hidden" name="notes[ar]" value="{{ $product?->getTranslation('notes', 'ar', false) }}">
<input type="hidden" name="notes[en]" value="{{ $product?->getTranslation('notes', 'en', false) }}">

@if ($product?->is_active)
    <input type="hidden" name="is_active" value="1">
@endif

@if ($product?->is_featured)
    <input type="hidden" name="is_featured" value="1">
@endif
