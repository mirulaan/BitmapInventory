<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Valuation & Cost Accounting</h1>
                <p class="mt-1 text-sm text-slate-500">Live inventory valuation models (FIFO, LIFO, Weighted Average), Cost of Goods Sold (COGS), and estimated carrying costs.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex items-center gap-3">
                <a href="{{ route('inventory.valuation.export') }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-200 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                    <svg class="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    <span>Export Valuation CSV</span>
                </a>
            </div>
        </div>

        <!-- 5 Key Metrics Cards (2-col grid on Mobile) -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-5 gap-3 sm:gap-4">
            <!-- 1. FIFO -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">FIFO Valuation</p>
                    <span class="inline-flex items-center rounded-md bg-indigo-50 px-1.5 py-0.5 text-[10px] sm:text-2xs font-semibold text-indigo-700">FIFO</span>
                </div>
                <p class="mt-2 sm:mt-3 text-lg sm:text-2xl font-bold text-slate-900">RM {{ number_format($totalFifo, 2) }}</p>
                <p class="mt-1 text-[10px] sm:text-2xs text-slate-400">Latest batch costs</p>
            </div>

            <!-- 2. LIFO -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">LIFO Valuation</p>
                    <span class="inline-flex items-center rounded-md bg-sky-50 px-1.5 py-0.5 text-[10px] sm:text-2xs font-semibold text-sky-700">LIFO</span>
                </div>
                <p class="mt-2 sm:mt-3 text-lg sm:text-2xl font-bold text-slate-900">RM {{ number_format($totalLifo, 2) }}</p>
                <p class="mt-1 text-[10px] sm:text-2xs text-slate-400">Earliest batch costs</p>
            </div>

            <!-- 3. Weighted Average -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-sm ring-1 ring-emerald-500/10">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] sm:text-xs font-bold text-emerald-700 uppercase tracking-wider">Weighted Avg</p>
                    <span class="inline-flex items-center rounded-md bg-emerald-50 px-1.5 py-0.5 text-[10px] sm:text-2xs font-semibold text-emerald-700">Avg</span>
                </div>
                <p class="mt-2 sm:mt-3 text-lg sm:text-2xl font-bold text-emerald-950">RM {{ number_format($totalWeightedAvg, 2) }}</p>
                <p class="mt-1 text-[10px] sm:text-2xs text-emerald-600">Blended cost</p>
            </div>

            <!-- 4. COGS -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Total COGS</p>
                    <span class="inline-flex items-center rounded-md bg-rose-50 px-1.5 py-0.5 text-[10px] sm:text-2xs font-semibold text-rose-700">COGS</span>
                </div>
                <p class="mt-2 sm:mt-3 text-lg sm:text-2xl font-bold text-slate-900">RM {{ number_format($totalCogs, 2) }}</p>
                <p class="mt-1 text-[10px] sm:text-2xs text-slate-400">Sold assets cost</p>
            </div>

            <!-- 5. Holding Cost -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 sm:p-5 shadow-sm col-span-2 sm:col-span-1">
                <div class="flex items-center justify-between">
                    <p class="text-[11px] sm:text-xs font-bold text-slate-500 uppercase tracking-wider">Est. Carrying Cost</p>
                    <span class="inline-flex items-center rounded-md bg-amber-50 px-1.5 py-0.5 text-[10px] sm:text-2xs font-semibold text-amber-700">18% p.a.</span>
                </div>
                <p class="mt-2 sm:mt-3 text-lg sm:text-2xl font-bold text-amber-900">RM {{ number_format($estimatedHoldingCost, 2) }}</p>
                <p class="mt-1 text-[10px] sm:text-2xs text-slate-400">Storage & overhead estimate</p>
            </div>
        </div>

        <!-- Breakdown Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Product Model Cost Accounting Breakdown</h2>
                    <p class="text-xs text-slate-500">Individual product unit costs, valuation models, and gross profit metrics.</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Product / SKU</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Category & Brand</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">In Stock</th>
                            <th scope="col" class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Avg Unit Cost</th>
                            <th scope="col" class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Catalog Price</th>
                            <th scope="col" class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">FIFO Value</th>
                            <th scope="col" class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Weighted Avg Value</th>
                            <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Sold / COGS</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($rows as $row)
                            @php
                                $p = $row['product'];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                    <div class="font-bold text-slate-900">{{ $p->name }}</div>
                                    <div class="font-mono text-xs text-slate-500">{{ $p->sku }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $p->category ?? 'N/A' }}</span>
                                    @if($p->brand)
                                        <span class="text-slate-500 ml-1">· {{ $p->brand }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center font-bold text-slate-900">
                                    {{ $row['in_stock_count'] }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-mono font-semibold text-slate-700">
                                    RM {{ number_format($row['weighted_avg_cost'], 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-mono text-slate-600">
                                    RM {{ number_format($row['catalog_unit_price'], 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-mono font-bold text-indigo-700">
                                    RM {{ number_format($row['fifo_valuation'], 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-mono font-bold text-emerald-700">
                                    RM {{ number_format($row['weighted_avg_valuation'], 2) }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-xs">
                                    <span class="font-bold text-slate-900">{{ $row['sold_count'] }} units</span>
                                    <div class="font-mono text-rose-600">RM {{ number_format($row['cogs'], 2) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-sm text-slate-400 italic">No products registered in catalog.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
