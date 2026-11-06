<x-app-layout>
    <!-- Add Tom Select CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.default.css" rel="stylesheet">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Receive New Hardware</h1>
            <p class="mt-1 text-sm text-gray-500">Add stock to an internal warehouse rack.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ route('inventory.index') }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Back to Inventory</a>
        </div>
    </div>

    <!-- GLOBAL ERROR CATCHER -->
    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Form Validation Failed:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $selectedProductId = (string) old('product_id', '');
        $selectedProduct = $products->firstWhere('id', $selectedProductId);
        $oldCategory = trim((string) old('category', ''));
        $selectedCategory = $oldCategory !== '' ? $oldCategory : ($selectedProduct?->category ?? '');
        $oldSerials = old('serial_numbers', []);
        if (!is_array($oldSerials)) {
            $oldSerials = [];
        }
    @endphp

    <form action="{{ route('inventory.store') }}" method="POST" id="inventory_form">
        @csrf
        <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden max-w-4xl">
            <div class="px-4 py-6 sm:p-8">
                
                <!-- TOP SECTION: Dropdowns -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-8 border-b border-gray-200 pb-8">
                    
                    <!-- 1. Category Filter -->
                    <div>
                        <label for="category_select" class="block text-sm font-medium leading-6 text-gray-900">1. Filter by Category</label>
                        <select id="category_select" name="category" class="searchable-select mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">-- Show All Categories --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->name }}" {{ strcasecmp((string)$selectedCategory, (string)$category->name) === 0 ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 2. Product Selection -->
                    <div>
                        <label for="product_id" class="block text-sm font-medium leading-6 text-gray-900">2. Select Product <span class="text-red-500">*</span></label>
                        <select id="product_id" name="product_id" required class="searchable-select mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">-- Select Product --</option>
                            @foreach ($products as $prod)
                                @php
                                    $matchesCategory = empty($selectedCategory) || strcasecmp((string)($prod->category ?? ''), (string)$selectedCategory) === 0;
                                @endphp
                                @if ($matchesCategory || (string)$selectedProductId === (string)$prod->id)
                                    <option value="{{ $prod->id }}" {{ (string)$selectedProductId === (string)$prod->id ? 'selected' : '' }} data-category="{{ $prod->category }}">
                                        [{{ $prod->sku }}] {{ $prod->name }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <!-- 3. Placement Location -->
                    <div class="sm:col-span-2">
                        <label for="location_id" class="block text-sm font-medium leading-6 text-gray-900">3. Receiving Warehouse Rack <span class="text-red-500">*</span></label>
                        <select id="location_id" name="location_id" required class="searchable-select mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <option value="">-- Select a Location --</option>
                            @foreach($locations as $location)
                                <option value="{{ $location->id }}" {{ (string)old('location_id') === (string)$location->id ? 'selected' : '' }}>
                                    {{-- This checks if branch/floor exist, and stitches them together with a dot! --}}
                                    {{ $location->full_path }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Batch / Lot Number -->
                    <div>
                        <label for="batch_no" class="block text-sm font-medium leading-6 text-gray-900">4. Batch / Lot # (Optional)</label>
                        <input type="text" id="batch_no" name="batch_no" value="{{ old('batch_no') }}" placeholder="e.g. LOT-2026-B01" class="mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>

                    <!-- 5. Acquisition Unit Cost -->
                    <div>
                        <label for="unit_cost" class="block text-sm font-medium leading-6 text-gray-900">5. Unit Acquisition Cost (RM, Optional)</label>
                        <input type="number" step="0.01" min="0" id="unit_cost" name="unit_cost" value="{{ old('unit_cost') }}" placeholder="e.g. 1250.00" class="mt-2 block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                    </div>
                </div>

                <!-- BOTTOM SECTION: Batch Add Serial Numbers -->
                <div>
                    <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-500 mb-4">Batch Add Serial Numbers</h3>

                    <!-- Input and Quantity Row -->
                    <div class="flex flex-col sm:flex-row sm:items-center gap-3 sm:gap-6 mb-6">
                        
                        <!-- Serial Input -->
                        <div class="flex-1 relative">
                            <input type="text" id="temp_sn" placeholder="Scan or type serial number and press Enter..." class="block w-full rounded-md border-0 px-3 py-2 pr-20 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                            <button type="button" onclick="addSerialNumber()" class="absolute inset-y-0 right-0 flex items-center rounded-r-md px-5 bg-slate-800 text-white font-medium text-sm hover:bg-slate-700 transition-colors cursor-pointer">
                                Add
                            </button>
                        </div>

                        <!-- Auto-Calculated Quantity -->
                        <div class="flex items-center gap-3 shrink-0 bg-gray-50 px-4 py-2 rounded-md ring-1 ring-gray-200">
                            <label class="block text-sm font-medium text-gray-700">Total Quantity:</label>
                            <input type="text" id="display_quantity" value="{{ count($oldSerials) }}" readonly class="block w-16 rounded border-0 py-1 text-center bg-white font-bold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm focus:ring-0">
                        </div>
                    </div>

                    <!-- Dynamic Table -->
                    <div class="ring-1 ring-gray-200 rounded-lg overflow-hidden">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3 pl-4 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Scanned Serial Numbers</th>
                                    <th scope="col" class="py-3 pr-4 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                                </tr>
                            </thead>
                            <tbody id="sn_table_body" class="divide-y divide-gray-200 bg-white">
                                @if (empty($oldSerials))
                                    <!-- Empty state row -->
                                    <tr id="empty_row">
                                        <td colspan="2" class="py-8 text-center text-sm text-gray-400 italic">No serial numbers added yet. The list will generate here as you scan.</td>
                                    </tr>
                                @else
                                    @foreach ($oldSerials as $sn)
                                        @php
                                            $snStr = is_scalar($sn) ? (string)$sn : '';
                                        @endphp
                                        @if ($snStr !== '')
                                            <tr class="hover:bg-gray-50 transition-colors">
                                                <td class="whitespace-nowrap py-3 pl-4 text-sm font-medium text-gray-900">
                                                    {{ $snStr }}
                                                    <input type="hidden" name="serial_numbers[]" value="{{ $snStr }}">
                                                </td>
                                                <td class="whitespace-nowrap py-3 pr-4 text-right text-sm font-medium">
                                                    <button type="button" onclick="removeSerialNumber(this)" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded text-xs cursor-pointer">Remove</button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
            
            <!-- Submit Footer -->
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-all">
                    Save to Inventory
                </button>
            </div>
        </div>
    </form>

    <script>
        // --- 1. Product & Category Filtering & Synchronization ---
        const allProducts = @json($products);
        let lastCategory = "{{ addslashes($selectedCategory) }}";
        let lastProduct = "{{ addslashes($selectedProductId) }}";

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text ?? '';
            return div.innerHTML;
        }

        function syncProductsToCategory(resetProductIfMismatch = true) {
            const categorySelect = document.getElementById('category_select');
            const productSelect = document.getElementById('product_id');
            if (!categorySelect || !productSelect) return;

            const selectedCategory = (categorySelect.value || '').trim();
            const currentProductId = productSelect.value || '';

            // Check if currentProductId matches selectedCategory
            const currentProduct = allProducts.find(p => String(p.id) === String(currentProductId));
            const currentProductMatches = currentProduct && (!selectedCategory || (currentProduct.category || '').toLowerCase() === selectedCategory.toLowerCase());

            const targetProductId = (currentProductMatches && !resetProductIfMismatch) ? String(currentProductId) : (currentProductMatches ? String(currentProductId) : '');

            // Filter matching products
            const matchingProducts = allProducts.filter(prod => {
                if (!selectedCategory) return true;
                return (prod.category || '').toLowerCase() === selectedCategory.toLowerCase();
            });

            const ts = productSelect.tomselect;
            if (ts) {
                ts.clear(true);
                ts.clearOptions();
                ts.addOption({ value: '', text: '-- Select Product --' });
                matchingProducts.forEach(prod => {
                    ts.addOption({
                        value: String(prod.id),
                        text: `[${prod.sku}] ${prod.name}`
                    });
                });
                ts.refreshOptions(false);
                if (targetProductId) {
                    ts.setValue(String(targetProductId), true);
                } else {
                    ts.setValue('', true);
                }
            }

            // Also update native select options for DOM integrity and fallback
            productSelect.innerHTML = '<option value="">-- Select Product --</option>';
            matchingProducts.forEach(prod => {
                const opt = document.createElement('option');
                opt.value = prod.id;
                opt.text = `[${prod.sku}] ${prod.name}`;
                if (String(prod.id) === String(targetProductId)) {
                    opt.selected = true;
                }
                productSelect.appendChild(opt);
            });

            lastProduct = targetProductId;
        }

        function onProductSelected() {
            const categorySelect = document.getElementById('category_select');
            const productSelect = document.getElementById('product_id');
            if (!categorySelect || !productSelect) return;

            const selectedProductId = productSelect.value || '';
            lastProduct = selectedProductId;
            if (!selectedProductId) return;

            const prod = allProducts.find(p => String(p.id) === String(selectedProductId));
            if (prod && prod.category) {
                const currentCat = categorySelect.value || '';
                if (currentCat.toLowerCase() !== prod.category.toLowerCase()) {
                    lastCategory = prod.category;
                    const catTs = categorySelect.tomselect;
                    if (catTs) {
                        catTs.setValue(prod.category, true);
                    } else {
                        categorySelect.value = prod.category;
                    }
                }
            }
        }

        function onCategoryChange() {
            const categorySelect = document.getElementById('category_select');
            if (!categorySelect) return;
            const val = categorySelect.value || '';
            if (val === lastCategory) return;
            lastCategory = val;
            syncProductsToCategory(false);
        }

        function onProductChange() {
            const productSelect = document.getElementById('product_id');
            if (!productSelect) return;
            const val = productSelect.value || '';
            if (val === lastProduct) return;
            lastProduct = val;
            onProductSelected();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const categorySelect = document.getElementById('category_select');
            const productSelect = document.getElementById('product_id');

            if (categorySelect) {
                categorySelect.addEventListener('change', onCategoryChange);
            }
            if (productSelect) {
                productSelect.addEventListener('change', onProductChange);
            }

            // Attach to TomSelect instances once initialized by global layout script
            setTimeout(function() {
                if (categorySelect && categorySelect.tomselect) {
                    categorySelect.tomselect.on('change', onCategoryChange);
                }
                if (productSelect && productSelect.tomselect) {
                    productSelect.tomselect.on('change', onProductChange);
                }
            }, 50);
        });

        // --- 2. Dynamic Serial Number Table Logic ---
        function addSerialNumber() {
            const inputField = document.getElementById('temp_sn');
            const snValue = inputField.value.trim();
            const tableBody = document.getElementById('sn_table_body');
            const emptyRow = document.getElementById('empty_row');

            if (snValue === "") {
                inputField.focus();
                return;
            }

            // Remove the empty state message if it exists
            if (emptyRow) { emptyRow.remove(); }

            // Create new table row
            const tr = document.createElement('tr');
            tr.className = "hover:bg-gray-50 transition-colors";
            
            const safeSn = escapeHtml(snValue);
            tr.innerHTML = `
                <td class="whitespace-nowrap py-3 pl-4 text-sm font-medium text-gray-900">
                    ${safeSn}
                    <input type="hidden" name="serial_numbers[]" value="${safeSn}">
                </td>
                <td class="whitespace-nowrap py-3 pr-4 text-right text-sm font-medium">
                    <button type="button" onclick="removeSerialNumber(this)" class="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded text-xs cursor-pointer">Remove</button>
                </td>
            `;

            tableBody.prepend(tr); // Add new item to the TOP of the list
            
            // Cleanup and recount
            inputField.value = "";
            inputField.focus();
            updateQuantity();
        }

        function removeSerialNumber(buttonElement) {
            const row = buttonElement.closest('tr');
            if (row) {
                row.remove();
            }
            
            const tableBody = document.getElementById('sn_table_body');
            const remaining = tableBody.querySelectorAll('input[name="serial_numbers[]"]');
            if (remaining.length === 0) {
                tableBody.innerHTML = `
                    <tr id="empty_row">
                        <td colspan="2" class="py-8 text-center text-sm text-gray-400 italic">No serial numbers added yet. The list will generate here as you scan.</td>
                    </tr>
                `;
            }
            updateQuantity();
        }

        function updateQuantity() {
            const count = document.querySelectorAll('input[name="serial_numbers[]"]').length;
            document.getElementById('display_quantity').value = count;
        }

        // Prevent the "Enter" key from submitting the entire page when scanning barcodes
        document.getElementById('temp_sn')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSerialNumber();
            }
        });
    </script>
</x-app-layout>
