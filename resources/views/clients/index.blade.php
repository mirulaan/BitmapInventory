<x-app-layout>
    <div class="space-y-6">
        @include('settings._nav', [
            'title' => 'Client Accounts',
            'description' => 'Manage client companies, loan partners, and corporate purchaser accounts.'
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
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <form method="GET" action="{{ route('clients.index') }}" class="bg-white border border-slate-200/80 rounded-2xl p-3 shadow-2xs flex items-center gap-3 flex-1">
                <div class="flex-1 relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search company name, contact person, or email..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-1.5 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors cursor-pointer shrink-0">
                    Search
                </button>
                @if(request()->filled('search'))
                    <a href="{{ route('clients.index') }}" class="rounded-xl bg-white border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors shrink-0">
                        Clear
                    </a>
                @endif
            </form>

            @if(Auth::user()->hasRole('admin', 'warehouse', 'engineer'))
            <a href="{{ route('clients.create') }}" class="inline-flex items-center justify-center gap-2 rounded-2xl bg-indigo-600 px-5 py-3 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-all shrink-0">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Add Client Account</span>
            </a>
            @endif
        </div>

        <!-- Clients Table Card -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Company Name</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Primary Contact</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Contact Info</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Active Loans</th>
                            <th scope="col" class="relative py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($clients as $client)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                
                                <!-- Company Name -->
                                <td class="py-4 pl-6 pr-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-indigo-50 to-slate-100 border border-slate-200/80 flex items-center justify-center text-indigo-700 font-bold text-xs shrink-0 shadow-2xs">
                                            {{ strtoupper(substr($client->company_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-slate-900">{{ $client->company_name }}</div>
                                            <div class="text-[11px] text-slate-400 font-mono">Account ID #{{ $client->id }}</div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Person -->
                                <td class="py-4 px-4 whitespace-nowrap text-xs">
                                    @if($client->contact_person)
                                        <div class="font-semibold text-slate-800 flex items-center gap-1.5">
                                            <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                            <span>{{ $client->contact_person }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Unassigned</span>
                                    @endif
                                </td>

                                <!-- Contact Details -->
                                <td class="py-4 px-4 whitespace-nowrap text-xs">
                                    <div class="space-y-1">
                                        @if($client->contact_no)
                                            <div class="flex items-center gap-1.5 text-slate-700 font-medium">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                                </svg>
                                                <span>{{ $client->contact_no }}</span>
                                            </div>
                                        @endif
                                        @if($client->email)
                                            <div class="flex items-center gap-1.5 text-slate-500">
                                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                                </svg>
                                                <span>{{ $client->email }}</span>
                                            </div>
                                        @endif
                                        @if(!$client->contact_no && !$client->email)
                                            <span class="text-slate-400 italic">No details provided</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Active Field Loans -->
                                <td class="py-4 px-4 whitespace-nowrap text-center">
                                    @if($client->active_loans_count > 0)
                                        <span class="inline-flex items-center gap-1.5 rounded-full bg-amber-50 px-3 py-1 text-xs font-bold text-amber-700 border border-amber-200/80">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                            {{ $client->active_loans_count }} On Loan
                                        </span>
                                    @else
                                        <span class="text-xs text-slate-400 font-medium">0 Loans</span>
                                    @endif
                                </td>

                                <!-- Action Buttons -->
                                <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap text-xs">
                                    @if(Auth::user()->hasRole('admin', 'warehouse', 'engineer'))
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('clients.edit', $client->id) }}" class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 transition-colors">
                                            Edit
                                        </a>
                                        <form action="{{ route('clients.destroy', $client->id) }}" method="POST" onsubmit="return confirm('Delete client account {{ $client->company_name }}?');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200/80 hover:bg-rose-100 transition-colors cursor-pointer" {{ $client->active_loans_count > 0 ? 'title=Cannot-delete-client-with-active-loans' : '' }}>
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
                                    No client accounts found matching your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($clients->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3">
                    {{ $clients->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
