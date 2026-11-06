<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Process Return</h1>
            <p class="mt-1 text-sm text-gray-500">Return {{ $item->product->sku }} (S/N: {{ $item->serial_number }}) to stock.</p>
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

    <form action="{{ route('inventory.process_return', $item->id) }}" method="POST">
        @csrf
        <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden max-w-2xl">
            <div class="px-4 py-6 sm:p-8">
                
                <div class="mb-8 rounded-md bg-indigo-50 p-4 ring-1 ring-indigo-200">
                    <p class="text-sm text-indigo-800">
                        <strong>Current Status:</strong> On Loan to {{ $item->client ? $item->client->company_name : 'Unknown Client' }} <br>
                        <strong>Returning Item:</strong> {{ $item->product->name }}
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    <div class="sm:col-span-6">
                        <label for="location_id" class="block text-sm font-medium leading-6 text-gray-900">Receiving Warehouse Rack <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="location_id" name="location_id" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">-- Select Return Location --</option>
                                @foreach($locations as $location)
                                    <option value="{{ $location->id }}" {{ old('location_id') == $location->id ? 'selected' : '' }}>
                                        {{ $location->full_path }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">Select the exact physical rack where this item is being placed.</p>
                    </div>

                    <div class="col-span-full">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Return Condition / Notes</label>
                        <div class="mt-2">
                            <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. Unit returned in good working condition, original packaging included.">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 cursor-pointer transition-all">
                    Confirm Return
                </button>
            </div>
        </div>
    </form>
</x-app-layout>