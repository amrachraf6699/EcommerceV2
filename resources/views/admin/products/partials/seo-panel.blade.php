<form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6" data-ajax-form data-ajax-fragment="product">
    @csrf
    @method('PUT')
    @include('admin.products.partials.update-hidden-required-fields', ['product' => $product])
    @include('admin.products.partials.seo-fields', ['product' => $product])
    <div class="flex justify-end">
        <button class="admin-btn-primary" type="submit">حفظ بيانات SEO</button>
    </div>
</form>
