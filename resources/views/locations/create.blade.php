<x-app-layout>
    <div class="max-w-3xl mx-auto space-y-6">
        @include('settings._nav', [
            'title' => 'Register Location / Site',
            'description' => 'Add an internal warehouse rack structure or register an external client site.'
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

        <form action="{{ route('locations.store') }}" method="POST">
            @csrf
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 space-y-6">

                    <!-- Facility Type Selection -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Facility Type <span class="text-rose-500">*</span>
                        </label>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            <label class="cursor-pointer border rounded-2xl p-4 flex items-start gap-3 transition-all hover:border-indigo-300 {{ old('creation_mode', 'internal') === 'internal' ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600' : 'border-slate-200 bg-white' }}" id="label_mode_internal">
                                <input type="radio" name="creation_mode" value="internal" {{ old('creation_mode', 'internal') === 'internal' ? 'checked' : '' }} onchange="toggleLocationType(this.value)" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="block text-sm font-bold text-slate-900">Internal Warehouse Rack</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Physical storage at KL or Penang headquarters.</span>
                                </div>
                            </label>

                            <label class="cursor-pointer border rounded-2xl p-4 flex items-start gap-3 transition-all hover:border-indigo-300 {{ old('creation_mode') === 'client_site' ? 'border-indigo-600 bg-indigo-50/40 ring-1 ring-indigo-600' : 'border-slate-200 bg-white' }}" id="label_mode_client_site">
                                <input type="radio" name="creation_mode" value="client_site" {{ old('creation_mode') === 'client_site' ? 'checked' : '' }} onchange="toggleLocationType(this.value)" class="mt-0.5 text-indigo-600 focus:ring-indigo-500">
                                <div>
                                    <span class="block text-sm font-bold text-slate-900">External Client Site</span>
                                    <span class="block text-xs text-slate-500 mt-0.5">Customer factory, showroom, or field installation.</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- INTERNAL WAREHOUSE FIELDS -->
                    <div id="internal_fields" class="space-y-5 border-t border-slate-100 pt-6 {{ old('creation_mode', 'internal') === 'internal' ? '' : 'hidden' }}">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div>
                                <label for="branch" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Branch Facility <span class="text-rose-500">*</span>
                                </label>
                                <select id="branch" name="branch" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="KL" {{ old('branch') == 'KL' ? 'selected' : '' }}>Kuala Lumpur (KL HQ)</option>
                                    <option value="Penang" {{ old('branch') == 'Penang' ? 'selected' : '' }}>Penang Regional Hub</option>
                                </select>
                            </div>

                            <div>
                                <label for="floor" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                    Floor Level <span class="text-rose-500">*</span>
                                </label>
                                <select id="floor" name="floor" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                    <option value="Floor 1" {{ old('floor') == 'Floor 1' ? 'selected' : '' }}>Floor 1</option>
                                    <option value="Floor 2" {{ old('floor') == 'Floor 2' ? 'selected' : '' }}>Floor 2</option>
                                    <option value="Floor 3" {{ old('floor') == 'Floor 3' ? 'selected' : '' }}>Floor 3</option>
                                    <option value="Floor 4" {{ old('floor') == 'Floor 4' ? 'selected' : '' }}>Floor 4</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="rack_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Storage Rack / Bin Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="rack_name" id="rack_name" value="{{ old('rack_name') }}" placeholder="e.g. Rack A1 - Camera Vault" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 placeholder:text-slate-400">
                            <p class="mt-1 text-xs text-slate-400">Formatted in the hierarchy as: <span class="font-mono text-slate-600">[Branch].[Floor].[Rack]</span></p>
                        </div>
                    </div>

                    <!-- EXTERNAL CLIENT SITE FIELDS -->
                    <div id="external_fields" class="space-y-5 border-t border-slate-100 pt-6 {{ old('creation_mode') === 'client_site' ? '' : 'hidden' }}">
                        <div>
                            <label for="client_site_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Client Facility / Site Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="client_site_name" id="client_site_name" value="{{ old('client_site_name') }}" placeholder="e.g. Inari Amertron Bayan Lepas Plant 2" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-2xs focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 placeholder:text-slate-400">
                        </div>
                    </div>

                </div>

                <div class="p-4 sm:px-8 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('locations.index') }}" class="rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 shadow-2xs hover:bg-slate-50 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 transition-colors cursor-pointer">
                        Save Location
                    </button>
                </div>
            </div>
        </form>
    </div>

    <script>
        function toggleLocationType(type) {
            const internalDiv = document.getElementById('internal_fields');
            const externalDiv = document.getElementById('external_fields');
            const labelInternal = document.getElementById('label_mode_internal');
            const labelClientSite = document.getElementById('label_mode_client_site');

            if (type === 'internal') {
                internalDiv.classList.remove('hidden');
                externalDiv.classList.add('hidden');
                labelInternal.classList.add('border-indigo-600', 'bg-indigo-50/40', 'ring-1', 'ring-indigo-600');
                labelInternal.classList.remove('border-slate-200', 'bg-white');
                labelClientSite.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'ring-1', 'ring-indigo-600');
                labelClientSite.classList.add('border-slate-200', 'bg-white');
            } else {
                internalDiv.classList.add('hidden');
                externalDiv.classList.remove('hidden');
                labelClientSite.classList.add('border-indigo-600', 'bg-indigo-50/40', 'ring-1', 'ring-indigo-600');
                labelClientSite.classList.remove('border-slate-200', 'bg-white');
                labelInternal.classList.remove('border-indigo-600', 'bg-indigo-50/40', 'ring-1', 'ring-indigo-600');
                labelInternal.classList.add('border-slate-200', 'bg-white');
            }
        }
    </script>
</x-app-layout>
