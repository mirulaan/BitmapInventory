<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Transfer Hardware Location</h1>
            <p class="mt-1 text-sm text-gray-500">Relocate an item internally to another warehouse rack or internal zone.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.product', $item->product_id) }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Cancel</a>
        </div>
    </div>

    <!-- Error Catcher -->
    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Transfer Failed:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Left Column: Item Summary -->
        <div class="lg:col-span-1">
            <div class="bg-slate-50 ring-1 ring-slate-200 shadow-sm sm:rounded-xl overflow-hidden p-6 space-y-4">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Asset Details</h3>
                <div>
                    <p class="text-xs text-slate-500">Product</p>
                    <p class="font-bold text-slate-900">{{ $item->product->name }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">SKU</p>
                    <p class="text-sm font-mono text-slate-700">{{ $item->product->sku }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Serial Number</p>
                    <p class="text-sm font-mono text-indigo-700 font-bold">{{ $item->serial_number ?? 'N/A' }}</p>
                </div>
                @if($item->batch_no)
                <div>
                    <p class="text-xs text-slate-500">Batch / Lot #</p>
                    <p class="text-sm font-mono text-slate-700">{{ $item->batch_no }}</p>
                </div>
                @endif
                <div class="pt-4 border-t border-slate-200">
                    <p class="text-xs text-slate-500">Current Warehouse Location</p>
                    <p class="text-sm font-semibold text-slate-900 mt-1">
                        {{ $item->location->full_path ?? $item->location->name ?? 'Unknown' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Transfer Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('inventory.process_transfer', $item->id) }}" method="POST">
                @csrf
                <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-6 sm:p-8 space-y-6">
                        
                        <!-- Destination Location -->
                        <div>
                            <label for="to_location_id" class="block text-sm font-medium leading-6 text-gray-900">New Destination Warehouse Rack <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <select id="to_location_id" name="to_location_id" required class="searchable-select block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                    <option value="">-- Select Destination Rack --</option>
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id }}" {{ old('to_location_id') == $loc->id ? 'selected' : '' }}>
                                            {{ $loc->full_path }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Reference Number -->
                        <div>
                            <label for="reference_number" class="block text-sm font-medium leading-6 text-gray-900">Transfer Order / Ticket Reference (Optional)</label>
                            <div class="mt-2">
                                <input type="text" id="reference_number" name="reference_number" value="{{ old('reference_number') }}" placeholder="e.g. TR-2026-081" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Transfer Reason / Remarks (Optional)</label>
                            <div class="mt-2">
                                <textarea id="notes" name="notes" rows="3" placeholder="Reason for shelf relocation or zone movement..." class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                    </div>
                    
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                        <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-colors">
                            Complete Transfer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
