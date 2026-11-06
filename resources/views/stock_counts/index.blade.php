<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Cycle Counts & Stock Audits</h1>
                <p class="mt-1 text-sm text-slate-500">Conduct physical rack-by-rack stocktaking, barcode verification, and discrepancy/shrinkage reconciliation.</p>
            </div>
            <div class="mt-4 sm:mt-0">
                @if(Auth::user()->hasRole('admin', 'warehouse'))
                    <a href="{{ route('stock-counts.create') }}" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        <span>Start Stock Count Session</span>
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

        <!-- Metric Badges -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">In-Progress Counts</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $inProgressCount }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">Completed & Reconciled</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $completedCount }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-sm">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Audit Sessions</p>
                <p class="text-2xl font-bold text-slate-900 mt-1">{{ $totalSessionsCount }}</p>
            </div>
        </div>

        <!-- Filter Toolbar -->
        <form method="GET" action="{{ route('stock-counts.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs flex flex-col sm:flex-row items-center gap-3">
            <div class="w-full sm:w-48">
                <select name="status" class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Statuses</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>

            <div class="w-full sm:w-64">
                <select name="location_id" class="block w-full rounded-xl border border-slate-200 px-3 py-2 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Racks / Warehouses</option>
                    @foreach($locations as $loc)
                        <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->full_path }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors w-full sm:w-auto cursor-pointer">
                    Filter
                </button>
                @if(request()->anyFilled(['status', 'location_id']))
                    <a href="{{ route('stock-counts.index') }}" class="rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors w-full sm:w-auto text-center">
                        Clear
                    </a>
                @endif
            </div>
        </form>

        <!-- Sessions Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Audit Session</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Target Rack / Location</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Manifest Count</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($stockCounts as $sc)
                            @php
                                $matched = $sc->items->where('status', 'matched')->count();
                                $total = $sc->items->count();
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="whitespace-nowrap py-4 pl-6 pr-3 text-sm">
                                    <a href="{{ route('stock-counts.show', $sc->id) }}" class="font-mono font-bold text-indigo-600 hover:text-indigo-800">
                                        {{ $sc->count_number }}
                                    </a>
                                    <div class="text-xs text-slate-400 mt-0.5">Started {{ $sc->started_at->format('d M Y, h:i A') }} by {{ $sc->starter?->name ?? 'System' }}</div>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-xs font-medium text-slate-700">
                                    {{ $sc->location ? $sc->location->full_path : 'Full Warehouse / All Internal Racks' }}
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center text-xs">
                                    <span class="font-bold text-slate-900">{{ $matched }} / {{ $total }} verified</span>
                                </td>
                                <td class="whitespace-nowrap px-4 py-4 text-center">
                                    @if($sc->status === 'in_progress')
                                        <span class="inline-flex items-center rounded-md bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700 border border-amber-200">
                                            Scanning In Progress
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700 border border-emerald-200">
                                            Completed & Reconciled
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap py-4 pl-3 pr-6 text-right text-xs">
                                    <a href="{{ route('stock-counts.show', $sc->id) }}" class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-1.5 font-bold text-slate-700 hover:bg-slate-100 transition-colors">
                                        Open Session &rarr;
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-sm text-slate-400 italic">
                                    No stocktaking sessions recorded. Click "Start Stock Count Session" to perform a physical inventory audit.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($stockCounts->hasPages())
                <div class="px-6 py-4 border-t border-slate-100">
                    {{ $stockCounts->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
