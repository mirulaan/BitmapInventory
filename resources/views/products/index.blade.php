<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- Header Banner & Action Bar -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Product Master Catalog</h1>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 border border-slate-200">
                        {{ $products->total() }} Models
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Registered hardware components, unit pricing, specifications, and physical stock availability.</p>
            </div>
            
            @if(Auth::user()->hasRole('admin', 'warehouse'))
            <div class="shrink-0">
                <a href="{{ route('products.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 hover:shadow transition-all cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Product Model
                </a>
            </div>
            @endif
        </div>

        <!-- Success & Error Alerts -->
        @if(session('success'))
            <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-emerald-800 text-sm font-semibold">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-rose-800 text-sm font-semibold">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>{{ session('error') }}</span>
                </div>
            </div>
        @endif

        <!-- Filter & Search Toolbar -->
        <form method="GET" action="{{ route('products.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col md:flex-row gap-3 items-center">
            
            <!-- Search Bar -->
            <div class="flex-1 w-full relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search model name, SKU, or brand..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>

            <!-- Brand Filter -->
            <div class="w-full md:w-44">
                <select name="brand" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Brands</option>
                    @foreach($brands as $b)
                        <option value="{{ $b }}" {{ request('brand') == $b ? 'selected' : '' }}>
                            {{ $b }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Category Filter -->
            <div class="w-full md:w-44">
                <select name="category" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->name }}" {{ request('category') == $category->name ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Sort By -->
            <div class="w-full md:w-44">
                <select name="sort" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Name (A to Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Name (Z to A)</option>
                    <option value="brand_asc" {{ request('sort') == 'brand_asc' ? 'selected' : '' }}>Brand (A to Z)</option>
                    <option value="brand_desc" {{ request('sort') == 'brand_desc' ? 'selected' : '' }}>Brand (Z to A)</option>
                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Price (High to Low)</option>
                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Price (Low to High)</option>
                </select>
            </div>

            <!-- Stock Alert Filter -->
            <div class="w-full md:w-40">
                <select name="stock_alert" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Stock</option>
                    <option value="low_stock" {{ request('stock_alert') == 'low_stock' ? 'selected' : '' }}>⚠️ Low Stock Only</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full md:w-auto">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors w-full md:w-auto cursor-pointer">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'brand', 'category', 'sort', 'stock_alert']))
                    <a href="{{ route('products.index') }}" class="rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors w-full md:w-auto text-center">
                        Clear
                    </a>
                @endif
            </div>

        </form>

        <!-- Product Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Product Model</th>
                            <th scope="col" class="py-3.5 px-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Category</th>
                            <th scope="col" class="py-3.5 px-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Unit Price</th>
                            <th scope="col" class="py-3.5 px-3 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Physical Stock</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($products as $product)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Product Model Name + Image/Icon Thumbnail -->
                                <td class="py-4 pl-6 pr-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3.5">
                                        
                                        <!-- Thumbnail Box -->
                                        <div class="w-12 h-12 rounded-xl border border-slate-200/80 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0 shadow-2xs">
                                            @if($product->image_url)
                                                <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-0.5">
                                            @else
                                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-tr from-slate-100 to-indigo-50/50 text-indigo-600 font-bold text-sm">
                                                    {{ strtoupper(substr($product->name, 0, 1)) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('inventory.product', $product->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors text-sm truncate block">
                                                    {{ $product->name }}
                                                </a>
                                                @if($product->brand)
                                                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-50 border border-indigo-100 px-1.5 py-0.5 rounded">
                                                        {{ $product->brand }}
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <span class="font-mono text-xs text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">{{ $product->sku }}</span>
                                                @if($product->description)
                                                    <span class="text-xs text-slate-400 truncate max-w-xs hidden sm:inline" title="{{ $product->description }}">{{ Str::limit($product->description, 35) }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Category -->
                                <td class="py-4 px-3 whitespace-nowrap">
                                    <span class="inline-flex items-center rounded-lg bg-slate-100 px-2.5 py-1 text-xs font-semibold text-slate-700">
                                        {{ $product->category }}
                                    </span>
                                </td>

                                <!-- Unit Price -->
                                <td class="py-4 px-3 whitespace-nowrap text-sm font-semibold text-slate-900">
                                    {{ $product->unit_price ? 'RM ' . number_format($product->unit_price, 2) : '-' }}
                                </td>

                                <!-- Stock Availability & Min Alert -->
                                <td class="py-4 px-3 whitespace-nowrap text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-0.5 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10" title="In Stock">
                                                {{ $product->in_stock_qty }} stock
                                            </span>
                                            <span class="inline-flex items-center gap-1 rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20" title="On Loan">
                                                {{ $product->on_loan_qty }} loan
                                            </span>
                                        </div>

                                        @if($product->min_stock_level > 0)
                                            @if($product->in_stock_qty <= $product->min_stock_level)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-rose-50 border border-rose-200 px-2 py-0.5 text-[10px] font-bold text-rose-700 mt-1.5" title="In-stock count is at or below minimum threshold of {{ $product->min_stock_level }}">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span> Low (Min: {{ $product->min_stock_level }})
                                                </span>
                                            @else
                                                <span class="text-[10px] text-slate-400 font-medium mt-1">
                                                    Min: {{ $product->min_stock_level }}
                                                </span>
                                            @endif
                                        @endif
                                    </div>
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap text-xs font-medium">
                                    <div class="flex items-center justify-end gap-2">
                                        <!-- View Physical Hardware Units -->
                                        <a href="{{ route('inventory.product', $product->id) }}" class="inline-flex items-center rounded-xl bg-slate-100 px-2.5 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-colors">
                                            View Units
                                        </a>

                                        <!-- Edit & Delete (admin, warehouse) -->
                                        @if(Auth::user()->hasRole('admin', 'warehouse'))
                                        <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center rounded-xl bg-indigo-50 px-2.5 py-1.5 text-xs font-semibold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 transition-colors">
                                            Edit
                                        </a>

                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Delete {{ $product->name }} ({{ $product->sku }})? This action cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center rounded-xl bg-rose-50 px-2.5 py-1.5 text-xs font-semibold text-rose-700 border border-rose-200/80 hover:bg-rose-100 transition-colors cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-sm text-slate-400 font-medium">
                                    No products found matching your search.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($products->hasPages())
                <div class="p-4 border-t border-slate-100 bg-white">
                    {{ $products->links() }}
                </div>
            @endif
        </div>

    </div>
</x-app-layout>
