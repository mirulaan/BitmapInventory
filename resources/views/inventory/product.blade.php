<x-app-layout>
    <div class="space-y-6">
        <!-- Top Navigation & Action -->
        <div class="flex items-center justify-between">
            <a href="{{ route('inventory.index') }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-slate-500 hover:text-slate-900 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                <span>Back to Fleet Overview</span>
            </a>

            <a href="{{ route('products.edit', $product->id) }}" class="inline-flex items-center gap-1.5 rounded-xl bg-white border border-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition-colors">
                <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L6.832 19.82a4.5 4.5 0 01-1.897 1.13l-2.685.8.8-2.685a4.5 4.5 0 011.13-1.897L16.863 4.487zm0 0L19.5 7.125" />
                </svg>
                <span>Edit Model Specs & Photo</span>
            </a>
        </div>

        <!-- Product Hero Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-start sm:items-center gap-4">
                <!-- Product Thumbnail / Visual Box -->
                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl border border-slate-200/80 bg-slate-50 p-1 flex items-center justify-center shrink-0 shadow-2xs overflow-hidden">
                    @if($product->image_url)
                        <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain">
                    @else
                        <div class="w-full h-full flex items-center justify-center rounded-xl bg-gradient-to-tr from-slate-100 to-indigo-50/50 text-indigo-600 font-bold text-xl sm:text-2xl">
                            {{ strtoupper(substr($product->name, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-slate-900 tracking-tight">{{ $product->name }}</h1>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <span class="font-mono text-xs font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded-md border border-slate-200/80">
                            {{ $product->sku }}
                        </span>
                        @if($product->brand)
                            <span class="text-xs font-bold text-slate-800 bg-slate-100 px-2.5 py-0.5 rounded-md border border-slate-200/80">
                                {{ $product->brand }}
                            </span>
                        @endif
                        <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-2.5 py-0.5 rounded-md border border-indigo-100">
                            {{ $product->category }}
                        </span>
                        @if($product->unit_price)
                            <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-md border border-emerald-100">
                                RM {{ number_format($product->unit_price, 2) }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Unit Breakdown Stats -->
            <div class="flex items-center gap-3 border-t md:border-t-0 md:border-l border-slate-100 pt-4 md:pt-0 md:pl-6 overflow-x-auto">
                <div class="text-center px-2.5 py-1 shrink-0">
                    <p class="text-xl font-bold {{ ($product->min_stock_level > 0 && $items->where('status', 'in_stock')->count() <= $product->min_stock_level) ? 'text-rose-600' : 'text-slate-900' }}">
                        {{ $items->where('status', 'in_stock')->count() }}
                    </p>
                    <p class="text-2xs font-semibold text-emerald-600 uppercase tracking-wider mt-0.5">In Stock</p>
                </div>
                <div class="h-8 w-px bg-slate-200 shrink-0"></div>
                <div class="text-center px-2.5 py-1 shrink-0">
                    <p class="text-xl font-bold text-slate-900">{{ $items->where('status', 'on_loan')->count() }}</p>
                    <p class="text-2xs font-semibold text-amber-600 uppercase tracking-wider mt-0.5">On Loan</p>
                </div>
                <div class="h-8 w-px bg-slate-200 shrink-0"></div>
                <div class="text-center px-2.5 py-1 shrink-0">
                    <p class="text-xl font-bold text-purple-700">{{ $items->where('status', 'reserved')->count() }}</p>
                    <p class="text-2xs font-semibold text-purple-600 uppercase tracking-wider mt-0.5">Reserved</p>
                </div>
                <div class="h-8 w-px bg-slate-200 shrink-0"></div>
                <div class="text-center px-2.5 py-1 shrink-0">
                    <p class="text-xl font-bold text-rose-700">{{ $items->where('status', 'rma_defective')->count() }}</p>
                    <p class="text-2xs font-semibold text-rose-600 uppercase tracking-wider mt-0.5">RMA/Repair</p>
                </div>
                <div class="h-8 w-px bg-slate-200 shrink-0"></div>
                <div class="text-center px-2.5 py-1 shrink-0">
                    <p class="text-xl font-bold text-slate-900">{{ $items->count() }}</p>
                    <p class="text-2xs font-semibold text-slate-400 uppercase tracking-wider mt-0.5">Total</p>
                </div>
            </div>
        </div>

        <!-- Low Stock Warning Alert -->
        @if($product->min_stock_level > 0 && $items->where('status', 'in_stock')->count() <= $product->min_stock_level)
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-2.5 text-rose-800 text-sm font-semibold">
                    <svg class="w-5 h-5 text-rose-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>Low Stock Alert: Current warehouse inventory ({{ $items->where('status', 'in_stock')->count() }} units) is at or below the minimum threshold of {{ $product->min_stock_level }} units.</span>
                </div>
                @if(Auth::user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('inventory.create') }}" class="rounded-xl bg-rose-600 px-3.5 py-1.5 text-xs font-bold text-white shadow-sm hover:bg-rose-500 transition-colors shrink-0 text-center">
                    Stock In More
                </a>
                @endif
            </div>
        @endif

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

        <!-- Search & Filter Bar -->
        <form method="GET" action="{{ route('inventory.product', $product->id) }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col sm:flex-row gap-3 items-center">
            <!-- SN Search Input -->
            <div class="flex-1 w-full relative">
                <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by Serial Number..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>
            
            <!-- Location Filter -->
            <div class="w-full sm:w-56">
                <select name="location_id" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Locations</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-48">
                <select name="status" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Statuses</option>
                    <option value="in_stock" {{ request('status') == 'in_stock' ? 'selected' : '' }}>In Stock Only</option>
                    <option value="on_loan" {{ request('status') == 'on_loan' ? 'selected' : '' }}>On Loan Only</option>
                </select>
            </div>

            <!-- Buttons -->
            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors w-full sm:w-auto cursor-pointer">
                    Search
                </button>
                @if(request()->anyFilled(['search', 'location_id', 'status']))
                    <a href="{{ route('inventory.product', $product->id) }}" class="rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors w-full sm:w-auto text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <!-- Hardware Units Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Serial Number</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Current Location & Client</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Serial Number -->
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                    <div class="flex items-center gap-2">
                                        <div class="w-2 h-2 rounded-full {{ $item->status === 'in_stock' ? 'bg-emerald-500' : ($item->status === 'on_loan' ? 'bg-amber-500' : ($item->status === 'reserved' ? 'bg-purple-500' : 'bg-rose-500')) }}"></div>
                                        <span class="font-mono font-bold text-slate-900">{{ $item->serial_number }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 mt-1">
                                        @if($item->batch_no)
                                            <span class="inline-flex items-center rounded bg-slate-100 px-1.5 py-0.5 text-2xs font-mono font-medium text-slate-600 border border-slate-200">
                                                Lot: {{ $item->batch_no }}
                                            </span>
                                        @endif
                                        @if($item->unit_cost > 0)
                                            <span class="inline-flex items-center text-2xs text-slate-400 font-mono">
                                                Cost: RM {{ number_format($item->unit_cost, 2) }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                
                                <!-- Location & Client -->
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    @if($item->client)
                                        <div class="font-bold text-slate-900">{{ $item->client->company_name }}</div>
                                    @endif
                                    <div class="text-slate-500 flex items-center gap-1.5 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                        </svg>
                                        <span>{{ $item->location ? $item->location->name : 'Unknown Location' }}</span>
                                    </div>
                                </td>

                                <!-- Status Badge -->
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    @if($item->status === 'on_loan')
                                        @php
                                            $endDate = $item->currentLoan?->loan_end_date ? \Carbon\Carbon::parse($item->currentLoan->loan_end_date)->endOfDay() : null;
                                            $isOverdue = $endDate && $endDate->isPast();
                                            $isExpiringSoon = $endDate && $endDate->isFuture() && now()->diffInDays($endDate) <= 14;
                                        @endphp

                                        @if($isOverdue)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-50 px-3 py-1 text-xs font-bold text-rose-700 border border-rose-300 animate-pulse" title="Overdue since {{ $endDate->format('d M Y') }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                                Overdue ({{ $endDate->format('d M') }})
                                            </span>
                                        @elseif($isExpiringSoon)
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800 border border-amber-300" title="Due on {{ $endDate->format('d M Y') }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                                Due Soon ({{ $endDate->format('d M') }})
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50/60 px-3 py-1 text-xs font-semibold text-amber-700 border border-amber-200" title="{{ $endDate ? 'Due on ' . $endDate->format('d M Y') : 'No end date set' }}">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                                                On Loan
                                            </span>
                                        @endif
                                    @else
                                        <x-status-badge :status="$item->status" />
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right">
                                    <div class="flex items-center justify-end flex-wrap gap-1.5">
                                        @if(Auth::user()->hasRole('admin', 'warehouse', 'engineer'))
                                            @if($item->status === 'in_stock')
                                                <!-- Transfer -->
                                                <a href="{{ route('inventory.transfer', $item->id) }}" class="inline-flex items-center rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-colors" title="Move rack">
                                                    Transfer
                                                </a>
                                                <!-- Reserve -->
                                                <a href="{{ route('inventory.reserve', $item->id) }}" class="inline-flex items-center rounded-lg bg-purple-50 px-2 py-1 text-xs font-semibold text-purple-700 border border-purple-200 hover:bg-purple-100 transition-colors">
                                                    Reserve
                                                </a>
                                                <!-- Loan -->
                                                <a href="{{ route('inventory.loan', $item->id) }}" class="inline-flex items-center rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 transition-colors">
                                                    Loan Out
                                                </a>
                                                <!-- Sell -->
                                                <a href="{{ route('inventory.sell', $item->id) }}" class="inline-flex items-center rounded-lg bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/80 hover:bg-emerald-100 transition-colors">
                                                    Sell
                                                </a>
                                                <!-- RMA -->
                                                <a href="{{ route('inventory.rma', $item->id) }}" class="inline-flex items-center rounded-lg bg-rose-50 px-2 py-1 text-xs font-semibold text-rose-700 border border-rose-200 hover:bg-rose-100 transition-colors" title="Defective">
                                                    RMA
                                                </a>
                                                <!-- Scrap / Decommission (admin, warehouse) -->
                                                @if(Auth::user()->hasRole('admin', 'warehouse'))
                                                    <a href="{{ route('inventory.decommission', $item->id) }}" class="inline-flex items-center rounded-lg bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors" title="Decommission / Scrap">
                                                        Scrap
                                                    </a>
                                                @endif
                                            @elseif($item->status === 'reserved')
                                                <!-- Release Reservation -->
                                                <form action="{{ route('inventory.process_release', $item->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" onclick="return confirm('Release this reserved hardware back to active warehouse stock?')" class="rounded-lg bg-slate-100 px-2 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-200 transition-colors cursor-pointer">
                                                        Release
                                                    </button>
                                                </form>
                                                <a href="{{ route('inventory.loan', $item->id) }}" class="inline-flex items-center rounded-lg bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors">
                                                    Loan
                                                </a>
                                                <a href="{{ route('inventory.sell', $item->id) }}" class="inline-flex items-center rounded-lg bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                                    Sell
                                                </a>
                                            @elseif($item->status === 'rma_defective')
                                                <!-- Return from RMA -->
                                                <a href="{{ route('inventory.return_rma', $item->id) }}" class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors">
                                                    Return to Stock
                                                </a>
                                                @if(Auth::user()->hasRole('admin', 'warehouse'))
                                                    <a href="{{ route('inventory.decommission', $item->id) }}" class="inline-flex items-center rounded-lg bg-amber-50 px-2 py-1 text-xs font-semibold text-amber-700 border border-amber-200 hover:bg-amber-100 transition-colors">
                                                        Scrap
                                                    </a>
                                                @endif
                                            @elseif($item->status === 'on_loan')
                                                <a href="{{ route('inventory.return', $item->id) }}" class="inline-flex items-center rounded-lg bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/80 hover:bg-emerald-100 transition-colors">
                                                    Return
                                                </a>
                                                <a href="{{ route('inventory.convert', $item->id) }}" class="inline-flex items-center rounded-lg bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700 border border-purple-200/80 hover:bg-purple-100 transition-colors">
                                                    Convert to Sale
                                                </a>
                                            @endif
                                        @endif

                                        <!-- Edit Button (admin, warehouse) -->
                                        @if(Auth::user()->hasRole('admin', 'warehouse'))
                                            <a href="{{ route('inventory.edit', $item->id) }}" class="inline-flex items-center rounded-lg bg-slate-50 px-2 py-1 text-xs font-semibold text-slate-700 border border-slate-200/80 hover:bg-slate-100 transition-colors">
                                                Edit
                                            </a>
                                        @endif

                                        @if(Auth::user()->isViewer())
                                            <span class="text-xs text-slate-400 italic font-medium px-2 py-1">Read-only</span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="py-12 text-center">
                                    <div class="max-w-xs mx-auto text-center space-y-2">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0l-3-3m3 3l3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-800">No Hardware Units Found</p>
                                        <p class="text-xs text-slate-500">There are no registered serial numbers matching the active filters for this product model.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
