<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6 pb-12">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-5">
            <div>
                <a href="{{ route('users.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors inline-flex items-center gap-1 mb-2">
                    &larr; Back to Users
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Edit User Account: {{ $user->name }}</h1>
                <p class="text-xs text-slate-500 mt-1">Update operator profile, modify role access, or toggle active login state.</p>
            </div>

            <!-- Role Badge -->
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border {{ $user->role_badge_color }}">
                {{ $user->role_title }}
            </span>
        </div>

        @if($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                <div class="flex items-center gap-2 text-rose-800 text-xs font-bold mb-1">
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    <span>Please fix the following validation errors:</span>
                </div>
                <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Reset Code Card (Super Admin Feature) -->
        <div class="bg-gradient-to-r from-amber-50 to-orange-50 border border-amber-200/80 rounded-2xl p-6 shadow-2xs">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h3 class="text-xs font-bold uppercase tracking-wider text-amber-900 flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                        </svg>
                        Password Reset Code (Super Admin Generated)
                    </h3>
                    <p class="text-xs text-amber-800 mt-1">
                        When users forget their password, generate a single-use code valid for 24 hours to give them.
                    </p>
                </div>

                <div class="shrink-0 flex items-center gap-2">
                    @if($user->hasActiveResetCode())
                        <div class="inline-flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-amber-300 shadow-2xs">
                            <span class="font-mono font-black text-amber-900 text-sm tracking-wider">{{ $user->password_reset_code }}</span>
                            <span class="text-[11px] text-amber-700">({{ $user->password_reset_code_expires_at->diffForHumans() }})</span>
                        </div>
                        <form action="{{ route('users.revoke_reset_code', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-white border border-rose-200 text-xs font-semibold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                Revoke
                            </button>
                        </form>
                    @else
                        <form action="{{ route('users.generate_reset_code', $user->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-amber-600 text-white text-xs font-bold shadow-sm hover:bg-amber-500 transition-colors cursor-pointer">
                                Generate 1-Day Reset Code
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Edit Form -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm font-mono">@</span>
                            <input type="text" name="username" id="username" required value="{{ old('username', $user->username) }}" class="block w-full rounded-xl border border-slate-200 pl-8 pr-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                    </div>

                    <!-- Contact Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Assigned Role <span class="text-rose-500">*</span>
                        </label>
                        <select name="role" id="role" required class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="viewer" {{ old('role', $user->role) === 'viewer' ? 'selected' : '' }}>Auditor / Viewer (Read-only)</option>
                            <option value="engineer" {{ old('role', $user->role) === 'engineer' ? 'selected' : '' }}>Field Engineer (Field ops & Clients)</option>
                            <option value="warehouse" {{ old('role', $user->role) === 'warehouse' ? 'selected' : '' }}>Warehouse Manager (Stock & Master data)</option>
                            <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Super Admin (Unrestricted root)</option>
                        </select>
                    </div>

                    <!-- Change Password (Optional) -->
                    <div class="sm:col-span-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Override Password <span class="text-slate-400 font-normal lowercase">(leave blank to keep current)</span>
                        </label>
                        <input type="password" name="password" id="password" placeholder="New password (optional)" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <!-- Active Status -->
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Account is Active and authorized to sign in</span>
                        </label>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                        Update User Account
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
