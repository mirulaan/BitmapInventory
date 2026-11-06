<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">New Purchase Order</h1>
            <p class="mt-1 text-sm text-gray-500">Draft a supplier purchase order for hardware equipment replenishment.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('purchase-orders.index') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Back to Orders</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Please review the form errors:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('purchase-orders.store') }}" method="POST" id="po_form">
        @csrf
        <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden max-w-5xl">
            <div class="px-4 py-6 sm:p-8 space-y-8">
                
                <!-- TOP SECTION: Order Header -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 border-b border-gray-200 pb-8">
                    <!-- Supplier -->
                    <div>
                        <label for="supplier_id" class="block text-sm font-medium leading-6 text-gray-900">Supplier Vendor <span class="text-red-500">*</span></label>
                        <select id="supplier_id" name="supplier_id" required class="searchable-select mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">-- Select Supplier --</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Order Date -->
                    <div>
                        <label for="order_date" class="block text-sm font-medium leading-6 text-gray-900">Order Date <span class="text-red-500">*</span></label>
                        <input type="date" id="order_date" name="order_date" required value="{{ old('order_date', now()->format('Y-m-d')) }}" class="mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Expected Delivery Date -->
                    <div>
                        <label for="expected_delivery_date" class="block text-sm font-medium leading-6 text-gray-900">Expected Delivery (ETA)</label>
                        <input type="date" id="expected_delivery_date" name="expected_delivery_date" value="{{ old('expected_delivery_date', now()->addDays(7)->format('Y-m-d')) }}" class="mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- Notes / PO Terms -->
                    <div class="sm:col-span-3">
                        <label for="notes" class="block text-sm font-medium leading-6 text-gray-900">Order Remarks & Payment Terms (Optional)</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="e.g. FOB Shipping Point, Net 30 days, urgent project replenishment..." class="mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <!-- MIDDLE SECTION: Line Items Table -->
                <div>
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500">Ordered Equipment Line Items</h3>
                        <button type="button" onclick="addLineItem()" class="inline-flex items-center gap-1.5 rounded-lg bg-indigo-50 border border-indigo-200 px-3 py-1 text-xs font-bold text-indigo-700 hover:bg-indigo-100 transition-colors cursor-pointer">
                            + Add Line Item
                        </button>
                    </div>

                    <div class="ring-1 ring-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200" id="items_table">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 pl-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Hardware Model</th>
                                    <th scope="col" class="py-3 px-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider w-28">Order Qty</th>
                                    <th scope="col" class="py-3 px-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-36">Unit Cost (RM)</th>
                                    <th scope="col" class="py-3 px-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-36">Subtotal (RM)</th>
                                    <th scope="col" class="py-3 pr-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">Action</th>
                                </tr>
                            </thead>
                            <tbody id="items_body" class="divide-y divide-gray-200 bg-white">
                                <!-- Dynamic rows added here -->
                            </tbody>
                            <tfoot class="bg-slate-50 border-t border-gray-200 font-bold">
                                <tr>
                                    <td colspan="3" class="py-3 pl-4 text-right text-sm text-slate-700">Estimated Total Amount:</td>
                                    <td class="py-3 px-3 text-right text-base text-indigo-700 font-mono" id="grand_total">RM 0.00</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

            </div>

            <!-- Submit Footer -->
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-all">
                    Generate Purchase Order
                </button>
            </div>
        </div>
    </form>

    <script>
        const productsList = @json($products);
        let rowIndex = 0;

        function addLineItem(defaultProductId = '', defaultQty = 5, defaultCost = 0) {
            const tbody = document.getElementById('items_body');
            const tr = document.createElement('tr');
            tr.id = `row_${rowIndex}`;
            tr.className = "hover:bg-gray-50/70";

            let optionsHtml = '<option value="">-- Choose Product --</option>';
            productsList.forEach(p => {
                const selected = p.id == defaultProductId ? 'selected' : '';
                optionsHtml += `<option value="${p.id}" data-price="${p.unit_price || 0}" ${selected}>[${p.sku}] ${p.name}</option>`;
            });

            tr.innerHTML = `
                <td class="py-2.5 pl-4 pr-2">
                    <select name="items[${rowIndex}][product_id]" required onchange="onProductSelect(this, ${rowIndex})" class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-indigo-600 sm:text-xs">
                        ${optionsHtml}
                    </select>
                </td>
                <td class="py-2.5 px-2">
                    <input type="number" name="items[${rowIndex}][quantity]" required min="1" value="${defaultQty}" oninput="calculateRow(${rowIndex})" class="block w-full rounded-md border-0 px-2.5 py-1.5 text-center text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-xs">
                </td>
                <td class="py-2.5 px-2">
                    <input type="number" step="0.01" min="0" name="items[${rowIndex}][unit_cost]" required value="${defaultCost}" oninput="calculateRow(${rowIndex})" class="block w-full rounded-md border-0 px-2.5 py-1.5 text-right font-mono text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-xs">
                </td>
                <td class="py-2.5 px-3 text-right font-mono font-bold text-slate-800 text-xs" id="subtotal_${rowIndex}">
                    RM 0.00
                </td>
                <td class="py-2.5 pr-4 text-right">
                    <button type="button" onclick="removeLineItem(${rowIndex})" class="text-rose-600 hover:text-rose-900 text-xs font-semibold cursor-pointer">
                        Remove
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            calculateRow(rowIndex);
            rowIndex++;
        }

        function onProductSelect(selectElement, idx) {
            const opt = selectElement.options[selectElement.selectedIndex];
            const price = parseFloat(opt.getAttribute('data-price') || 0);
            // Default acquisition cost estimate to 70% of catalog price
            const estimatedCost = price > 0 ? (price * 0.70).toFixed(2) : 0;
            const costInput = document.querySelector(`input[name="items[${idx}][unit_cost]"]`);
            if (costInput && (!costInput.value || parseFloat(costInput.value) === 0)) {
                costInput.value = estimatedCost;
            }
            calculateRow(idx);
        }

        function calculateRow(idx) {
            const qtyInput = document.querySelector(`input[name="items[${idx}][quantity]"]`);
            const costInput = document.querySelector(`input[name="items[${idx}][unit_cost]"]`);
            const subtotalCell = document.getElementById(`subtotal_${idx}`);

            if (qtyInput && costInput && subtotalCell) {
                const qty = parseInt(qtyInput.value) || 0;
                const cost = parseFloat(costInput.value) || 0;
                const subtotal = qty * cost;
                subtotalCell.innerText = `RM ${subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
            }
            calculateGrandTotal();
        }

        function removeLineItem(idx) {
            const row = document.getElementById(`row_${idx}`);
            if (row) {
                row.remove();
            }
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            const qtyInputs = document.querySelectorAll('input[name*="[quantity]"]');
            qtyInputs.forEach(qi => {
                const tr = qi.closest('tr');
                if (tr) {
                    const ci = tr.querySelector('input[name*="[unit_cost]"]');
                    if (ci) {
                        const q = parseInt(qi.value) || 0;
                        const c = parseFloat(ci.value) || 0;
                        total += (q * c);
                    }
                }
            });

            document.getElementById('grand_total').innerText = `RM ${total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        // Add 1 blank line on load
        document.addEventListener('DOMContentLoaded', function() {
            addLineItem();
        });
    </script>
</x-app-layout>
