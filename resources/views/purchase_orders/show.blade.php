<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div class="flex items-center gap-3">
                <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 shadow-2xs">
                    &larr;
                </a>
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold font-mono text-slate-900 tracking-tight">{{ $purchaseOrder->po_number }}</h1>
                        @if($purchaseOrder->status === 'draft')
                            <span class="inline-flex items-center rounded-md bg-slate-100 px-2 py-0.5 text-xs font-semibold text-slate-700">Draft</span>
                        @elseif($purchaseOrder->status === 'ordered')
                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200">Ordered & Awaiting Delivery</span>
                        @elseif($purchaseOrder->status === 'received')
                            <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">Received & Stocked</span>
                        @elseif($purchaseOrder->status === 'cancelled')
                            <span class="inline-flex items-center rounded-md bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700">Cancelled</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">Created by {{ $purchaseOrder->creator?->name ?? 'System' }} on {{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</p>
                </div>
            </div>

            <!-- Action Controls -->
            <div class="mt-4 sm:mt-0 flex flex-wrap items-center gap-2">
                <button type="button" onclick="window.print()" class="rounded-xl bg-white border border-slate-200 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-2xs transition-colors cursor-pointer">
                    Print Order
                </button>

                @if(Auth::user()->hasRole('admin', 'warehouse'))
                    @if($purchaseOrder->status === 'draft')
                        <form action="{{ route('purchase-orders.update_status', $purchaseOrder->id) }}" method="POST" class="inline">
                            @csrf
                            <input type="hidden" name="status" value="ordered">
                            <button type="submit" class="rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                                Mark as Sent / Ordered &rarr;
                            </button>
                        </form>
                    @endif

                    @if($purchaseOrder->status !== 'received' && $purchaseOrder->status !== 'cancelled')
                        <form action="{{ route('purchase-orders.update_status', $purchaseOrder->id) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this Purchase Order?')">
                            @csrf
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="rounded-xl bg-rose-50 border border-rose-200 px-3 py-2 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer">
                                Cancel PO
                            </button>
                        </form>
                    @endif
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

        <!-- Receive Goods Box (When PO is ordered) -->
        @if(Auth::user()->hasRole('admin', 'warehouse') && $purchaseOrder->status === 'ordered')
            <div class="bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200/80 rounded-2xl p-6 shadow-sm">
                <div class="sm:flex sm:items-center sm:justify-between gap-6">
                    <div class="space-y-1">
                        <h3 class="text-base font-bold text-emerald-950 flex items-center gap-2">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Shipment Arrived? Receive Goods into Warehouse</span>
                        </h3>
                        <p class="text-xs text-emerald-700">Receive all ordered hardware units directly into your warehouse rack. Serial numbers will be automatically registered and cataloged.</p>
                    </div>

                    <form action="{{ route('purchase-orders.receive', $purchaseOrder->id) }}" method="POST" class="mt-4 sm:mt-0 flex flex-col sm:flex-row items-center gap-3 shrink-0">
                        @csrf
                        <div class="w-full sm:w-64">
                            <select name="location_id" required class="block w-full rounded-xl border border-emerald-300 bg-white px-3 py-2 text-xs font-medium text-slate-900 shadow-2xs">
                                <option value="">-- Target Warehouse Rack --</option>
                                @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}">{{ $loc->full_path }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" onclick="return confirm('Receive all units into selected warehouse rack?')" class="rounded-xl bg-emerald-700 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-emerald-600 cursor-pointer transition-colors w-full sm:w-auto">
                            Receive & Stock In
                        </button>
                    </form>
                </div>
            </div>
        @endif

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Supplier Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vendor / Supplier</h3>
                <div>
                    <p class="text-base font-bold text-slate-900">{{ $purchaseOrder->supplier?->name ?? 'Unspecified' }}</p>
                    <p class="text-xs text-slate-500 mt-0.5">{{ $purchaseOrder->supplier?->contact_person }}</p>
                </div>
                <div class="text-xs text-slate-600 space-y-1 font-mono">
                    @if($purchaseOrder->supplier?->email)
                        <p>{{ $purchaseOrder->supplier->email }}</p>
                    @endif
                    @if($purchaseOrder->supplier?->phone)
                        <p>{{ $purchaseOrder->supplier->phone }}</p>
                    @endif
                    @if($purchaseOrder->supplier?->address)
                        <p class="text-slate-500 font-sans not-italic mt-2">{{ $purchaseOrder->supplier->address }}</p>
                    @endif
                </div>
            </div>

            <!-- Schedule Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Order Schedule</h3>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400">Order Placed:</span>
                        <span class="font-bold text-slate-900 ml-1">{{ $purchaseOrder->order_date ? $purchaseOrder->order_date->format('d M Y') : '—' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400">Expected Arrival:</span>
                        <span class="font-bold text-slate-900 ml-1">{{ $purchaseOrder->expected_delivery_date ? $purchaseOrder->expected_delivery_date->format('d M Y') : 'Open Term' }}</span>
                    </div>
                    @if($purchaseOrder->notes)
                        <div class="pt-2 border-t border-slate-100">
                            <span class="text-slate-400">Terms / Notes:</span>
                            <p class="text-slate-700 italic mt-0.5">{{ $purchaseOrder->notes }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Total Valuation Card -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-3 flex flex-col justify-between">
                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Purchase Order Amount</h3>
                    <p class="text-3xl font-bold font-mono text-indigo-700 mt-2">RM {{ number_format($purchaseOrder->total_amount, 2) }}</p>
                    <p class="text-xs text-slate-500 mt-1">{{ $purchaseOrder->items->count() }} line items ordered</p>
                </div>
            </div>
        </div>

        <!-- Ordered Items Table -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-base font-bold text-slate-900">Equipment Manifest</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Product Model</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Ordered Qty</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Received Qty</th>
                            <th scope="col" class="py-3.5 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Unit Cost (RM)</th>
                            <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Subtotal (RM)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @foreach($purchaseOrder->items as $item)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                    <div class="font-bold text-slate-900">{{ $item->product?->name }}</div>
                                    <div class="font-mono text-xs text-slate-500">{{ $item->product?->sku }} · {{ $item->product?->brand ?? 'General' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center font-bold text-slate-900 text-sm">
                                    {{ $item->quantity_ordered }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center text-sm">
                                    <span class="inline-flex items-center rounded-md {{ $item->quantity_received >= $item->quantity_ordered ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-700' }} px-2 py-0.5 text-xs font-bold">
                                        {{ $item->quantity_received }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-right font-mono text-slate-700 text-sm">
                                    RM {{ number_format($item->unit_cost, 2) }}
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right font-mono font-bold text-slate-900 text-sm">
                                    RM {{ number_format($item->total_cost, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-slate-50/75 border-t border-slate-100 font-bold">
                        <tr>
                            <td colspan="4" class="py-4 pl-6 text-right text-sm text-slate-700">Total Purchase Order Value:</td>
                            <td class="py-4 pl-3 pr-6 text-right font-mono text-lg text-indigo-700">
                                RM {{ number_format($purchaseOrder->total_amount, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
