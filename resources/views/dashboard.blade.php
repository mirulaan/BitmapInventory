<x-app-layout>
    <div class="space-y-6 pb-8">
        
        <!-- Header Banner & Action Bar -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold tracking-tight text-slate-900">Operations Command Center</h1>
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700 ring-1 ring-inset ring-emerald-600/20">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Telemetry
                    </span>
                </div>
                <p class="mt-1 text-sm text-slate-500">Real-time serialized hardware tracking, proof-of-concept loans, and inventory movements.</p>
            </div>
            
            <!-- Quick Actions Group -->
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('inventory.audit') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:text-slate-900 transition-all cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Audit Ledger
                </a>

                <a href="{{ route('inventory.index') }}" class="inline-flex items-center justify-center rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 hover:text-slate-900 transition-all cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                    </svg>
                    All Assets
                </a>

                @if(Auth::user()->hasRole('admin', 'warehouse'))
                <a href="{{ route('inventory.create') }}" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2 text-xs font-semibold text-white shadow-sm hover:bg-indigo-500 hover:shadow transition-all cursor-pointer">
                    <svg class="w-4 h-4 mr-1.5 text-white/90" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Receive Hardware
                </a>
                @endif
            </div>
        </div>

        <!-- KPI Metrics Grid (4 High-Impact Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- 1. Total Active Units -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Hardware</p>
                    <div class="p-2.5 bg-blue-50 text-blue-600 rounded-xl group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ number_format($totalHardware) }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span class="inline-flex items-center gap-1 text-blue-600">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        {{ number_format($inStock) }} In Warehouse
                    </span>
                    <span class="inline-flex items-center gap-1 text-amber-600">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        {{ number_format($onLoan) }} Deployed
                    </span>
                </div>
            </div>

            <!-- 2. Fleet Valuation -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Active Fleet Value</p>
                    <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2">
                    <p class="text-3xl font-extrabold text-slate-900 tracking-tight">RM {{ number_format($totalFleetValue, 2) }}</p>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                    <span>Physical serialized assets</span>
                    <span class="text-slate-400">{{ number_format($soldCount) }} units sold</span>
                </div>
            </div>

            <!-- 3. Field Deployment Utilization -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="flex items-center justify-between">
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Fleet Utilization</p>
                    <div class="p-2.5 bg-indigo-50 text-indigo-600 rounded-xl group-hover:scale-105 transition-transform">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-2 flex items-baseline gap-2">
                    <p class="text-3xl font-extrabold text-slate-900 tracking-tight">{{ $statusDistribution['loan'] }}%</p>
                    <span class="text-xs font-semibold text-slate-500">In Active Loan</span>
                </div>
                <div class="mt-3 pt-3 border-t border-slate-100 space-y-1.5">
                    <div class="w-full h-2 bg-slate-100 rounded-full overflow-hidden flex">
                        <div class="h-full bg-blue-500 transition-all duration-500" style="width: {{ $statusDistribution['stock'] }}%"></div>
                        <div class="h-full bg-amber-400 transition-all duration-500" style="width: {{ $statusDistribution['loan'] }}%"></div>
                    </div>
                    <div class="flex justify-between text-[11px] text-slate-400 font-medium">
                        <span>{{ $statusDistribution['stock'] }}% Stock</span>
                        <span>{{ $statusDistribution['loan'] }}% Loan</span>
                    </div>
                </div>
            </div>

            <!-- 4. Loan Compliance & Overdue Alert -->
            @if($overdueCount > 0)
                <div class="bg-rose-50/70 border border-rose-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-rose-700 uppercase tracking-wider">Loan Governance</p>
                        <div class="p-2.5 bg-rose-100 text-rose-700 rounded-xl group-hover:scale-105 transition-transform animate-pulse">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-3xl font-extrabold text-rose-700 tracking-tight">{{ $overdueCount }} Overdue</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-rose-200/80 flex items-center justify-between text-xs text-rose-800 font-medium">
                        <span>Requires immediate action</span>
                        @if($dueSoonCount > 0)
                            <span class="text-amber-700 font-semibold">{{ $dueSoonCount }} due soon</span>
                        @endif
                    </div>
                </div>
            @elseif($dueSoonCount > 0)
                <div class="bg-amber-50/70 border border-amber-200 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-amber-700 uppercase tracking-wider">Loan Governance</p>
                        <div class="p-2.5 bg-amber-100 text-amber-700 rounded-xl group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-3xl font-extrabold text-amber-800 tracking-tight">{{ $dueSoonCount }} Due Soon</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-amber-200/80 flex items-center justify-between text-xs text-amber-800 font-medium">
                        <span>Expiring within 14 days</span>
                        <span class="text-emerald-700 font-semibold">0 overdue</span>
                    </div>
                </div>
            @else
                <div class="bg-white border border-slate-200/80 rounded-2xl p-5 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Loan Governance</p>
                        <div class="p-2.5 bg-emerald-50 text-emerald-600 rounded-xl group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                            </svg>
                        </div>
                    </div>
                    <div class="mt-2">
                        <p class="text-3xl font-extrabold text-emerald-600 tracking-tight">100% On Term</p>
                    </div>
                    <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500 font-medium">
                        <span>All active loans compliant</span>
                        <span class="text-emerald-600 font-semibold">Zero flags</span>
                    </div>
                </div>
            @endif

        </div>

        <!-- Main Content Area: Split 2-Column Hub -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <!-- Left Column: Live Transfer & Movement Ledger (8 cols) -->
            <div class="lg:col-span-8 space-y-6">
                
                <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div>
                            <h2 class="text-base font-bold text-slate-900 flex items-center gap-2">
                                <span class="h-2 w-2 rounded-full bg-indigo-500"></span>
                                Live Movement Ledger
                            </h2>
                            <p class="text-xs text-slate-500 mt-0.5">Real-time audit log of hardware receiving, dispatches, returns, and sales.</p>
                        </div>
                        <a href="{{ route('inventory.audit') }}" class="inline-flex items-center text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors group">
                            View Full Ledger
                            <svg class="w-3.5 h-3.5 ml-1 group-hover:translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                            </svg>
                        </a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/75">
                                <tr>
                                    <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Event Type</th>
                                    <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Hardware / S/N</th>
                                    <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Destination</th>
                                    <th scope="col" class="py-3 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reference / By</th>
                                    <th scope="col" class="py-3 px-4 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Time</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($recentActivity as $log)
                                    @php
                                        $typeInfo = match($log->transaction_type) {
                                            'stock_in' => [
                                                'bg' => 'bg-blue-50 text-blue-700 ring-blue-700/10',
                                                'label' => 'Stock In',
                                                'dot' => 'bg-blue-500'
                                            ],
                                            'dispatch_loan' => [
                                                'bg' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
                                                'label' => 'PoC Loan Out',
                                                'dot' => 'bg-amber-500'
                                            ],
                                            'return_loan' => [
                                                'bg' => 'bg-teal-50 text-teal-700 ring-teal-600/20',
                                                'label' => 'Loan Returned',
                                                'dot' => 'bg-teal-500'
                                            ],
                                            'convert_to_sale' => [
                                                'bg' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                'label' => 'Loan to Sale',
                                                'dot' => 'bg-emerald-500'
                                            ],
                                            'direct_sale' => [
                                                'bg' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
                                                'label' => 'Direct Sale',
                                                'dot' => 'bg-emerald-500'
                                            ],
                                            default => [
                                                'bg' => 'bg-slate-50 text-slate-700 ring-slate-600/10',
                                                'label' => ucwords(str_replace('_', ' ', $log->transaction_type)),
                                                'dot' => 'bg-slate-400'
                                            ]
                                        };
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <!-- Event Type Badge -->
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold ring-1 ring-inset {{ $typeInfo['bg'] }}">
                                                <span class="w-1.5 h-1.5 rounded-full {{ $typeInfo['dot'] }}"></span>
                                                {{ $typeInfo['label'] }}
                                            </span>
                                        </td>

                                        <!-- Hardware Name & S/N -->
                                        <td class="py-3.5 px-4 whitespace-nowrap">
                                            @if($log->inventoryItem?->product)
                                                <a href="{{ route('inventory.product', $log->inventoryItem->product_id) }}" class="font-semibold text-slate-900 hover:text-indigo-600 transition-colors block text-sm">
                                                    {{ $log->inventoryItem->product->name }}
                                                </a>
                                            @else
                                                <span class="font-semibold text-slate-900 text-sm">Hardware Asset</span>
                                            @endif
                                            <div class="font-mono text-xs text-slate-500 mt-0.5 inline-flex items-center gap-1 bg-slate-100/70 px-1.5 py-0.5 rounded">
                                                {{ $log->inventoryItem?->serial_number ?? 'Bulk Item (Qty: '.$log->quantity.')' }}
                                            </div>
                                        </td>

                                        <!-- Destination -->
                                        <td class="py-3.5 px-4 whitespace-nowrap text-sm">
                                            @if(in_array($log->transaction_type, ['stock_in', 'return_loan']))
                                                <div class="inline-flex items-center gap-1.5 text-slate-700">
                                                    <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                    </svg>
                                                    <span class="font-medium text-slate-900">{{ $log->toLocation?->name ?? 'Main Warehouse' }}</span>
                                                </div>
                                            @elseif(in_array($log->transaction_type, ['dispatch_loan', 'direct_sale', 'convert_to_sale']))
                                                <div class="inline-flex items-center gap-1.5 text-slate-700">
                                                    <svg class="w-3.5 h-3.5 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                                    </svg>
                                                    <span class="font-medium text-slate-900">{{ $log->client?->company_name ?? $log->toLocation?->name ?? '-' }}</span>
                                                </div>
                                            @else
                                                <span class="text-slate-400">-</span>
                                            @endif
                                        </td>

                                        <!-- Operator & Reference -->
                                        <td class="py-3.5 px-4 whitespace-nowrap text-xs text-slate-500">
                                            <div class="font-medium text-slate-800">
                                                {{ $log->person_in_charge ?? $log->user?->name ?? 'System' }}
                                            </div>
                                            @if($log->loan_form_no)
                                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">Doc: {{ $log->loan_form_no }}</div>
                                            @elseif($log->po_customer)
                                                <div class="text-[11px] text-slate-400 font-mono mt-0.5">PO: {{ $log->po_customer }}</div>
                                            @endif
                                        </td>

                                        <!-- Relative Time -->
                                        <td class="py-3.5 px-4 whitespace-nowrap text-right text-xs font-semibold text-slate-400">
                                            {{ $log->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="py-12 text-center text-sm text-slate-400 font-medium">
                                            No movements recorded yet. Use "+ Receive Hardware" to begin.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Fast Navigation / Management Quick Tiles -->
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <a href="{{ route('products.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group flex flex-col justify-between">
                        <div class="p-2 bg-indigo-50 text-indigo-600 rounded-xl w-fit group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                            </svg>
                        </div>
                        <div class="mt-3">
                            <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Catalog Models</p>
                            <p class="text-xs text-slate-400 mt-0.5">Manage SKUs & pricing</p>
                        </div>
                    </a>

                    <a href="{{ route('clients.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group flex flex-col justify-between">
                        <div class="p-2 bg-blue-50 text-blue-600 rounded-xl w-fit group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                        </div>
                        <div class="mt-3">
                            <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Client Accounts</p>
                            <p class="text-xs text-slate-400 mt-0.5">Enterprise accounts</p>
                        </div>
                    </a>

                    <a href="{{ route('locations.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group flex flex-col justify-between">
                        <div class="p-2 bg-emerald-50 text-emerald-600 rounded-xl w-fit group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                            </svg>
                        </div>
                        <div class="mt-3">
                            <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Locations & Sites</p>
                            <p class="text-xs text-slate-400 mt-0.5">Racks & field sites</p>
                        </div>
                    </a>

                    <a href="{{ route('categories.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm hover:border-indigo-300 hover:shadow-md transition-all group flex flex-col justify-between">
                        <div class="p-2 bg-amber-50 text-amber-600 rounded-xl w-fit group-hover:scale-105 transition-transform">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                        </div>
                        <div class="mt-3">
                            <p class="text-sm font-bold text-slate-900 group-hover:text-indigo-600 transition-colors">Categories</p>
                            <p class="text-xs text-slate-400 mt-0.5">Hardware groupings</p>
                        </div>
                    </a>
                </div>

            </div>

            <!-- Right Column: Priority Focus Widgets (4 cols) -->
            <div class="lg:col-span-4 space-y-6">
                
                <!-- Widget 1: Field Loan Watchlist -->
                <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <span class="p-1 bg-amber-100 text-amber-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Field Loan Watchlist</h3>
                        </div>
                        <span class="text-xs font-extrabold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-full border border-amber-200">
                            {{ $activeLoans->count() }} Deployed
                        </span>
                    </div>

                    <div class="divide-y divide-slate-100 max-h-[380px] overflow-y-auto">
                        @forelse($activeLoans as $loan)
                            <div class="p-4 hover:bg-slate-50 transition-colors space-y-2">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="text-sm font-bold text-slate-900 truncate">{{ $loan->product->name }}</p>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5">{{ $loan->serial_number }}</p>
                                    </div>

                                    <!-- Status Pill -->
                                    @if($loan->is_overdue)
                                        <span class="inline-flex items-center rounded-md bg-rose-50 px-2 py-1 text-xs font-bold text-rose-700 ring-1 ring-inset ring-rose-600/30 shrink-0 animate-pulse">
                                            Overdue ({{ $loan->days_overdue }}d)
                                        </span>
                                    @elseif($loan->is_expiring_soon)
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2 py-1 text-xs font-bold text-amber-800 ring-1 ring-inset ring-amber-600/30 shrink-0">
                                            Due in {{ $loan->days_remaining }}d
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-slate-50 px-2 py-1 text-xs font-medium text-slate-600 ring-1 ring-inset ring-slate-500/20 shrink-0">
                                            Due {{ $loan->end_date_formatted }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex items-center justify-between pt-1 text-xs">
                                    <div class="text-slate-600 flex items-center gap-1 truncate max-w-[170px]">
                                        <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                        </svg>
                                        <span class="truncate font-medium">{{ $loan->client?->company_name ?? 'Client Site' }}</span>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="flex items-center gap-1.5 shrink-0">
                                        <a href="{{ route('inventory.return', $loan->id) }}" class="rounded-lg bg-teal-50 px-2 py-1 text-[11px] font-semibold text-teal-700 border border-teal-200 hover:bg-teal-100 transition-colors" title="Process Return">
                                            Return
                                        </a>
                                        <a href="{{ route('inventory.convert', $loan->id) }}" class="rounded-lg bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors" title="Convert to Sale">
                                            Convert
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">No Active Field Loans</p>
                                <p class="text-xs text-slate-400 mt-0.5">All hardware currently safely stocked in warehouse.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Widget 2: Low Stock Replenishment Alerts -->
                <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                    <div class="p-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <span class="p-1 bg-rose-100 text-rose-700 rounded-lg">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                                </svg>
                            </span>
                            <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Low Stock Watchlist</h3>
                        </div>
                        <span class="text-xs font-semibold text-slate-400">Min Alert Watch</span>
                    </div>

                    <div class="divide-y divide-slate-100">
                        @forelse($lowStockProducts as $product)
                            <a href="{{ route('inventory.product', $product->id) }}" class="p-4 flex items-center justify-between hover:bg-slate-50 transition-colors group block">
                                <div class="min-w-0 pr-3 flex-1">
                                    <p class="text-sm font-semibold text-slate-900 truncate group-hover:text-indigo-600 transition-colors">{{ $product->name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-[11px] text-slate-400 font-mono">{{ $product->sku }}</span>
                                        @if($product->brand)
                                            <span class="text-[10px] bg-slate-100 text-slate-700 px-1.5 py-0.5 rounded font-bold">{{ $product->brand }}</span>
                                        @endif
                                        <span class="text-[10px] bg-indigo-50 text-indigo-700 px-1.5 py-0.5 rounded font-medium">{{ $product->category }}</span>
                                    </div>
                                </div>
                                <div class="shrink-0 text-right flex items-center gap-3">
                                    <div class="text-right">
                                        <span class="block text-base font-black leading-none {{ $product->in_stock_qty == 0 ? 'text-rose-600' : 'text-amber-600' }}">
                                            {{ $product->in_stock_qty }}
                                        </span>
                                        <span class="block text-[10px] text-slate-400 font-semibold mt-1">
                                            @if($product->min_stock_level > 0)
                                                Min: {{ $product->min_stock_level }}
                                            @else
                                                In Stock
                                            @endif
                                        </span>
                                    </div>
                                    <svg class="w-4 h-4 text-slate-300 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                    </svg>
                                </div>
                            </a>
                        @empty
                            <div class="p-6 text-center">
                                <div class="w-9 h-9 bg-emerald-50 text-emerald-600 rounded-full flex items-center justify-center mx-auto mb-2">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                    </svg>
                                </div>
                                <p class="text-sm font-semibold text-slate-700">Healthy Stock Reserve</p>
                                <p class="text-xs text-slate-400 mt-0.5">All product lines meet safety stock margins.</p>
                            </div>
                        @endforelse
                    </div>

                    @if($lowStockProducts->isNotEmpty())
                        <div class="p-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between gap-2">
                            @if(Auth::user()->hasRole('admin', 'warehouse'))
                            <form method="POST" action="{{ route('purchase-orders.auto_draft') }}">
                                @csrf
                                <button type="submit" class="text-xs font-bold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 px-2.5 py-1.5 rounded-lg transition-colors inline-flex items-center gap-1.5 cursor-pointer">
                                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m3.75 9v6m3-3H9m1.5-12H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                    </svg>
                                    Auto-Draft PO
                                </button>
                            </form>
                            <a href="{{ route('inventory.create') }}" class="text-xs font-bold text-slate-700 hover:text-slate-900 transition-colors inline-flex items-center gap-1">
                                + Manual Receive
                            </a>
                            @else
                            <span class="text-xs text-rose-600 font-semibold w-full text-center">Immediate stock replenishment required</span>
                            @endif
                        </div>
                    @endif
                </div>

            </div>

        </div>

    </div>
</x-app-layout>
