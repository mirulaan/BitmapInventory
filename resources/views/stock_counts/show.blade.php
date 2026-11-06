<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('stock-counts.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-2xs">
                    &larr;
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold font-mono text-slate-900 tracking-tight">{{ $stockCount->count_number }}</h1>
                        @if($stockCount->status === 'in_progress')
                            <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200 animate-pulse">Scanning Active</span>
                        @else
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Reconciled & Closed</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Scope: <span class="font-bold text-slate-700">{{ $stockCount->location ? $stockCount->location->full_path : 'Full Warehouse' }}</span>
                        · Started {{ $stockCount->started_at->format('d M Y, h:i A') }} by {{ $stockCount->starter?->name ?? 'System' }}
                    </p>
                </div>
            </div>

            <!-- Reconciliation Action -->
            <div class="mt-4 sm:mt-0 flex items-center gap-2">
                @if(Auth::user()->hasRole('admin', 'warehouse') && $stockCount->status === 'in_progress')
                    <form action="{{ route('stock-counts.reconcile', $stockCount->id) }}" method="POST" onsubmit="return confirm('Complete this cycle count and reconcile all discrepancies? Missing units will be written off as shrinkage, and misplaced units will be relocated.')">
                        @csrf
                        <button type="submit" class="rounded-xl bg-emerald-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-500 transition-colors cursor-pointer">
                            Reconcile Discrepancies & Close Session
                        </button>
                    </form>
                @endif
            </div>
        </div>

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

        @if(session('warning'))
            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-amber-800 text-sm font-semibold">
                    <svg class="w-5 h-5 text-amber-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                    <span>{{ session('warning') }}</span>
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

        <!-- Live Barcode Scanning Bar -->
        @if($stockCount->status === 'in_progress')
            <div class="bg-white border-2 border-indigo-500/40 rounded-2xl p-4 shadow-sm">
                <form action="{{ route('stock-counts.scan', $stockCount->id) }}" method="POST" class="flex flex-col sm:flex-row items-center gap-3">
                    @csrf
                    <div class="flex-1 w-full relative">
                        <svg class="w-5 h-5 text-indigo-500 absolute left-3.5 top-1/2 -translate-y-1/2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 013.75 9.375v-4.5zM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 01-1.125-1.125v-4.5zM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0113.5 9.375v-4.5z" />
                        </svg>
                        <input type="text" id="serial_number" name="serial_number" autofocus placeholder="Scan barcode or type serial number and press Enter..." class="block w-full rounded-xl border-0 pl-11 pr-4 py-2.5 text-sm font-mono text-slate-900 bg-slate-50 focus:bg-white ring-1 ring-inset ring-slate-200 focus:ring-2 focus:ring-indigo-600">
                    </div>
                    <button type="submit" class="w-full sm:w-auto rounded-xl bg-indigo-600 px-6 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-colors">
                        Verify Scan
                    </button>
                </form>
            </div>
        @endif

        <!-- Discrepancy Breakdown Metrics -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Matched & Verified</p>
                <p class="text-2xl font-bold text-emerald-700 mt-1">{{ $matchedCount }}</p>
                <p class="text-2xs text-slate-400 mt-0.5">Found physically on expected rack</p>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-rose-600 uppercase tracking-wider">Missing / Shrinkage</p>
                <p class="text-2xl font-bold text-rose-700 mt-1">{{ $missingCount }}</p>
                <p class="text-2xs text-slate-400 mt-0.5">Expected but not scanned yet</p>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">Misplaced Items</p>
                <p class="text-2xl font-bold text-amber-700 mt-1">{{ $misplacedCount }}</p>
                <p class="text-2xs text-slate-400 mt-0.5">Belongs to another rack</p>
            </div>

            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Surplus / Untracked</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $surplusCount }}</p>
                <p class="text-2xs text-slate-400 mt-0.5">Unregistered hardware scanned</p>
            </div>
        </div>

        <!-- Inventory Count Items Manifest -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                <h2 class="text-base font-bold text-slate-900">Stock Count Audit Manifest ({{ $totalItems }} Items)</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Serial Number</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Hardware Model</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Expected Location</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Audit Status</th>
                            <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Scanned At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($stockCount->items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm font-mono font-bold text-slate-900">
                                    {{ $item->scanned_serial_number }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <div class="font-bold text-slate-900">{{ $item->inventoryItem?->product?->name ?? 'Untracked Hardware' }}</div>
                                    <div class="font-mono text-slate-400">{{ $item->inventoryItem?->product?->sku ?? 'N/A' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs font-medium text-slate-600">
                                    {{ $item->expectedLocation?->full_path ?? 'Unassigned' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    @if($item->status === 'matched')
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-0.5 text-xs font-bold text-emerald-700 border border-emerald-200">
                                            ✓ Matched
                                        </span>
                                    @elseif($item->status === 'missing')
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-0.5 text-xs font-bold text-rose-700 border border-rose-200">
                                            ⚠ Missing / Shrinkage
                                        </span>
                                    @elseif($item->status === 'misplaced')
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-0.5 text-xs font-bold text-amber-700 border border-amber-200">
                                            ⇄ Misplaced
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-bold text-slate-700">
                                            + Surplus Untracked
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-xs font-mono text-slate-500">
                                    {{ $item->scanned_at ? $item->scanned_at->format('h:i:s A') : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
