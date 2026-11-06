<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Direct Sale</h1>
            <p class="mt-1 text-sm text-gray-500">Sell and dispatch {{ $item->product->sku }} (S/N: {{ $item->serial_number }}) directly from stock.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.index') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Cancel</a>
        </div>
    </div>

    <!-- GLOBAL ERROR CATCHER -->
    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Form Validation Failed:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('inventory.process_sell', $item->id) }}" method="POST">
        @csrf
        <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden max-w-2xl">
            <div class="px-4 py-6 sm:p-8">
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <!-- Assign to Client -->
                    <div class="sm:col-span-4">
                        <label for="client_id" class="block text-sm font-medium leading-6 text-gray-900">Assign to Client <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="client_id" name="client_id" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">-- Select Client --</option>
                                @foreach($clients as $client)
                                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                        {{ $client->company_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Destination Site -->
                    <div class="sm:col-span-4">
                        <label for="location_id" class="block text-sm font-medium leading-6 text-gray-900">Destination Site <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="location_id" name="location_id" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">-- Select Client Site --</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <!-- Person in Charge -->
                    <div class="sm:col-span-4">
                        <label for="person_in_charge" class="block text-sm font-medium leading-6 text-gray-900">Person in Charge (PIC) <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="person_in_charge" id="person_in_charge" value="{{ old('person_in_charge') }}" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. John Doe, Lead Technician">
                        </div>
                    </div>

                    <!-- PO from Customer -->
                    <div class="sm:col-span-4">
                        <label for="po_customer" class="block text-sm font-medium leading-6 text-gray-900">PO from Customer <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="po_customer" id="po_customer" value="{{ old('po_customer') }}" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. POC-2026-9921">
                        </div>
                    </div>

                    <!-- PO for Supplier -->
                    <div class="sm:col-span-4">
                        <label for="po_supplier" class="block text-sm font-medium leading-6 text-gray-900">PO for Supplier <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="po_supplier" id="po_supplier" value="{{ old('po_supplier') }}" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. POS-2026-5541">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="col-span-full">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Sales Notes</label>
                        <div class="mt-2">
                            <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Any final details regarding the sale or warranty start date.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    Process Sale
                </button>
            </div>
        </div>
    </form>
</x-app-layout>