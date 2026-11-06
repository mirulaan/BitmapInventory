<x-app-layout>
    <div class="max-w-2xl mx-auto space-y-6">
        @include('settings._nav', [
            'title' => 'Rename Brand',
            'description' => 'Update the manufacturer brand name across all catalog models.'
        ])

        <form action="{{ route('brands.update', $brand->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 space-y-5">
                    <div>
                        <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Brand Name <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" value="{{ old('name', $brand->name) }}" required class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                        @error('name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-xl bg-blue-50 border border-blue-200 p-4 text-xs text-blue-800 flex items-start gap-2.5">
                        <svg class="w-4 h-4 text-blue-600 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                        <span>Renaming will cascade to all active hardware products currently assigned to brand <strong>"{{ $brand->name }}"</strong>.</span>
                    </div>
                </div>

                <div class="p-4 sm:px-8 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('brands.index') }}" class="rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                        Update Brand
                    </button>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
