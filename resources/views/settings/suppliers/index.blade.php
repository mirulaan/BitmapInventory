<x-app-layout>
    <div class="space-y-6">
        @include('settings._nav', [
            'title' => 'Equipment & Hardware Suppliers',
            'description' => 'Manage authorized hardware distributors, equipment vendors, and procurement contacts.'
        ])

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

        @php
            $canManage = Auth::user()->hasRole('admin', 'warehouse');
        @endphp

        <div class="grid grid-cols-1 {{ $canManage ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6">
            
            <!-- Left Side: Suppliers Table -->
            <div class="{{ $canManage ? 'lg:col-span-2' : 'lg:col-span-1' }} space-y-4">
                
                <!-- Search Toolbar -->
                <form method="GET" action="{{ route('suppliers.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-3 shadow-2xs flex items-center gap-3">
                    <div class="flex-1 relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search supplier name, contact, email..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-1.5 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors cursor-pointer">
                        Search
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('suppliers.index') }}" class="rounded-xl bg-white border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                            Clear
                        </a>
                    @endif
                </form>

                <!-- Table Card -->
                <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-100">
                            <thead class="bg-slate-50/75">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Supplier Name</th>
                                    <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Contact & Phone</th>
                                    <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Purchase Orders</th>
                                    @if($canManage)
                                        <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($suppliers as $supplier)
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                            <div class="font-bold text-slate-900">{{ $supplier->name }}</div>
                                            @if($supplier->email)
                                                <div class="text-xs text-slate-400 font-mono">{{ $supplier->email }}</div>
                                            @endif
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-xs">
                                            <div class="font-semibold text-slate-700">{{ $supplier->contact_person ?? '—' }}</div>
                                            <div class="text-slate-400 font-mono">{{ $supplier->phone ?? '—' }}</div>
                                        </td>
                                        <td class="whitespace-nowrap px-4 py-4 text-center">
                                            <span class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-bold text-indigo-700">
                                                {{ $supplier->purchase_orders_count }} POs
                                            </span>
                                        </td>
                                        @if($canManage)
                                            <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-xs">
                                                <div class="flex items-center justify-end gap-2">
                                                    <a href="{{ route('suppliers.edit', $supplier->id) }}" class="rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1 text-xs font-semibold text-slate-700 hover:bg-slate-100 transition-colors">
                                                        Edit
                                                    </a>
                                                    @if(Auth::user()->isAdmin())
                                                        <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to remove this supplier?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="rounded-lg bg-rose-50 border border-rose-200 px-2.5 py-1 text-xs font-semibold text-rose-700 hover:bg-rose-100 transition-colors cursor-pointer">
                                                                Delete
                                                            </button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $canManage ? 4 : 3 }}" class="py-10 text-center text-sm text-slate-400 italic">
                                            No suppliers found. Create your first supplier to start issuing Purchase Orders.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($suppliers->hasPages())
                        <div class="px-6 py-4 border-t border-slate-100">
                            {{ $suppliers->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Side: Fast Quick-Add Form -->
            @if($canManage)
                <div class="lg:col-span-1">
                    <div class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm space-y-5 sticky top-6">
                        <div>
                            <h3 class="text-base font-bold text-slate-900">Add New Supplier</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Register an authorized distributor or hardware vendor.</p>
                        </div>

                        <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Supplier Name <span class="text-rose-500">*</span></label>
                                <input type="text" id="name" name="name" required value="{{ old('name') }}" placeholder="e.g. Basler Asia Pte Ltd" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </div>

                            <div>
                                <label for="contact_person" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Contact Person (Optional)</label>
                                <input type="text" id="contact_person" name="contact_person" value="{{ old('contact_person') }}" placeholder="e.g. David Wong" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="sales@vendor.com" class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                                <div>
                                    <label for="phone" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Phone</label>
                                    <input type="text" id="phone" name="phone" value="{{ old('phone') }}" placeholder="+6012-3456789" class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-xs text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                </div>
                            </div>

                            <div>
                                <label for="address" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Office / Warehouse Address</label>
                                <textarea id="address" name="address" rows="2" placeholder="Full address or country/city..." class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-xs text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('address') }}</textarea>
                            </div>

                            <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-colors">
                                Save Supplier
                            </button>
                        </form>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
