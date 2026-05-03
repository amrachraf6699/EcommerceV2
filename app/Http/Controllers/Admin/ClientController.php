<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreClientRequest;
use App\Http\Requests\Admin\UpdateClientRequest;
use App\Models\Client;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index(Request $request): View
    {
        $query = Client::query()->latest();

        if ($request->filled('search')) {
            $search = (string) $request->string('search');
            $query->where(fn ($builder) => $builder
                ->where('name', 'like', "%{$search}%")
                ->orWhere('position', 'like', "%{$search}%"));
        }

        return view('admin.clients.index', [
            'clients' => $query->paginate(12)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.clients.create', [
            'client' => new Client(),
        ]);
    }

    public function store(StoreClientRequest $request): RedirectResponse
    {
        Client::create([
            ...$request->safe()->except('photo'),
            'photo' => $request->file('photo')->store('clients', 'public'),
        ]);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'تم إنشاء العميل بنجاح.');
    }

    public function edit(Client $client): View
    {
        return view('admin.clients.edit', compact('client'));
    }

    public function update(UpdateClientRequest $request, Client $client): RedirectResponse
    {
        $data = $request->safe()->except('photo');

        if ($request->hasFile('photo')) {
            Storage::disk('public')->delete($client->photo);
            $data['photo'] = $request->file('photo')->store('clients', 'public');
        }

        $client->update($data);

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'تم تحديث العميل.');
    }

    public function destroy(Client $client): RedirectResponse
    {
        Storage::disk('public')->delete($client->photo);
        $client->delete();

        return redirect()
            ->route('admin.clients.index')
            ->with('success', 'تم حذف العميل.');
    }
}
