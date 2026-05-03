<form method="POST" action="{{ route('admin.products.update', $product) }}" class="space-y-6" data-ajax-form data-ajax-fragment="product">
    @csrf
    @method('PUT')
    @include('admin.products.partials.basic-fields', ['product' => $product, 'categories' => $categories])
    <div class="flex justify-end">
        <button class="admin-btn-primary" type="submit">حفظ البيانات الأساسية</button>
    </div>
</form>
