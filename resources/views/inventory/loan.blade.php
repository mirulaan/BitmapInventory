<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dispatch Loan / PoC</h1>
            <p class="mt-1 text-sm text-gray-500">Assign a component to a client for testing or Proof of Concept.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.index') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Cancel</a>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        
        <!-- Left Column: Item Summary -->
        <div class="lg:col-span-1">
            <div class="bg-slate-50 ring-1 ring-slate-200 shadow-sm sm:rounded-xl overflow-hidden p-6">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Asset Details</h3>
                
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-slate-500">Product</p>
                        <p class="font-medium text-slate-900">{{ $item->product->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">SKU</p>
                        <p class="text-sm font-mono text-slate-700">{{ $item->product->sku }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-500">Serial Number</p>
                        <p class="text-sm font-mono text-slate-700">{{ $item->serial_number ?? 'N/A' }}</p>
                    </div>
                    <div class="pt-4 border-t border-slate-200">
                        <p class="text-xs text-slate-500">Current Physical Location</p>
                        <p class="text-sm text-slate-900 mt-1">
                            <span class="inline-flex items-center rounded-md bg-white px-2 py-1 text-xs font-medium ring-1 ring-inset ring-slate-300">
                                {{ $item->location->name ?? 'Unknown' }}
                            </span>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Dispatch Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('inventory.process_loan', $item->id) }}" method="POST">
                @csrf
                <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-6 sm:p-8">
                        <div class="grid grid-cols-1 gap-x-6 gap-y-6 sm:grid-cols-6">
                            
                            <!-- Client Selection -->
                            <div class="sm:col-span-4">
                                <label for="client_id" class="block text-sm font-medium leading-6 text-gray-900">Assign to Client <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <select id="client_id" name="client_id" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                        <option value="">-- Select Client --</option>
                                        @foreach($clients as $client)
                                            <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Target Location (Client Site) -->
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
                                <p class="mt-2 text-xs text-gray-500">The primary contact receiving this hardware at the client site.</p>
                            </div>

                            <!-- Loan Form Number -->
                            <div class="sm:col-span-4">
                                <label for="loan_form_no" class="block text-sm font-medium leading-6 text-gray-900">Loan Form No. <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <input type="text" name="loan_form_no" id="loan_form_no" value="{{ old('loan_form_no') }}" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="e.g. LF-2026-104">
                                </div>
                            </div>

                            <!-- Loan Start Date -->
                            <div class="sm:col-span-3">
                                <label for="loan_start_date" class="block text-sm font-medium leading-6 text-gray-900">Start Date <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <input type="date" name="loan_start_date" id="loan_start_date" value="{{ old('loan_start_date', date('Y-m-d')) }}" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            <!-- Loan Duration -->
                            <div class="sm:col-span-3">
                                <label for="loan_duration" class="block text-sm font-medium leading-6 text-gray-900">Duration (Months) <span class="text-red-500">*</span></label>
                                <div class="mt-2">
                                    <input type="number" id="loan_duration" name="loan_duration" min="1" max="12" value="1" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                </div>
                            </div>

                            <!-- Calculated End Date (Read-Only) -->
                            <div class="sm:col-span-3">
                                <label for="loan_end_date" class="block text-sm font-medium leading-6 text-gray-900">End Date</label>
                                <div class="mt-2">
                                    <input type="date" name="loan_end_date" id="loan_end_date" value="{{ old('loan_end_date') }}" readonly class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6 focus:ring-0">
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="col-span-full">
                                <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Terms / Notes</label>
                                <div class="mt-2">
                                    <textarea id="notes" name="notes" rows="3" class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6" placeholder="Expected duration, specific conditions, etc."></textarea>
                                </div>
                            </div>

                        </div>
                    </div>
                    
                    <!-- Submit Footer -->
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                        <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                            Confirm Loan Dispatch
                        </button>
                    </div>
                </div>
            </form>
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

        @if(session('error'))
            <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
                <p class="text-sm font-medium text-red-800">Database Error: {{ session('error') }}</p>
            </div>
        @endif
    </div>

    <script>
    function calculateEndDate() {
        const startInput = document.getElementById('loan_start_date').value;
        const duration = parseInt(document.getElementById('loan_duration').value) || 0;
        const endInput = document.getElementById('loan_end_date');

        if (startInput && duration > 0) {
            const date = new Date(startInput);
            
            // Add the specified number of months
            date.setMonth(date.getMonth() + duration);
            
            // Format back to YYYY-MM-DD for the HTML date input
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            
            endInput.value = `${year}-${month}-${day}`;
        }
    }

    // Trigger calculation when start date or duration changes
    document.getElementById('loan_start_date').addEventListener('change', calculateEndDate);
    document.getElementById('loan_duration').addEventListener('input', calculateEndDate);
    
    // Automatically calculate on initial page load to populate default values
    document.addEventListener('DOMContentLoaded', calculateEndDate);
</script>
</x-app-layout>