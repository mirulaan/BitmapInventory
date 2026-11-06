<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        $query = Category::orderBy('name');

        if ($request->filled('search')) {
            $query->where('name', $like, "%{$request->search}%");
        }

        $categories = $query->paginate(15)->withQueryString();

        // Get product counts for each category
        $categoryCounts = Product::selectRaw('category, count(*) as count')
            ->groupBy('category')
            ->pluck('count', 'category');

        return view('settings.categories.index', compact('categories', 'categoryCounts'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255|unique:categories,name']);
        
        Category::create(['name' => $request->name]);
        
        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function edit(Category $category)
    {
        return view('settings.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id
        ]);

        $oldName = $category->name;
        $category->update(['name' => $request->name]);

        // Smart Update: Rename this category on all existing products
        Product::where('category', $oldName)->update(['category' => $request->name]);

        return redirect()->route('categories.index')->with('success', 'Category renamed successfully.');
    }

    public function destroy(Category $category)
    {
        if (Product::where('category', $category->name)->exists()) {
            return back()->with('error', 'Cannot delete this category because there are products actively using it.');
        }

        $category->delete();

        return redirect()->route('categories.index')->with('success', 'Category deleted.');
    }
}
