<x-app-layout>
    <div class="space-y-6">
        @include('settings._nav', [
            'title' => 'Manufacturer Brands',
            'description' => 'Manage equipment manufacturers, hardware brands, and vendor classifications.'
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

        @php
            $canManage = Auth::user()->hasRole('admin', 'warehouse');
        @endphp

        <div class="grid grid-cols-1 {{ $canManage ? 'lg:grid-cols-3' : 'lg:grid-cols-1' }} gap-6">
            
            <!-- Left Side: Brands Table -->
            <div class="{{ $canManage ? 'lg:col-span-2' : 'lg:col-span-1' }} space-y-4">
                
                <!-- Search Toolbar -->
                <form method="GET" action="{{ route('brands.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-3 shadow-2xs flex items-center gap-3">
                    <div class="flex-1 relative">
                        <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search brand names (e.g. Basler, Cognex)..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-1.5 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>
                    <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors cursor-pointer">
                        Search
                    </button>
                    @if(request()->filled('search'))
                        <a href="{{ route('brands.index') }}" class="rounded-xl bg-white border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
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
                                    <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Manufacturer Brand</th>
                                    <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Catalog Usage</th>
                                    <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 bg-white">
                                @forelse($brands as $brand)
                                    @php
                                        $modelsCount = $brandCounts[$brand->name] ?? 0;
                                    @endphp
                                    <tr class="hover:bg-slate-50/70 transition-colors">
                                        <td class="py-4 pl-6 pr-3 whitespace-nowrap text-sm font-bold text-slate-900">
                                            <div class="flex items-center gap-3">
                                                <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-indigo-600 font-bold text-xs shrink-0">
                                                    {{ strtoupper(substr($brand->name, 0, 1)) }}
                                                </div>
                                                <span>{{ $brand->name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 whitespace-nowrap text-center">
                                            @if($modelsCount > 0)
                                                <span class="inline-flex items-center gap-1.5 rounded-full bg-indigo-50 px-3 py-1 text-xs font-bold text-indigo-700 border border-indigo-200/80">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-indigo-600"></span>
                                                    {{ $modelsCount }} {{ Str::plural('Model', $modelsCount) }}
                                                </span>
                                            @else
                                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-500">
                                                    Unassigned
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap text-xs">
                                            @if($canManage)
                                            <div class="flex items-center justify-end gap-2">
                                                <a href="{{ route('brands.edit', $brand->id) }}" class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 transition-colors">
                                                    Rename
                                                </a>
                                                <form action="{{ route('brands.destroy', $brand->id) }}" method="POST" onsubmit="return confirm('Delete brand {{ $brand->name }}?');" class="inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200/80 hover:bg-rose-100 transition-colors cursor-pointer" {{ $modelsCount > 0 ? 'title=Cannot-delete-in-use-brand' : '' }}>
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
                                        <td colspan="3" class="py-12 text-center text-slate-500 text-sm">
                                            No brands found matching your query.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($brands->hasPages())
                        <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3">
                            {{ $brands->links() }}
                        </div>
                    @endif
                </div>
            </div>

            @if($canManage)
            <!-- Right Side: Add Brand Form (1/3) -->
            <div class="lg:col-span-1">
                <form action="{{ route('brands.store') }}" method="POST" class="bg-white border border-slate-200/80 rounded-2xl p-6 shadow-sm sticky top-6">
                    @csrf
                    <div class="flex items-center gap-2 mb-4">
                        <span class="p-1.5 rounded-xl bg-indigo-50 text-indigo-600 border border-indigo-100">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </span>
                        <h3 class="text-sm font-bold text-slate-900">Add New Brand</h3>
                    </div>

                    <p class="text-xs text-slate-500 mb-4">Brands populate the manufacturer selection dropdown across product catalog creation and editing.</p>

                    <div class="space-y-4">
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Brand Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" required placeholder="e.g. Computar, Hikrobot" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 placeholder:text-slate-400">
                            @error('name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <button type="submit" class="w-full rounded-xl bg-indigo-600 px-4 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                            Save Brand
                        </button>
                    </div>

                    <div class="mt-6 pt-5 border-t border-slate-100">
                        <div class="flex items-start gap-2 text-slate-400 text-xs">
                            <svg class="w-4 h-4 shrink-0 text-slate-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                            </svg>
                            <span>Renaming a brand automatically updates all linked hardware models in the catalog.</span>
                        </div>
                    </div>
                </form>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>
