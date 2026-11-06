<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- Header Banner & Action Bar -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Hardware Fleet Assets</h1>
                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-bold text-slate-700 border border-slate-200">
                        {{ $products->count() }} Product Models
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Live operational stock availability, warehouse reserves, and field deployment breakdown.</p>
            </div>
            
            <div class="flex items-center gap-2.5 shrink-0">
                <a href="{{ route('products.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    Catalog Models
                </a>
                @if(Auth::user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 hover:shadow transition-all cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Receive Hardware
                </a>
                @endif
            </div>
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

        <!-- Search & Sort Filter -->
        <form method="GET" action="{{ route('inventory.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-center">
            <div class="flex-1 w-full relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search hardware model name, SKU, or brand..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>

            <!-- Brand Filter -->
            <div class="w-full sm:w-48">
                <select name="brand" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Brands</option>
                    @foreach($brands ?? [] as $b)
                        <option value="{{ $b }}" {{ request('brand') == $b ? 'selected' : '' }}>{{ $b }}</option>
                    @endforeach
                </select>
            </div>
            
            <div class="w-full sm:w-56">
                <select name="sort" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Model Name (A-Z)</option>
                    <option value="name_desc" {{ request('sort') == 'name_desc' ? 'selected' : '' }}>Model Name (Z-A)</option>
                    <option value="brand_asc" {{ request('sort') == 'brand_asc' ? 'selected' : '' }}>Brand (A-Z)</option>
                    <option value="brand_desc" {{ request('sort') == 'brand_desc' ? 'selected' : '' }}>Brand (Z-A)</option>
                    <option value="category_asc" {{ request('sort') == 'category_asc' ? 'selected' : '' }}>Category (A-Z)</option>
                    <option value="category_desc" {{ request('sort') == 'category_desc' ? 'selected' : '' }}>Category (Z-A)</option>
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors w-full sm:w-auto cursor-pointer">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'brand', 'sort']))
                    <a href="{{ route('inventory.index') }}" class="rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors w-full sm:w-auto text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <!-- Fleet Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Product Model</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">In Warehouse</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">On Client Loan</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Total Active Fleet</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
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

                                        <div>
                                            <div class="flex items-center gap-2">
                                                <a href="{{ route('inventory.product', $product->id) }}" class="font-bold text-slate-900 hover:text-indigo-600 transition-colors text-sm block">
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
                                                <span class="text-xs text-slate-400 font-medium">{{ $product->category }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- In Warehouse -->
                                <td class="py-4 px-4 whitespace-nowrap text-center">
                                    @if($product->min_stock_level > 0 && $product->in_stock_qty <= $product->min_stock_level)
                                        <div class="flex flex-col items-center">
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3.5 py-1 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/30">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500 animate-pulse"></span>
                                                {{ $product->in_stock_qty }} Units
                                            </span>
                                            <span class="text-[10px] text-rose-600 font-bold mt-1">⚠️ Low (Min: {{ $product->min_stock_level }})</span>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3.5 py-1 text-xs font-bold text-blue-700 ring-1 ring-inset ring-blue-700/10">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            {{ $product->in_stock_qty }} Units
                                        </span>
                                    @endif
                                </td>

                                <!-- On Client Loan -->
                                <td class="py-4 px-4 whitespace-nowrap text-center">
                                    <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3.5 py-1 text-xs font-bold text-amber-700 ring-1 ring-inset ring-amber-600/20">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                        {{ $product->on_loan_qty }} Units
                                    </span>
                                </td>

                                <!-- Total Active Fleet -->
                                <td class="py-4 px-4 whitespace-nowrap text-center text-sm font-extrabold text-slate-900">
                                    {{ $product->in_stock_qty + $product->on_loan_qty }}
                                </td>

                                <!-- View Units -->
                                <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap text-xs font-medium">
                                    <a href="{{ route('inventory.product', $product->id) }}" class="inline-flex items-center gap-1 rounded-xl bg-white border border-slate-300 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-indigo-600 transition-all">
                                        View Serial Numbers
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                        </svg>
                                    </a>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-sm text-slate-400 font-medium">
                                    No hardware inventory found. Use "+ Receive Hardware" to add inventory.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</x-app-layout>
