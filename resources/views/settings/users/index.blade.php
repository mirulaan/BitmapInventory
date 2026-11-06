<x-app-layout>
    <div class="space-y-6 pb-12">
        
        <!-- Settings Sub-Navigation -->
        @include('settings._nav', [
            'title' => 'User Accounts & Access Control',
            'description' => 'Manage system operators, assign permission roles, manage active status, and issue 24-hour single-use reset codes.'
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

        <!-- Stats Overview Cards -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs">
                <p class="text-xs font-bold uppercase tracking-wider text-slate-400">Total Accounts</p>
                <p class="text-2xl font-black text-slate-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs">
                <p class="text-xs font-bold uppercase tracking-wider text-emerald-600">Active Operators</p>
                <p class="text-2xl font-black text-emerald-700 mt-1">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs">
                <p class="text-xs font-bold uppercase tracking-wider text-rose-500">Deactivated</p>
                <p class="text-2xl font-black text-rose-700 mt-1">{{ $stats['inactive'] }}</p>
            </div>
            <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs">
                <p class="text-xs font-bold uppercase tracking-wider text-indigo-600">Super Admins</p>
                <p class="text-2xl font-black text-indigo-700 mt-1">{{ $stats['admins'] }}</p>
            </div>
        </div>

        <!-- Filter & Action Toolbar -->
        <div class="bg-white border border-slate-200/80 rounded-2xl p-4 shadow-2xs flex flex-col md:flex-row md:items-center justify-between gap-3">
            <form method="GET" action="{{ route('users.index') }}" class="flex flex-1 flex-wrap items-center gap-2.5">
                <!-- Search input -->
                <div class="flex-1 min-w-[200px] relative">
                    <svg class="w-4 h-4 absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name, username, or email..." class="block w-full rounded-xl border border-slate-200 pl-10 pr-3.5 py-2 text-xs sm:text-sm text-slate-900 placeholder:text-slate-400 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                </div>

                <!-- Role Filter -->
                <select name="role" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Roles</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Super Admin</option>
                    <option value="warehouse" {{ request('role') === 'warehouse' ? 'selected' : '' }}>Warehouse Manager</option>
                    <option value="engineer" {{ request('role') === 'engineer' ? 'selected' : '' }}>Field Engineer</option>
                    <option value="viewer" {{ request('role') === 'viewer' ? 'selected' : '' }}>Auditor / Viewer</option>
                </select>

                <!-- Status Filter -->
                <select name="status" class="rounded-xl border border-slate-200 px-3 py-2 text-xs font-semibold text-slate-700 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    <option value="">All Statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Deactivated</option>
                </select>

                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-slate-800 transition-colors cursor-pointer">
                    Filter
                </button>

                @if(request()->anyFilled(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="rounded-xl bg-white border border-slate-300 px-3 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Clear
                    </a>
                @endif
            </form>

            <a href="{{ route('users.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-xl bg-indigo-600 px-4 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 hover:shadow transition-all shrink-0 cursor-pointer">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                </svg>
                <span>Add User Account</span>
            </a>
        </div>

        <!-- Users Table -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-100">
                    <thead class="bg-slate-50/75">
                        <tr>
                            <th scope="col" class="py-3.5 pl-6 pr-3 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">User Identity</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Contact Email</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Role Access</th>
                            <th scope="col" class="py-3.5 px-4 text-center text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="py-3.5 px-4 text-left text-xs font-bold text-slate-500 uppercase tracking-wider">Reset Code (1-Day / 1-Use)</th>
                            <th scope="col" class="py-3.5 pl-3 pr-6 text-right text-xs font-bold text-slate-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($users as $user)
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- User Identity -->
                                <td class="py-4 pl-6 pr-3 whitespace-nowrap">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-slate-900 text-white flex items-center justify-center font-bold text-xs shadow-2xs shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm text-slate-900 flex items-center gap-1.5">
                                                <span>{{ $user->name }}</span>
                                                @if(Auth::id() === $user->id)
                                                    <span class="inline-flex items-center px-1.5 py-0.2 rounded text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">You</span>
                                                @endif
                                            </div>
                                            <div class="text-xs text-slate-400 font-mono">
                                                <span>@</span>{{ $user->username }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                <!-- Contact Email -->
                                <td class="py-4 px-4 whitespace-nowrap text-xs text-slate-600">
                                    <a href="mailto:{{ $user->email }}" class="text-indigo-600 hover:text-indigo-800 hover:underline flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                        </svg>
                                        {{ $user->email }}
                                    </a>
                                </td>

                                <!-- Role -->
                                <td class="py-4 px-4 whitespace-nowrap">
                                    @php
                                        $rolePills = [
                                            'admin' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'warehouse' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'engineer' => 'bg-sky-50 text-sky-700 border-sky-200',
                                            'viewer' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        ];
                                        $pillClass = $rolePills[$user->role] ?? 'bg-slate-100 text-slate-700 border-slate-200';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $pillClass }}">
                                        {{ $user->role_title }}
                                    </span>
                                </td>

                                <!-- Status -->
                                <td class="py-4 px-4 whitespace-nowrap text-center">
                                    <form action="{{ route('users.toggle_status', $user->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" 
                                            {{ Auth::id() === $user->id ? 'disabled title="Cannot deactivate yourself"' : '' }}
                                            class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold border transition-colors cursor-pointer {{ $user->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-100 text-slate-500 border-slate-200 hover:bg-slate-200' }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                            <span>{{ $user->is_active ? 'Active' : 'Inactive' }}</span>
                                        </button>
                                    </form>
                                </td>

                                <!-- Reset Code & Validity -->
                                <td class="py-4 px-4 whitespace-nowrap text-xs">
                                    @if($user->hasActiveResetCode())
                                        <div class="flex items-center gap-2">
                                            <div class="inline-flex items-center gap-1.5 bg-amber-50 border border-amber-200 text-amber-900 px-2.5 py-1 rounded-lg font-mono font-bold">
                                                <span id="code-{{ $user->id }}">{{ $user->password_reset_code }}</span>
                                                <button type="button" onclick="copyCode('{{ $user->password_reset_code }}', this)" title="Copy Code" class="text-amber-700 hover:text-amber-900 p-0.5 rounded transition-colors cursor-pointer">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.666 3.849A1.996 1.996 0 0014 3H6a2 2 0 00-2 2v14a2 2 0 002 2h8a2 2 0 002-2v-1.151m4.334-11.849a2 2 0 011.666 1.996v10.304a2 2 0 01-2 2h-6.334a2 2 0 01-1.996-1.666m8.33-12.634l-8.33 8.33" />
                                                    </svg>
                                                </button>
                                            </div>
                                            <div class="flex flex-col">
                                                <span class="text-[10px] text-slate-400">
                                                    Expires {{ $user->password_reset_code_expires_at->diffForHumans() }}
                                                </span>
                                                <form action="{{ route('users.revoke_reset_code', $user->id) }}" method="POST" class="inline">
                                                    @csrf
                                                    <button type="submit" class="text-[10px] text-rose-500 hover:underline cursor-pointer">
                                                        Revoke
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @else
                                        <form action="{{ route('users.generate_reset_code', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 text-xs font-semibold transition-colors cursor-pointer">
                                                <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                                                </svg>
                                                <span>Generate Code</span>
                                            </button>
                                        </form>
                                    @endif
                                </td>

                                <!-- Actions -->
                                <td class="py-4 pl-3 pr-6 text-right whitespace-nowrap text-xs">
                                    <div class="flex items-center justify-end gap-2">
                                        <a href="{{ route('users.edit', $user->id) }}" class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-700 border border-indigo-200/80 hover:bg-indigo-100 transition-colors">
                                            Edit
                                        </a>

                                        @if(Auth::id() !== $user->id)
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Delete user account {{ $user->username }}? This action cannot be undone.');" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-bold text-rose-700 border border-rose-200/80 hover:bg-rose-100 transition-colors cursor-pointer">
                                                Delete
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center text-slate-500 text-sm">
                                    No user accounts found matching your query.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="border-t border-slate-100 bg-slate-50/50 px-6 py-3">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

    </div>

    <script>
        function copyCode(code, btn) {
            navigator.clipboard.writeText(code).then(() => {
                const originalHtml = btn.innerHTML;
                btn.innerHTML = `<span class="text-[10px] text-emerald-600 font-bold">Copied!</span>`;
                setTimeout(() => {
                    btn.innerHTML = originalHtml;
                }, 1500);
            });
        }
    </script>
</x-app-layout>
