<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Log RMA Defect / Repair</h1>
            <p class="mt-1 text-sm text-gray-500">Flag an item as defective and dispatch it to a manufacturer or repair center.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.product', $item->product_id) }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Cancel</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">RMA Submission Failed:</h3>
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
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Defective Asset</h3>
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
                    <p class="text-sm font-mono text-rose-700 font-bold">{{ $item->serial_number ?? 'N/A' }}</p>
                </div>
                <div class="pt-4 border-t border-slate-200">
                    <p class="text-xs text-slate-500">Last Known Location</p>
                    <p class="text-sm font-semibold text-slate-900 mt-1">
                        {{ $item->location->full_path ?? $item->location->name ?? 'Unknown' }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Right Column: RMA Form -->
        <div class="lg:col-span-2">
            <form action="{{ route('inventory.process_rma', $item->id) }}" method="POST">
                @csrf
                <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden">
                    <div class="px-4 py-6 sm:p-8 space-y-6">
                        
                        <!-- RMA Ticket -->
                        <div>
                            <label for="rma_ticket_no" class="block text-sm font-medium leading-6 text-gray-900">RMA / Repair Case Number <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <input type="text" id="rma_ticket_no" name="rma_ticket_no" required value="{{ old('rma_ticket_no') }}" placeholder="e.g. RMA-2026-9041" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm">
                            </div>
                        </div>

                        <!-- Repair Vendor / Center -->
                        <div>
                            <label for="vendor_name" class="block text-sm font-medium leading-6 text-gray-900">Repair Center / Vendor Name <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <input type="text" id="vendor_name" name="vendor_name" required value="{{ old('vendor_name') }}" placeholder="e.g. Basler Official Service Center / In-house Lab" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm">
                            </div>
                        </div>

                        <!-- Defect Description -->
                        <div>
                            <label for="issue_description" class="block text-sm font-medium leading-6 text-gray-900">Defect Description / Hardware Symptoms <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <textarea id="issue_description" name="issue_description" rows="3" required placeholder="Detailed failure description (e.g. sensor line noise, bad port, intermittent power)..." class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm">{{ old('issue_description') }}</textarea>
                            </div>
                        </div>

                        <!-- Person In Charge -->
                        <div>
                            <label for="person_in_charge" class="block text-sm font-medium leading-6 text-gray-900">Handling Engineer / Staff <span class="text-red-500">*</span></label>
                            <div class="mt-2">
                                <input type="text" id="person_in_charge" name="person_in_charge" required value="{{ old('person_in_charge', Auth::user()->name) }}" class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm">
                            </div>
                        </div>

                        <!-- Notes -->
                        <div>
                            <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Tracking Notes (Optional)</label>
                            <div class="mt-2">
                                <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="Courier tracking number, expected turnaround date..." class="block w-full rounded-md border-0 px-3 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-rose-600 sm:text-sm">
                            </div>
                        </div>

                    </div>
                    
                    <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                        <button type="submit" class="rounded-md bg-rose-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-500 cursor-pointer transition-colors">
                            Dispatch to RMA Repair
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
