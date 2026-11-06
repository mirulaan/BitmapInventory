<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Decommission / Scrap Hardware</h1>
            <p class="mt-1 text-sm text-gray-500">Permanently write off broken, obsolete, or unrepairable assets from active fleet tracking.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.product', $item->product_id) }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Cancel</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Action Failed:</h3>
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
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Asset to Scrap</h3>
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
                    <p class="text-sm font-mono text-amber-700 font-bold">{{ $item->serial_number ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">Current Status</p>
                    <div class="mt-1">
                        <x-status-badge :status="$item->status" />
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Decommission Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('inventory.process_decommission', $item->id) }}" method="POST">
                @csrf
                <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-6 sm:p-8 space-y-6">
                        
                        <!-- Reason Selection -->
                        <div>
                            <label for="reason" class="block text-sm font-medium leading-6 text-gray-900">Decommission Reason <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <select id="reason" name="reason" required class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-amber-600 sm:text-sm">
                                    <option value="">-- Select Primary Reason --</option>
                                    <option value="BER (Beyond Economical Repair)" {{ old('reason') == 'BER (Beyond Economical Repair)' ? 'selected' : '' }}>BER (Beyond Economical Repair)</option>
                                    <option value="Physical / Water Damage" {{ old('reason') == 'Physical / Water Damage' ? 'selected' : '' }}>Physical / Water Damage</option>
                                    <option value="End of Life / Obsolete" {{ old('reason') == 'End of Life / Obsolete' ? 'selected' : '' }}>End of Life / Obsolete</option>
                                    <option value="Lost / Unrecovered Asset" {{ old('reason') == 'Lost / Unrecovered Asset' ? 'selected' : '' }}>Lost / Unrecovered Asset</option>
                                    <option value="Cannibalized for Parts" {{ old('reason') == 'Cannibalized for Parts' ? 'selected' : '' }}>Cannibalized for Parts</option>
                                </select>
                            </div>
                        </div>

                        <!-- Approval / Justification Notes -->
                        <div>
                            <label for="approval_notes" class="block text-sm font-medium leading-6 text-gray-900">Disposal Justification & Approval Notes <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <textarea id="approval_notes" name="approval_notes" rows="3" required placeholder="Manager approval, disposal certificate reference, or write-off authorization..." class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-amber-600 sm:text-sm">{{ old('approval_notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Person In Charge -->
                        <div>
                            <label for="person_in_charge" class="block text-sm font-medium leading-6 text-gray-900">Authorizing Staff / Manager <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <input type="text" id="person_in_charge" name="person_in_charge" required value="{{ old('person_in_charge', Auth::user()->name) }}" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-amber-600 sm:text-sm">
                            </div>
                        </div>

                    </div>
                    
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                        <button type="submit" onclick="return confirm('Are you sure you want to permanently decommission this hardware unit? This action cannot be reversed.')" class="rounded-md bg-amber-700 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-600 cursor-pointer transition-colors">
                            Confirm Decommission & Scrap
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
