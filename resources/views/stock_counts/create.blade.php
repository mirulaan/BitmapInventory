<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Initiate Cycle Count Session</h1>
            <p class="mt-1 text-sm text-gray-500">Select a warehouse rack or scan zone to audit physical inventory against system records.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('stock-counts.index') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Back to Counts</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Please review errors:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('stock-counts.store') }}" method="POST">
        @csrf
        <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden max-w-2xl">
            <div class="px-4 py-6 sm:p-8 space-y-6">
                
                <div>
                    <label for="location_id" class="block text-sm font-medium leading-6 text-gray-900">Audit Scope / Target Warehouse Rack</label>
                    <div class="mt-2">
                        <select id="location_id" name="location_id" class="searchable-select block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                            <option value="">-- Complete Warehouse (All Internal Locations) --</option>
                            @foreach($locations as $loc)
                                <option value="{{ $loc->id }}" {{ old('location_id') == $loc->id ? 'selected' : '' }}>
                                    {{ $loc->full_path }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <p class="mt-1.5 text-xs text-slate-500">Choosing a specific rack limits expected manifest items strictly to that shelf.</p>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Audit Purpose / Notes (Optional)</label>
                    <div class="mt-2">
                        <textarea id="notes" name="notes" rows="3" placeholder="e.g. Q4 Scheduled Shelf Audit / High-value lens verification..." class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">{{ old('notes') }}</textarea>
                    </div>
                </div>

            </div>

            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-colors">
                    Start Count Session & Build Manifest
                </button>
            </div>
        </div>
    </form>
</x-app-layout>
