<x-app-layout>
    <div class="space-y-6">
        @include('settings._nav', [
            'title' => 'Locations & Sites',
            'description' => 'Manage warehouse branches, storage racks, and external deployment sites.'
        ])

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

        <!-- Filter & Action Toolbar -->
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <form method="GET" action="{{ route('locations.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-3 shadow-2xs flex flex-col sm:flex-row gap-3 items-center flex-1">
                <div class="flex-1 w-full relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search rack name, floor, or site..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-1.5 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <div class="w-full sm:w-44">
                    <select name="type" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">All Types</option>
                        <option value="internal" {{ request('type') == 'internal' ? 'selected' : '' }}>Internal Warehouse</option>
                        <option value="client_site" {{ request('type') == 'client_site' ? 'selected' : '' }}>Client Site</option>
                    </select>
                </div>

                <div class="w-full sm:w-36">
                    <select name="branch" onchange="this.form.submit()" class="block w-full rounded-xl border border-slate-200 px-3 py-1.5 text-xs sm:text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <option value="">All Branches</option>
                        <option value="KL" {{ request('branch') == 'KL' ? 'selected' : '' }}>KL</option>
                        <option value="Penang" {{ request('branch') == 'Penang' ? 'selected' : '' }}>Penang</option>
                    </select>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors cursor-pointer w-full sm:w-auto">
                        Filter
                    </button>
                    @if(request()->anyFilled(['search', 'type', 'branch']))
                        <a href="{{ route('locations.index') }}" class="rounded-xl bg-white border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors w-full sm:w-auto text-center">
                            Clear
                        </a>
                    @endif
                </div>
            </form>

            @if(Auth::user()->hasRole('admin', 'warehouse'))
            <a href="{{ route('locations.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Add Location</span>
            </a>
            @endif
        </div>

        <!-- Locations Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Location Identifier</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Facility Type</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Branch / Floor</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Active Units</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($locations as $location)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Location Name & Path -->
                                <td class="py-4 pl-6 pr-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 border {{ $location->type === 'internal' ? 'bg-indigo-50 border-indigo-100 text-indigo-600' : 'bg-purple-50 border-purple-100 text-purple-600' }}">
                                            @if($location->type === 'internal')
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 14.25h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6m-13.5-3a3 3 0 01-3-3m3 3h13.5m-13.5 0a3 3 0 01-3-3m3 3a3 3 0 100 6h13.5a3 3 0 100-6" />
                                                </svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 21a3 3 0 003-3v-4.5a3 3 0 00-3-3h-1.5V6a3 3 0 00-3-3H9a3 3 0 00-3 3v4.5H4.5a3 3 0 00-3 3V18a3 3 0 003 3h15z" />
                                                </svg>
                                            @endif
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-slate-900">{{ $location->name }}</div>
                                            <div class="font-mono text-xs text-slate-400 mt-0.5">{{ $location->full_path }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Facility Type Badge -->
                                <td class="py-4 px-4 whitespace-nowrap text-xs">
                                    @if($location->type === 'internal')
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-bold text-blue-700 border border-blue-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            Internal Warehouse
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-purple-50 px-2.5 py-1 text-xs font-bold text-purple-700 border border-purple-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span>
                                            Client Site
                                        </span>
                                    @endif
                                </td>

                                <!-- Branch & Floor -->
                                <td class="py-4 px-4 whitespace-nowrap text-xs">
                                    @if($location->type === 'internal')
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-semibold text-slate-700 bg-slate-100 px-2 py-0.5 rounded border border-slate-200/60">{{ $location->branch ?? 'N/A' }}</span>
                                            <span class="text-slate-400">&bull;</span>
                                            <span class="text-slate-600">{{ $location->floor ?? 'N/A' }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">External Field Site</span>
                                    @endif
                                </td>

                                <!-- Active Units Count -->
                                <td class="py-4 px-4 whitespace-nowrap text-center">
                                    @if($location->active_units_count > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-3 py-1 text-xs font-bold text-emerald-700 border border-emerald-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            {{ $location->active_units_count }} Units
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">0 Units</span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap text-xs">
                                    @if(Auth::user()->hasRole('admin', 'warehouse'))
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('locations.edit', $location->id) }}" class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 transition-colors">
                                            Edit
                                        </a>
                                        <form action="{{ route('locations.destroy', $location->id) }}" method="POST" onsubmit="return confirm('Delete location {{ $location->name }}?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200/80 hover:bg-rose-100 transition-colors cursor-pointer" {{ $location->active_units_count > 0 ? 'title=Cannot-delete-location-with-active-inventory' : '' }}>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                    @else
                                        <span class="text-xs text-slate-400 italic">Read-only</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-12 text-center text-slate-500 text-sm">
                                    No locations configured matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($locations->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3">
                    {{ $locations->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
