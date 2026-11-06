<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        @include('settings._nav', [
            'title' => 'Edit Client Account',
            'description' => 'Update profile specifications for ' . $client->company_name . '.'
        ])

        @if ($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                <div class="flex items-center gap-2 text-rose-800 font-semibold text-sm">
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    Please fix the following issues:
                </div>
                <ul class="mt-2 text-xs text-rose-700 list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('clients.update', $client->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 space-y-5">
                    
                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Company Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="company_name" id="company_name" value="{{ old('company_name', $client->company_name) }}" required class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        @error('company_name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <!-- Contact Person -->
                    <div>
                        <label for="contact_person" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Primary Contact Person
                        </label>
                        <input type="text" name="contact_person" id="contact_person" value="{{ old('contact_person', $client->contact_person) }}" placeholder="e.g. Ir. Lee Wei Ming" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        @error('contact_person') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <!-- Contact Number -->
                        <div>
                            <label for="contact_no" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Phone / Mobile No.
                            </label>
                            <input type="text" name="contact_no" id="contact_no" value="{{ old('contact_no', $client->contact_no) }}" placeholder="e.g. +60 12-345 6789" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @error('contact_no') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Email Address
                            </label>
                            <input type="email" name="email" id="email" value="{{ old('email', $client->email) }}" placeholder="e.g. lee.wm@vitrox.com" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @error('email') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>
                    </div>

                </div>

                <div class="p-4 sm:px-8 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('clients.index') }}" class="rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                        Update Client Account
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
