<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Client::withCount([
            'inventoryItems as total_units_count',
            'inventoryItems as active_loans_count' => function ($q) {
                $q->where('status', 'on_loan');
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $like) {
                $q->where('company_name', $like, "%{$search}%")
                    ->orWhere('contact_person', $like, "%{$search}%")
                    ->orWhere('contact_no', $like, "%{$search}%")
                    ->orWhere('email', $like, "%{$search}%");
            });
        }

        $clients = $query->orderBy('company_name')->paginate(15)->withQueryString();

        return view('clients.index', compact('clients'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        Client::create($request->all());

        return redirect()->route('clients.index')->with('success', 'Client created successfully.');
    }

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_no' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
        ]);

        $client->update($request->all());

        return redirect()->route('clients.index')->with('success', 'Client updated successfully.');
    }

    public function destroy(Client $client)
    {
        if (\App\Models\InventoryItem::where('client_id', $client->id)->exists()) {
            return back()->with('error', 'Cannot delete this client because hardware inventory units are actively assigned or loaned to them.');
        }

        $client->delete();

        return redirect()->route('clients.index')->with('success', 'Client deleted successfully.');
    }
}
