<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Location::withCount([
            'inventoryItems as active_units_count' => function ($q) {
                $q->where('status', '!=', 'sold');
            }
        ]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhere('branch', $like, "%{$search}%")
                    ->orWhere('floor', $like, "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('branch')) {
            $query->where('branch', $request->branch);
        }

        $locations = $query->orderBy('type')
            ->orderBy('branch')
            ->orderBy('floor')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('locations.index', compact('locations'));
    }

    public function create()
    {
        return view('locations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'creation_mode' => 'required|in:internal,client_site',
            'branch' => 'nullable|required_if:creation_mode,internal|in:KL,Penang',
            'floor' => 'nullable|required_if:creation_mode,internal|in:Floor 1,Floor 2,Floor 3,Floor 4',
            'rack_name' => 'nullable|required_if:creation_mode,internal|string|max:255',
            'client_site_name' => 'nullable|required_if:creation_mode,client_site|string|max:255',
        ]);

        if ($request->creation_mode === 'internal') {
            Location::create([
                'type' => 'internal',
                'branch' => $request->branch,
                'floor' => $request->floor,
                'name' => $request->rack_name,
            ]);
        } else {
            Location::create([
                'type' => 'client_site',
                'name' => $request->client_site_name,
            ]);
        }

        return redirect()->route('locations.index')->with('success', 'Location successfully created.');
    }

    public function edit(Location $location)
    {
        return view('locations.edit', compact('location'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'creation_mode' => 'required|in:internal,client_site',
            'branch' => 'nullable|required_if:creation_mode,internal|in:KL,Penang',
            'floor' => 'nullable|required_if:creation_mode,internal|in:Floor 1,Floor 2,Floor 3,Floor 4',
            'rack_name' => 'nullable|required_if:creation_mode,internal|string|max:255',
            'client_site_name' => 'nullable|required_if:creation_mode,client_site|string|max:255',
        ]);

        if ($request->creation_mode === 'internal') {
            $location->update([
                'type' => 'internal',
                'branch' => $request->branch,
                'floor' => $request->floor,
                'name' => $request->rack_name,
            ]);
        } else {
            $location->update([
                'type' => 'client_site',
                'branch' => null,
                'floor' => null,
                'name' => $request->client_site_name,
            ]);
        }

        return redirect()->route('locations.index')->with('success', 'Location successfully updated.');
    }

    public function destroy(Location $location)
    {
        if (\App\Models\InventoryItem::where('location_id', $location->id)->exists()) {
            return back()->with('error', 'Cannot delete! This location currently holds active inventory.');
        }

        $location->delete();

        return redirect()->route('locations.index')->with('success', 'Location successfully removed.');
    }
}
