<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BrandController extends Controller
{
    /**
     * Display a listing of brands with search and catalog usage counts.
     */
    public function index(Request $request): View
    {
        $like = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Brand::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', $like, "%{$request->search}%");
        }

        $brands = $query->paginate(15)->withQueryString();

        // Get product counts for each brand
        $brandCounts = Product::selectRaw('brand, count(*) as count')
            ->whereNotNull('brand')
            ->where('brand', '!=', '')
            ->groupBy('brand')
            ->pluck('count', 'brand');

        return view('settings.brands.index', compact('brands', 'brandCounts'));
    }

    /**
     * Store a newly created brand in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
        ]);

        Brand::create(['name' => trim($validated['name'])]);

        return redirect()->route('brands.index')->with('success', 'Brand added successfully.');
    }

    /**
     * Show the form for editing the specified brand.
     */
    public function edit(Brand $brand): View
    {
        return view('settings.brands.edit', compact('brand'));
    }

    /**
     * Update the specified brand in storage and cascade rename to products.
     */
    public function update(Request $request, Brand $brand): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
        ]);

        $oldName = $brand->name;
        $newName = trim($validated['name']);

        $brand->update(['name' => $newName]);

        // Cascade rename to all products using this brand
        Product::where('brand', $oldName)->update(['brand' => $newName]);

        return redirect()->route('brands.index')->with('success', 'Brand renamed successfully.');
    }

    /**
     * Remove the specified brand from storage if not currently in use.
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        if (Product::where('brand', $brand->name)->exists()) {
            return back()->with('error', 'Cannot delete this brand because there are products actively using it.');
        }

        $brand->delete();

        return redirect()->route('brands.index')->with('success', 'Brand deleted successfully.');
    }
}
