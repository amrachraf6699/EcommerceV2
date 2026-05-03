@csrf
@isset($method)
    @method($method)
@endisset

<div class="grid gap-4 lg:grid-cols-2">
    <label class="space-y-2">
        <span class="text-sm font-bold text-white">اسم العميل</span>
        <input class="admin-input" type="text" name="name" value="{{ old('name', $client->name ?? '') }}" required>
    </label>

    <label class="space-y-2">
        <span class="text-sm font-bold text-white">المسمى الوظيفي</span>
        <input class="admin-input" type="text" name="position" value="{{ old('position', $client->position ?? '') }}">
    </label>

    <div class="space-y-2 lg:col-span-2">
        <span class="text-sm font-bold text-white">الصورة</span>
        @if (! empty($client?->photo))
            <img class="h-24 w-24 border border-black/10 object-cover" src="{{ asset('storage/' . $client->photo) }}" alt="{{ $client->name }}">
        @endif
        <input class="admin-input" type="file" name="photo" data-filepond {{ isset($client) && $client->exists ? '' : 'required' }}>
    </div>
</div>

<div class="mt-6 flex gap-3">
    <button class="admin-btn-primary" type="submit">{{ $submitLabel }}</button>
    <a class="admin-btn-secondary" href="{{ route('admin.clients.index') }}">عودة</a>
</div>
