@if (session('success'))
    <div class="hidden" data-toast-message data-toast-type="success" data-toast-text="{{ session('success') }}"></div>
@endif

@if (session('error'))
    <div class="hidden" data-toast-message data-toast-type="error" data-toast-text="{{ session('error') }}"></div>
@endif

@if ($errors->any())
    <div class="admin-validation-errors mb-4 px-4 py-4 text-sm">
        <h3 class="font-bold">يرجى مراجعة الحقول التالية</h3>
        <ul class="mt-3 list-inside list-disc space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
