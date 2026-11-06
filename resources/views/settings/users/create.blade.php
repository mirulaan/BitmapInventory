<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6 pb-12">
        
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-200/80 pb-5">
            <div>
                <a href="{{ route('users.index') }}" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 transition-colors inline-flex items-center gap-1 mb-2">
                    &larr; Back to Users
                </a>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900">Add User Account</h1>
                <p class="text-xs text-slate-500 mt-1">Create an operator identity with designated role permissions.</p>
            </div>
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

        <!-- Card Form -->
        <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm p-6 sm:p-8">
            <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <!-- Full Name -->
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Full Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="{{ old('name') }}" placeholder="e.g. Alex Tan" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Username <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-400 text-sm font-mono">@</span>
                            <input type="text" name="username" id="username" required value="{{ old('username') }}" placeholder="alextan" class="block w-full rounded-xl border border-slate-200 pl-8 pr-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">Used for system login. Letters, numbers, dashes, and underscores only.</p>
                    </div>

                    <!-- Contact Email -->
                    <div>
                        <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Email Address <span class="text-rose-500">*</span>
                        </label>
                        <input type="email" name="email" id="email" required value="{{ old('email') }}" placeholder="alex@bitmap.com" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        <p class="text-[11px] text-slate-400 mt-1">Official contact and system notification record.</p>
                    </div>

                    <!-- Role -->
                    <div>
                        <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Assigned Role <span class="text-rose-500">*</span>
                        </label>
                        <select name="role" id="role" required class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 bg-white focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <option value="viewer" {{ old('role', 'viewer') === 'viewer' ? 'selected' : '' }}>Auditor / Viewer (Read-only)</option>
                            <option value="engineer" {{ old('role') === 'engineer' ? 'selected' : '' }}>Field Engineer (Field ops & Clients)</option>
                            <option value="warehouse" {{ old('role') === 'warehouse' ? 'selected' : '' }}>Warehouse Manager (Stock & Master data)</option>
                            <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>Super Admin (Unrestricted root)</option>
                        </select>
                    </div>

                    <!-- Initial Password -->
                    <div class="sm:col-span-2">
                        <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Initial Password <span class="text-rose-500">*</span>
                        </label>
                        <input type="password" name="password" id="password" required placeholder="Minimum 6 characters" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                    </div>

                    <!-- Active Status -->
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" {{ old('is_active', '1') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="text-xs font-semibold text-slate-700">Account is Active and authorized to sign in</span>
                        </label>
                    </div>
                </div>

                <div class="pt-6 border-t border-slate-100 flex items-center justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                        Save User Account
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>
