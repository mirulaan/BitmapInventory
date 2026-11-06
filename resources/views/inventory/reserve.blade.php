<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Reserve Hardware Asset</h1>
            <p class="mt-1 text-sm text-gray-500">Hold stock for an upcoming client project, pilot staging, or sales reservation.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.product', $item->product_id) }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Cancel</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Reservation Failed:</h3>
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
                    <p class="text-sm font-mono text-purple-700 font-bold">{{ $item->serial_number ?? 'N/A' }}</p>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <p class="text-xs text-slate-500">Physical Warehouse Location</p>
                    <p class="text-sm font-semibold text-slate-900 mt-1">
                        {{ $item->location->full_path ?? $item->location->name ?? 'Unknown' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: Reserve Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('inventory.process_reserve', $item->id) }}" method="POST">
                @csrf
                <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-6 sm:p-8 space-y-6">
                        
                        <!-- Client Selection -->
                        <div>
                            <label for="client_id" class="block text-sm font-medium leading-6 text-gray-900">Reserved For Client (Optional)</label>
                            <div class="mt-2">
                                <select id="client_id" name="client_id" class="searchable-select block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                                    <option value="">-- No specific client / Internal project --</option>
                                    @foreach($clients as $c)
                                        <option value="{{ $c->id }}" {{ old('client_id') == $c->id ? 'selected' : '' }}>
                                            {{ $c->company_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Project / Reference Name -->
                        <div>
                            <label for="project_name" class="block text-sm font-medium leading-6 text-gray-900">Project / Order Reference (Optional)</label>
                            <div class="mt-2">
                                <input type="text" id="project_name" name="project_name" value="{{ old('project_name') }}" placeholder="e.g. Project Apollo Staging / PO-HOLD-492" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                            </div>
                        </div>

                        <!-- Person In Charge -->
                        <div>
                            <label for="person_in_charge" class="block text-sm font-medium leading-6 text-gray-900">Person In Charge (PIC) <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <input type="text" id="person_in_charge" name="person_in_charge" required value="{{ old('person_in_charge', Auth::user()->name) }}" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Reservation Notes (Optional)</label>
                            <div class="mt-2">
                                <textarea id="notes" name="notes" rows="3" placeholder="Target deployment date or staging requirements..." class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                    </div>
                    
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                        <button type="submit" class="rounded-md bg-purple-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-purple-500 cursor-pointer transition-colors">
                            Confirm Reservation
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
