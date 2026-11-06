<x-app-layout>
    <div class="space-y-6">
        
        <!-- Header & Export Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-bold uppercase tracking-wider text-indigo-600 bg-indigo-50 border border-indigo-100 px-2.5 py-0.5 rounded-lg">
                        <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Immutable Audit Ledger
                    </span>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Audit & Transfers Log</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Comprehensive, immutable audit trail of every stock-in, field loan, return, and sales conversion.</p>
            </div>

            <!-- Working CSV Export Button -->
            <div class="flex items-center gap-2 shrink-0">
                <a href="{{ route('inventory.audit.export', request()->query()) }}" class="inline-flex items-center gap-2 rounded-xl bg-white border border-slate-300 px-4 py-2.5 text-xs font-bold text-slate-700 shadow-2xs hover:bg-slate-50 hover:text-slate-900 transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-emerald-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    <span>Export CSV</span>
                </a>
            </div>
        </div>

        <!-- KPI Metrics Ribbon -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Events -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-slate-100 flex items-center justify-center text-slate-700 shrink-0">
                    <svg class="w-5 h-5 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 010 3.75H5.625a1.875 1.875 0 010-3.75z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-black text-slate-900 leading-tight">{{ number_format($totalTransactions) }}</p>
                    <p class="text-2xs font-bold text-slate-400 uppercase tracking-wider mt-0.5">Total Audit Events</p>
                </div>
            </div>

            <!-- Stock Ins -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center text-blue-700 shrink-0">
                    <svg class="w-5 h-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-black text-slate-900 leading-tight">{{ number_format($stockInCount) }}</p>
                    <p class="text-2xs font-bold text-blue-600 uppercase tracking-wider mt-0.5">Stock In Receipts</p>
                </div>
            </div>

            <!-- Loans Out -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-amber-50 flex items-center justify-center text-amber-700 shrink-0">
                    <svg class="w-5 h-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-black text-slate-900 leading-tight">{{ number_format($loanOutCount) }}</p>
                    <p class="text-2xs font-bold text-amber-600 uppercase tracking-wider mt-0.5">PoC Loans Out</p>
                </div>
            </div>

            <!-- Sales / Conversions -->
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center text-emerald-700 shrink-0">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0115.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 013 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 00-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 01-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 003 15h-.75M15 10.5a3 3 0 11-6 0 3 3 0 016 0zm3 0h.008v.008H18V10.5zm-12 0h.008v.008H6V10.5z" />
                    </svg>
                </div>
                <div>
                    <p class="text-xl font-black text-slate-900 leading-tight">{{ number_format($salesCount) }}</p>
                    <p class="text-2xs font-bold text-emerald-600 uppercase tracking-wider mt-0.5">Sales / Conversions</p>
                </div>
            </div>
        </div>

        <!-- Filter & Search Toolbar -->
        <form method="GET" action="{{ route('inventory.audit') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm flex flex-col gap-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3 items-center">
                
                <!-- Search Input -->
                <div class="sm:col-span-2 md:col-span-3 lg:col-span-2 relative min-w-0">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search S/N, model, brand, PO, Form #, PIC, user..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2.5 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <!-- Transaction Type Filter -->
                <div class="min-w-0">
                    <select name="transaction_type" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">All Event Types</option>
                        <option value="stock_in" {{ request('transaction_type') == 'stock_in' ? 'selected' : '' }}>Stock In</option>
                        <option value="dispatch_loan" {{ request('transaction_type') == 'dispatch_loan' ? 'selected' : '' }}>Loan Out</option>
                        <option value="return_loan" {{ request('transaction_type') == 'return_loan' ? 'selected' : '' }}>Loan Returned</option>
                        <option value="convert_to_sale" {{ request('transaction_type') == 'convert_to_sale' ? 'selected' : '' }}>Loan to Sale</option>
                        <option value="direct_sale" {{ request('transaction_type') == 'direct_sale' ? 'selected' : '' }}>Direct Sale</option>
                    </select>
                </div>

                <!-- Facility / Location Filter -->
                <div class="min-w-0">
                    <select name="location_id" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3 py-2.5 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">All Locations</option>
                        @foreach($locations as $loc)
                            <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>
                                {{ $loc->full_path }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date From Filter -->
                <div class="min-w-0">
                    <div class="relative rounded-xl border border-slate-200 bg-white shadow-2xs focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20">
                        <span class="absolute top-1 left-2.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider pointer-events-none">From Date</span>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="block w-full border-0 bg-transparent pt-4 pb-1 px-2.5 text-xs text-slate-900 focus:ring-0">
                    </div>
                </div>

                <!-- Date To Filter -->
                <div class="min-w-0">
                    <div class="relative rounded-xl border border-slate-200 bg-white shadow-2xs focus-within:border-indigo-500 focus-within:ring-2 focus-within:ring-indigo-500/20">
                        <span class="absolute top-1 left-2.5 text-[9px] font-bold text-slate-400 uppercase tracking-wider pointer-events-none">To Date</span>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="block w-full border-0 bg-transparent pt-4 pb-1 px-2.5 text-xs text-slate-900 focus:ring-0">
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                <span class="text-xs text-slate-400 font-medium">Showing filtered immutable log entries.</span>
                <div class="flex items-center gap-2">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors cursor-pointer">
                        Apply Filter
                    </button>
                    @if(request()->anyFilled(['search', 'transaction_type', 'location_id', 'date_from', 'date_to']))
                        <a href="{{ route('inventory.audit') }}" class="rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Clear
                        </a>
                    @endif
                </div>
            </div>
        </form>

        <!-- Audit Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Timestamp / User</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Event Type</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Hardware / S/N</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Movement / Status</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Documentation</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Inspection</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($transactions as $log)
                            @php
                                $typeConfig = match($log->transaction_type) {
                                    'stock_in'        => ['bg' => 'bg-blue-50 text-blue-700 border-blue-200/80', 'dot' => 'bg-blue-500', 'label' => 'Stock In'],
                                    'dispatch_loan'   => ['bg' => 'bg-amber-50 text-amber-700 border-amber-200/80', 'dot' => 'bg-amber-500', 'label' => 'Loan Out'],
                                    'return_loan'     => ['bg' => 'bg-teal-50 text-teal-700 border-teal-200/80', 'dot' => 'bg-teal-500', 'label' => 'Loan Returned'],
                                    'convert_to_sale' => ['bg' => 'bg-purple-50 text-purple-700 border-purple-200/80', 'dot' => 'bg-purple-500', 'label' => 'Loan to Sale'],
                                    'direct_sale'     => ['bg' => 'bg-emerald-50 text-emerald-700 border-emerald-200/80', 'dot' => 'bg-emerald-500', 'label' => 'Direct Sale'],
                                    default           => ['bg' => 'bg-slate-50 text-slate-700 border-slate-200/80', 'dot' => 'bg-slate-400', 'label' => ucwords(str_replace('_', ' ', $log->transaction_type))],
                                };
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Date & User -->
                                <td class="whitespace-nowrap py-4 pl-6 pr-3">
                                    <div class="font-bold text-xs text-slate-900">{{ $log->created_at->format('d M Y') }}</div>
                                    <div class="text-slate-400 text-2xs mt-0.5 flex items-center gap-1 font-medium">
                                        <span>{{ $log->created_at->format('h:i A') }}</span>
                                        <span>&bull;</span>
                                        <span class="text-slate-600">{{ $log->user->name ?? 'System' }}</span>
                                    </div>
                                </td>

                                <!-- Event Badge -->
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold border {{ $typeConfig['bg'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $typeConfig['dot'] }}"></span>
                                        {{ $typeConfig['label'] }}
                                    </span>
                                </td>

                                <!-- Hardware Model & S/N -->
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <div class="flex items-center gap-2">
                                        <div class="font-bold text-slate-900">
                                            {{ $log->item?->product?->name ?? 'Unknown Model' }}
                                        </div>
                                        @if($log->item?->product?->brand)
                                            <span class="text-[10px] font-bold text-slate-600 bg-slate-100 border border-slate-200/80 px-1.5 py-0.5 rounded">
                                                {{ $log->item->product->brand }}
                                            </span>
                                        @endif
                                    </div>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="font-mono text-2xs text-slate-600 bg-slate-100 px-1.5 py-0.5 rounded">
                                            {{ $log->item?->serial_number ?? 'Bulk Item (Qty: '.$log->quantity.')' }}
                                        </span>
                                        @if($log->item?->product?->sku)
                                            <span class="text-2xs text-slate-400 font-mono">{{ $log->item->product->sku }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Movement & Status -->
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    @if($log->from_location_id === $log->to_location_id)
                                        <div class="text-slate-800 font-medium">
                                            <span>At:</span> <strong>{{ $log->toLocation->name ?? 'Facility' }}</strong>
                                        </div>
                                        <div class="text-2xs text-slate-400 flex items-center gap-1 mt-0.5">
                                            <span class="capitalize">{{ $log->from_status ?? 'Initial' }}</span>
                                            <span>&rarr;</span>
                                            <span class="font-bold text-indigo-600 capitalize">{{ $log->to_status }}</span>
                                        </div>
                                    @else
                                        <div class="text-slate-600 flex items-center gap-1.5">
                                            <span class="text-slate-400">From:</span>
                                            <span class="text-slate-700 font-medium">{{ $log->fromLocation->name ?? 'Origin' }}</span>
                                        </div>
                                        <div class="text-slate-900 font-bold flex items-center gap-1.5 mt-0.5">
                                            <span class="text-slate-400 font-normal">To:</span>
                                            <span>{{ $log->toLocation->name ?? 'Destination' }}</span>
                                        </div>
                                    @endif
                                </td>

                                <!-- Documentation & References -->
                                <td class="whitespace-nowrap px-4 py-4 text-xs">
                                    <div class="space-y-1">
                                        @if($log->loan_form_no)
                                            <div class="inline-flex items-center gap-1 font-mono text-2xs bg-amber-50 text-amber-800 border border-amber-200/80 px-1.5 py-0.5 rounded">
                                                <span>LF:</span> <strong>{{ $log->loan_form_no }}</strong>
                                            </div>
                                        @endif
                                        @if($log->po_customer)
                                            <div class="inline-flex items-center gap-1 font-mono text-2xs bg-emerald-50 text-emerald-800 border border-emerald-200/80 px-1.5 py-0.5 rounded">
                                                <span>PO:</span> <strong>{{ $log->po_customer }}</strong>
                                            </div>
                                        @endif
                                        @if($log->person_in_charge)
                                            <div class="text-2xs text-slate-500 flex items-center gap-1">
                                                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                </svg>
                                                <span>{{ $log->person_in_charge }}</span>
                                            </div>
                                        @endif
                                        @if(!$log->loan_form_no && !$log->po_customer && !$log->person_in_charge)
                                            <span class="text-slate-400 text-2xs font-mono">{{ $log->reference_number ?? '-' }}</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Action / Inspection Trigger -->
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-xs">
                                    <button type="button" 
                                        onclick="openAuditInspection({{ json_encode([
                                            'id' => $log->id,
                                            'date' => $log->created_at->format('d M Y, h:i:s A'),
                                            'type' => $typeConfig['label'],
                                            'user' => $log->user->name ?? 'System',
                                            'product' => $log->item?->product?->name ?? 'Unknown',
                                            'brand' => $log->item?->product?->brand ?? '-',
                                            'sku' => $log->item?->product?->sku ?? '-',
                                            'sn' => $log->item?->serial_number ?? 'Bulk Item',
                                            'quantity' => $log->quantity ?? 1,
                                            'from' => $log->fromLocation?->full_path ?? $log->fromLocation?->name ?? 'N/A',
                                            'to' => $log->toLocation?->full_path ?? $log->toLocation?->name ?? 'N/A',
                                            'status_change' => ($log->from_status ? ucfirst($log->from_status) : 'Initial') . ' → ' . ucfirst($log->to_status),
                                            'client' => $log->client?->company_name ?? ($log->toLocation?->type === 'client_site' ? $log->toLocation->name : '-'),
                                            'loan_form' => $log->loan_form_no ?? '-',
                                            'po_cust' => $log->po_customer ?? '-',
                                            'po_supp' => $log->po_supplier ?? '-',
                                            'ref' => $log->reference_number ?? '-',
                                            'pic' => $log->person_in_charge ?? '-',
                                            'notes' => $log->notes ?? '-',
                                            'start_date' => $log->loan_start_date ? $log->loan_start_date->format('d M Y') : '-',
                                            'end_date' => $log->loan_end_date ? $log->loan_end_date->format('d M Y') : '-',
                                        ]) }})"
                                        class="rounded-xl bg-slate-100 hover:bg-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 transition-colors cursor-pointer inline-flex items-center gap-1 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Inspect</span>
                                    </button>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="max-w-xs mx-auto text-center space-y-2">
                                        <div class="w-10 h-10 rounded-full bg-slate-100 text-slate-400 mx-auto flex items-center justify-center">
                                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                            </svg>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-800">No Transactions Found</p>
                                        <p class="text-xs text-slate-500">There are no audit ledger movements recorded matching the current filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($transactions->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>

    </div>

    <!-- Audit Record Inspection Modal -->
    <div id="audit_modal" class="fixed inset-0 z-50 hidden overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white border border-slate-200/80 rounded-3xl shadow-xl max-w-2xl w-full overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
            <!-- Modal Header -->
            <div class="p-6 bg-slate-50/80 border-b border-slate-100 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span id="modal_type_badge" class="px-3 py-1 rounded-full text-xs font-bold border bg-indigo-50 text-indigo-700 border-indigo-200">
                        Event
                    </span>
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Transaction Certificate</h3>
                        <p class="text-xs text-slate-500 font-mono" id="modal_log_id">Log #</p>
                    </div>
                </div>
                <button type="button" onclick="closeAuditInspection()" class="text-slate-400 hover:text-slate-700 rounded-xl p-1.5 hover:bg-slate-100 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Modal Content Grid -->
            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                
                <!-- Timestamp & Operator -->
                <div class="grid grid-cols-2 gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100">
                    <div>
                        <span class="block text-2xs font-bold text-slate-400 uppercase tracking-wider">Timestamp</span>
                        <span class="block text-xs font-bold text-slate-900 mt-0.5" id="modal_date">-</span>
                    </div>
                    <div>
                        <span class="block text-2xs font-bold text-slate-400 uppercase tracking-wider">Authorized Operator</span>
                        <span class="block text-xs font-bold text-slate-900 mt-0.5" id="modal_user">-</span>
                    </div>
                </div>

                <!-- Hardware Unit Specifications -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Hardware Information</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Model Name</span>
                            <span class="block text-xs font-bold text-slate-900 mt-0.5" id="modal_product">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Brand</span>
                            <span class="block text-xs font-bold text-slate-900 mt-0.5" id="modal_brand">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Serial Number</span>
                            <span class="block text-xs font-mono font-bold text-slate-900 mt-0.5" id="modal_sn">-</span>
                        </div>
                    </div>
                </div>

                <!-- Movement Details -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Movement & State Trail</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">From (Origin)</span>
                            <span class="block text-xs font-semibold text-slate-700 mt-0.5" id="modal_from">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">To (Destination)</span>
                            <span class="block text-xs font-bold text-slate-900 mt-0.5" id="modal_to">-</span>
                        </div>
                    </div>
                </div>

                <!-- Commercial / Documentation Trail -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-3">Documentation & Legal Trail</h4>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Client Account</span>
                            <span class="block text-xs font-bold text-slate-900 mt-0.5" id="modal_client">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Loan Form No.</span>
                            <span class="block text-xs font-mono font-bold text-slate-900 mt-0.5" id="modal_loan_form">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Customer PO</span>
                            <span class="block text-xs font-mono font-bold text-slate-900 mt-0.5" id="modal_po_cust">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Supplier PO</span>
                            <span class="block text-xs font-mono text-slate-700 mt-0.5" id="modal_po_supp">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Person-In-Charge (PIC)</span>
                            <span class="block text-xs font-semibold text-slate-900 mt-0.5" id="modal_pic">-</span>
                        </div>
                        <div class="border border-slate-100 p-3 rounded-xl bg-white">
                            <span class="block text-2xs text-slate-400">Loan Schedule</span>
                            <span class="block text-xs text-slate-700 mt-0.5" id="modal_loan_schedule">-</span>
                        </div>
                    </div>
                </div>

                <!-- Internal Notes / Remarks -->
                <div>
                    <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-2">Audit Notes / Remarks</h4>
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-100 text-xs text-slate-700" id="modal_notes">
                        -
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="p-4 sm:px-6 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                <span class="text-2xs text-slate-400 font-medium">Immutable Record &bull; Cannot be altered or purged.</span>
                <button type="button" onclick="closeAuditInspection()" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-2xs hover:bg-slate-800 transition-colors cursor-pointer">
                    Close Inspection
                </button>
            </div>

        </div>
    </div>

    <!-- Modal Interaction Script -->
    <script>
        function openAuditInspection(data) {
            document.getElementById('modal_type_badge').textContent = data.type;
            document.getElementById('modal_log_id').textContent = 'Audit Ledger Event #' + data.id;
            document.getElementById('modal_date').textContent = data.date;
            document.getElementById('modal_user').textContent = data.user;
            
            document.getElementById('modal_product').textContent = data.product;
            document.getElementById('modal_brand').textContent = data.brand;
            document.getElementById('modal_sn').textContent = data.sn;

            document.getElementById('modal_from').textContent = data.from;
            document.getElementById('modal_to').textContent = data.to;

            document.getElementById('modal_client').textContent = data.client;
            document.getElementById('modal_loan_form').textContent = data.loan_form;
            document.getElementById('modal_po_cust').textContent = data.po_cust;
            document.getElementById('modal_po_supp').textContent = data.po_supp;
            document.getElementById('modal_pic').textContent = data.pic;

            if (data.start_date !== '-' || data.end_date !== '-') {
                document.getElementById('modal_loan_schedule').textContent = data.start_date + ' to ' + data.end_date;
            } else {
                document.getElementById('modal_loan_schedule').textContent = 'N/A';
            }

            document.getElementById('modal_notes').textContent = data.notes !== '-' ? data.notes : 'No specific notes recorded for this event.';

            const modal = document.getElementById('audit_modal');
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeAuditInspection() {
            const modal = document.getElementById('audit_modal');
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        // Close on ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeAuditInspection();
            }
        });
    </script>
</x-app-layout>
