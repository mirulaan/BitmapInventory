<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Purchase Orders & Procurement</h1>
                <p class="mt-1 text-sm text-slate-500">Manage vendor purchase orders, monitor expected shipments, and automate inventory replenishment.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-3">
                <form action="{{ route('purchase-orders.auto_draft') }}" method="POST">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl bg-amber-50 border border-amber-200 px-3.5 py-2 text-xs font-bold text-amber-800 shadow-2xs hover:bg-amber-100 transition-colors cursor-pointer">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <span>Auto-Draft from Low Stock</span>
                    </button>
                </form>

                @if(Auth::user()->hasRole('admin', 'warehouse'))
                    <a href="{{ route('purchase-orders.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Create Purchase Order</span>
                    </a>
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

        @if(session('info'))
            <div class="rounded-2xl bg-blue-50 border border-blue-200 p-4 flex items-center justify-between">
                <div class="flex items-center gap-2.5 text-blue-800 text-sm font-semibold">
                    <svg class="w-5 h-5 text-blue-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                    </svg>
                    <span>{{ session('info') }}</span>
                </div>
            </div>
        @endif

        <!-- Metric Badges -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Draft POs</p>
                <p class="text-2xl font-bold text-slate-800 mt-1">{{ $draftCount }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-indigo-500 uppercase tracking-wider">Ordered / Pending</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $orderedCount }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Received & Stocked</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $receivedCount }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total PO Records</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalOrdersCount }}</p>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row items-center gap-3">
            <div class="flex-1 w-full relative">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search PO number, supplier, remarks..." class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
            </div>

            <div class="w-full sm:w-48">
                <select name="status" class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Statuses</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="ordered" {{ request('status') === 'ordered' ? 'selected' : '' }}>Ordered</option>
                    <option value="received" {{ request('status') === 'received' ? 'selected' : '' }}>Received</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>

            <div class="w-full sm:w-56">
                <select name="supplier_id" class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Suppliers</option>
                    @foreach($suppliers as $sup)
                        <option value="{{ $sup->id }}" {{ request('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors w-full sm:w-auto cursor-pointer">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'supplier_id']))
                    <a href="{{ route('purchase-orders.index') }}" class="rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors w-full sm:w-auto text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <!-- Orders Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">PO Number</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Order Date & ETA</th>
                            <th scope="col" class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Total Value</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($purchaseOrders as $po)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
                                        {{ $po->po_number }}
                                    </a>
                                    <div class="text-xs text-slate-400 mt-0.5">By {{ $po->creator?->name ?? 'System' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <div class="font-bold text-slate-900">{{ $po->supplier?->name ?? 'Unassigned Supplier' }}</div>
                                    <div class="text-slate-400 font-mono">{{ $po->supplier?->phone ?? $po->supplier?->email ?? '' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <div class="text-slate-700">Ordered: {{ $po->order_date ? $po->order_date->format('d M Y') : '—' }}</div>
                                    <div class="text-slate-400">ETA: {{ $po->expected_delivery_date ? $po->expected_delivery_date->format('d M Y') : 'Open' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right text-xs font-mono font-bold text-slate-900">
                                    RM {{ number_format($po->total_amount, 2) }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    @if($po->status === 'draft')
                                        <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-1 text-xs font-medium text-slate-700">Draft</span>
                                    @elseif($po->status === 'ordered')
                                        <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700 border border-indigo-200">Ordered</span>
                                    @elseif($po->status === 'received')
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Received</span>
                                    @elseif($po->status === 'cancelled')
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-medium text-rose-700">Cancelled</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-xs">
                                    <a href="{{ route('purchase-orders.show', $po->id) }}" class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-1.5 font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                                        View Details &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-sm text-slate-400 italic">
                                    No purchase orders recorded. Click "Create Purchase Order" or "Auto-Draft from Low Stock" to begin.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchaseOrders->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $purchaseOrders->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
