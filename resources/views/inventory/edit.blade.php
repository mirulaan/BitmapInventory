<x-app-layout>
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Hardware Details</h1>
            <p class="mt-1 text-sm text-gray-500">Update classification, physical location, or serial number.</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <a href="{{ url()->previous() }}" class="text-sm font-semibold leading-6 text-gray-900 hover:text-gray-700">&larr; Back</a>
        </div>
    </div>

    <!-- GLOBAL ERROR CATCHER -->
    @if ($errors->any())
        <div class="rounded-md bg-red-50 p-4 mb-6 ring-1 ring-red-200 shadow-sm">
            <h3 class="text-sm font-medium text-red-800">Could not update item. Please fix these errors:</h3>
            <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                <?php foreach($errors->all() as$error): ?>
                    <li>{{ $error }}</li>
                <?php endforeach; ?>
            </ul>
        </div>
    @endif

    @php
        $currentCategory =$item->product ? $item->product->category : '';$currentLocation = old('location_id', $item->location_id);$endDateDisplay = 'No Date Set';
        if ($item->status === 'on_loan' && $item->currentLoan &&$item->currentLoan->loan_end_date) {
            $endDateDisplay = \Carbon\Carbon::parse($item->currentLoan->loan_end_date)->format('d M Y');
        }
    @endphp

    <form action="{{ route('inventory.update', $item->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="bg-white ring-1 ring-gray-200 shadow-sm sm:rounded-xl overflow-hidden max-w-3xl">
            <div class="px-4 py-6 sm:p-8">
                
                <div class="grid grid-cols-1 gap-x-6 gap-y-8 sm:grid-cols-6">
                    
                    <!-- 1. Category Filter -->
                    <div class="sm:col-span-3">
                        <label for="category_select" class="block text-sm font-medium leading-6 text-gray-900">Filter by Category</label>
                        <div class="mt-2">
                            <select id="category_select" onchange="filterProducts()" class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value=""disabled>-- Show All Categories --</option>
                                <?php foreach($categories as $category): ?>
                                    <option value="{{ $category->name }}" <?= (trim($currentCategory) == trim($category->name)) ? 'selected' : '' ?>>
                                        {{ $category->name }}
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- 2. Product Selection -->
                    <div class="sm:col-span-3">
                        <label for="product_id" class="block text-sm font-medium leading-6 text-gray-900">Hardware Model <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="product_id" name="product_id" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <option value="">-- Select Product --</option>
                                <!-- Options populated by JavaScript -->
                            </select>
                        </div>
                    </div>

                    <!-- Location Edit -->
                    <div class="sm:col-span-6">
                        <label for="location_id" class="block text-sm font-medium leading-6 text-gray-900">Physical Location <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <select id="location_id" name="location_id" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                                <?php foreach($locations as$location): ?>
                                    <option value="{{ $location->id }}" @if($currentLocation ==$location->id) selected @endif>
                                        [{{ ucfirst(str_replace('_', ' ', $location->type)) }}] {{$location->name }}
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Serial Number -->
                    <div class="sm:col-span-6">
                        <label for="serial_number" class="block text-sm font-medium leading-6 text-gray-900">Serial Number <span class="text-red-500">*</span></label>
                        <div class="mt-2">
                            <input type="text" name="serial_number" id="serial_number" value="{{ old('serial_number', $item->serial_number) }}" required class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6">
                        </div>
                    </div>

                    <!-- CONDITIONAL: Extend Loan Block -->
                    @if ($item->status === 'on_loan')
                        <div class="sm:col-span-6 border-t border-gray-200 pt-8 mt-2">
                            <h3 class="text-sm font-semibold uppercase tracking-wider text-amber-600 mb-4">Active Loan Management</h3>
                            
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-medium leading-6 text-gray-900">Current End Date</label>
                                    <div class="mt-2">
                                        <input type="text" disabled value="{{ $endDateDisplay }}" class="block w-full rounded-md border-0 px-3 py-1.5 text-gray-500 bg-gray-50 shadow-sm ring-1 ring-inset ring-gray-300 sm:text-sm sm:leading-6 focus:ring-0">
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="extend_days" class="block text-sm font-medium leading-6 text-gray-900">Extend Loan By (Days)</label>
                                    <div class="mt-2">
                                        <div class="flex rounded-md shadow-sm ring-1 ring-inset ring-gray-300 focus-within:ring-2 focus-within:ring-inset focus-within:ring-indigo-600 w-full sm:max-w-md">
                                            <input type="number" name="extend_days" id="extend_days" min="1" placeholder="e.g. 14" class="block w-full border-0 bg-transparent py-1.5 pl-3 text-gray-900 placeholder:text-gray-400 focus:ring-0 sm:text-sm sm:leading-6">
                                            <span class="flex select-none items-center pr-3 text-gray-500 sm:text-sm">Days</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                </div>
            </div>
            
            <div class="flex items-center justify-end gap-x-6 border-t border-gray-900/10 bg-gray-50 px-4 py-4 sm:px-8">
                <button type="submit" class="rounded-md bg-indigo-600 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 cursor-pointer transition-all">
                    Update Hardware Details
                </button>
            </div>
        </div>
    </form>

    <script>
        const allProducts = @json($products);
        const selectedProductId = "{{ old('product_id', $item->product_id) }}";

        function initializeDropdowns() {
            const categorySelect = document.getElementById('category_select');

            //categorySelect.innerHTML = '<option value="" disabled selected>-- Show All Categories --</option>';
            
            // Auto-select the category based on the current product using strict matching
            if (selectedProductId) {
                const currentProduct = allProducts.find(p => p.id == selectedProductId);
                if (currentProduct && currentProduct.category) {
                    const targetCategory = currentProduct.category.toLowerCase().trim();
                    
                    // Loop through the dropdown options and force a match
                    for (let i = 0; i < categorySelect.options.length; i++) {
                        if (categorySelect.options[i].value.toLowerCase().trim() === targetCategory) {
                            categorySelect.selectedIndex = i;
                            break;
                        }
                    }
                }
            }
            
            // Now populate the products list
            filterProducts();
        }

        function filterProducts() {
            const selectedCategory = document.getElementById('category_select').value;
            const productSelect = document.getElementById('product_id');
            
            productSelect.innerHTML = '<option value="" disabled selected>-- Select Product --</option>';

            allProducts.forEach(prod => {
                const dbCategory = (prod.category || "").toLowerCase().trim();
                const filterCategory = selectedCategory.toLowerCase().trim();

                // Show product if category matches, or if no category is selected
                if (selectedCategory === "" || dbCategory === filterCategory) {
                    const opt = document.createElement('option');
                    opt.value = prod.id;
                    opt.text = `[${prod.sku}] ${prod.name}`;
                    
                    // Pre-select the exact product
                    if (selectedProductId == prod.id) { 
                        opt.selected = true; 
                    }
                    
                    productSelect.appendChild(opt);
                }
            });
        }

        // Run the initialization sequence on load
        document.addEventListener('DOMContentLoaded', initializeDropdowns);
    </script>
</x-app-layout>