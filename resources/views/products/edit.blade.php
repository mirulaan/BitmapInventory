<x-app-layout>
    <div class="max-w-4xl mx-auto space-y-6 pb-12">
        
        <!-- Header -->
        <div class="sm:flex sm:items-center sm:justify-between">
            <div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-indigo-600 hover:text-indigo-800 transition-colors inline-flex items-center gap-1">
                        &larr; Product Catalog
                    </a>
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-slate-900 mt-1">Edit Product Model</h1>
                <p class="mt-1 text-sm text-slate-500">Update specifications, pricing, and visual media for <span class="font-mono font-semibold text-slate-800">{{ $product->sku }}</span>.</p>
            </div>
            <div class="mt-4 sm:mt-0 flex gap-2">
                <a href="{{ route('products.index') }}" class="rounded-xl bg-white border border-slate-300 px-4 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                    Back to Catalog
                </a>
            </div>
        </div>

        @if($errors->any())
            <div class="rounded-2xl bg-rose-50 border border-rose-200 p-4">
                <div class="flex items-center gap-2 text-rose-800 font-semibold text-sm">
                    <svg class="w-4 h-4 text-rose-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                    </svg>
                    Please correct the following errors:
                </div>
                <ul class="mt-2 list-disc list-inside text-xs text-rose-700 space-y-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <div class="bg-white border border-slate-200/80 rounded-2xl shadow-sm overflow-hidden">
                <div class="p-6 sm:p-8 space-y-6">
                    
                    <!-- Media Asset Upload Section -->
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                            Product Reference Image / Icon
                        </label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5">
                            
                            <!-- Current Image / Live Preview Box -->
                            <div class="w-24 h-24 rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 flex items-center justify-center overflow-hidden shrink-0 relative group">
                                @if($product->image_url)
                                    <img id="image_preview" src="{{ $product->image_url }}" alt="{{ $product->name }}" class="w-full h-full object-contain p-1">
                                    <div id="preview_placeholder" class="flex flex-col items-center justify-center text-slate-400 p-2 text-center hidden">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        <span class="text-[10px] font-medium mt-1">No Image</span>
                                    </div>
                                @else
                                    <img id="image_preview" src="" alt="Preview" class="w-full h-full object-contain hidden p-1">
                                    <div id="preview_placeholder" class="flex flex-col items-center justify-center text-slate-400 p-2 text-center">
                                        <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                        </svg>
                                        <span class="text-[10px] font-medium mt-1">No Image</span>
                                    </div>
                                @endif
                            </div>

                            <!-- Upload & Removal Controls -->
                            <div class="flex-1 space-y-2">
                                <div class="flex flex-wrap items-center gap-3">
                                    <label for="image_file_input" class="cursor-pointer inline-flex items-center rounded-xl bg-white border border-slate-300 px-3.5 py-2 text-xs font-semibold text-slate-700 shadow-sm hover:bg-slate-50 transition-colors">
                                        <svg class="w-4 h-4 mr-1.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                                        </svg>
                                        {{ $product->image_url ? 'Replace Image' : 'Choose File' }}
                                    </label>

                                    @if($product->image_url)
                                        <label class="inline-flex items-center gap-2 cursor-pointer text-xs text-rose-600 hover:text-rose-800 font-semibold transition-colors bg-rose-50 px-2.5 py-1.5 rounded-xl border border-rose-200">
                                            <input type="checkbox" name="remove_image" id="remove_image" value="1" class="rounded border-rose-300 text-rose-600 focus:ring-rose-500" onchange="toggleRemoveImage(this)">
                                            Remove Current Image
                                        </label>
                                    @endif

                                    <button type="button" id="clear_new_image_btn" onclick="clearNewImageSelection()" class="hidden text-xs text-rose-600 hover:text-rose-800 font-semibold transition-colors">
                                        Cancel New Image
                                    </button>
                                </div>
                                <input type="file" id="image_file_input" accept="image/png,image/jpeg,image/webp,image/svg+xml" class="sr-only" onchange="previewSelectedImage(this)">
                                <input type="hidden" name="image_base64" id="image_base64">
                                <p class="text-xs text-slate-500 flex flex-wrap items-center gap-1.5">
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200 text-[11px]">
                                        <svg class="w-3 h-3 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                        </svg>
                                        Auto-Compressed
                                    </span>
                                    <span>High-resolution photos are automatically optimized to required web dimensions upon loading.</span>
                                </p>
                                <div id="compression_status" class="hidden text-xs font-semibold text-emerald-700 items-center gap-1.5 mt-1 bg-emerald-50/70 py-1 px-2.5 rounded-lg border border-emerald-200/60 w-fit"></div>
                                @error('image') <p class="text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                            </div>

                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6 grid grid-cols-1 gap-y-6 gap-x-6 sm:grid-cols-6">
                        
                        <!-- SKU -->
                        <div class="sm:col-span-2">
                            <label for="sku" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Master SKU <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="sku" id="sku" value="{{ old('sku', $product->sku) }}" required class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 font-mono">
                            @error('sku') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Product Name -->
                        <div class="sm:col-span-4">
                            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Product Model Name <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            @error('name') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Brand / Manufacturer -->
                        <div class="sm:col-span-2">
                            <label for="brand" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2 flex items-center justify-between">
                                <span>Brand / Manufacturer</span>
                                <a href="{{ route('brands.index') }}" target="_blank" class="text-[10px] text-indigo-600 hover:underline font-semibold lowercase">+ add brand</a>
                            </label>
                            @php $savedBrand = old('brand', $product->brand); @endphp
                            <select id="brand" name="brand" class="searchable-select block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                <option value="">-- Select Brand --</option>
                                @foreach($brands as $brandItem)
                                    <option value="{{ $brandItem->name }}" {{ strcasecmp((string)$savedBrand, $brandItem->name) === 0 ? 'selected' : '' }}>
                                        {{ $brandItem->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('brand') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Category -->
                        <div class="sm:col-span-2">
                            <label for="category" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Category <span class="text-rose-500">*</span>
                            </label>
                            <select id="category" name="category" required class="searchable-select block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                                @php $savedCategory = old('category', $product->category); @endphp
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->name }}" {{ strcasecmp($savedCategory, $cat->name) === 0 ? 'selected' : '' }}>
                                        {{ $cat->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('category') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Unit Price -->
                        <div class="sm:col-span-2">
                            <label for="unit_price" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Default Unit Price (RM)
                            </label>
                            <div class="relative rounded-xl shadow-sm">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
                                    <span class="text-xs font-bold text-slate-400">RM</span>
                                </div>
                                <input type="number" step="0.01" min="0" name="unit_price" id="unit_price" value="{{ old('unit_price', $product->unit_price) }}" placeholder="0.00" class="block w-full rounded-xl border border-slate-200 pl-11 pr-3.5 py-2 text-sm text-slate-900 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            </div>
                            @error('unit_price') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Min Stock Alert Level -->
                        <div class="sm:col-span-2">
                            <label for="min_stock_level" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Min Stock Alert Level
                            </label>
                            <input type="number" min="0" name="min_stock_level" id="min_stock_level" value="{{ old('min_stock_level', $product->min_stock_level ?? 0) }}" placeholder="0" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
                            <p class="mt-1 text-[11px] text-slate-400">Alerts when warehouse units &le; threshold.</p>
                            @error('min_stock_level') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                        <!-- Description -->
                        <div class="sm:col-span-4">
                            <label for="description" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Technical Specifications & Notes
                            </label>
                            <textarea id="description" name="description" rows="3" class="block w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm text-slate-900 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">{{ old('description', $product->description) }}</textarea>
                            @error('description') <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p> @enderror
                        </div>

                    </div>

                </div>

                <!-- Footer Bar -->
                <div class="p-4 sm:px-8 bg-slate-50/80 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('products.index') }}" class="text-xs font-bold text-slate-600 hover:text-slate-800 transition-colors">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-xl bg-indigo-600 px-5 py-2.5 text-xs font-bold text-white shadow-sm hover:bg-indigo-500 hover:shadow transition-all cursor-pointer">
                        Update Product Model
                    </button>
                </div>
            </div>

        </form>

    </div>

    <!-- Live Auto-Compress & Preview Script -->
    <script>
        const originalImageUrl = @json($product->image_url);

        function previewSelectedImage(input) {
            const preview = document.getElementById('image_preview');
            const placeholder = document.getElementById('preview_placeholder');
            const statusEl = document.getElementById('compression_status');
            const clearNewBtn = document.getElementById('clear_new_image_btn');
            const base64Input = document.getElementById('image_base64');
            const removeCheckbox = document.getElementById('remove_image');

            if (!input.files || !input.files[0]) return;
            const file = input.files[0];

            if (removeCheckbox) {
                removeCheckbox.checked = false;
                preview.classList.remove('opacity-30');
            }

            // If SVG vector, display and store directly
            if (file.type === 'image/svg+xml') {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                    if (clearNewBtn) clearNewBtn.classList.remove('hidden');
                    if (base64Input) base64Input.value = e.target.result;
                    if (statusEl) {
                        statusEl.innerHTML = `✓ Vector SVG loaded (${(file.size / 1024).toFixed(1)} KB)`;
                        statusEl.classList.remove('hidden');
                    }
                };
                reader.readAsDataURL(file);
                return;
            }

            // Display temporary compression progress status
            if (statusEl) {
                statusEl.innerHTML = `<span class="inline-block animate-pulse text-indigo-600 font-bold">⚙ Auto-compressing image...</span>`;
                statusEl.classList.remove('hidden');
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    const maxDim = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > maxDim || height > maxDim) {
                        if (width > height) {
                            height = Math.round((height * maxDim) / width);
                            width = maxDim;
                        } else {
                            width = Math.round((width * maxDim) / height);
                            height = maxDim;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.imageSmoothingEnabled = true;
                    ctx.imageSmoothingQuality = 'high';
                    ctx.drawImage(img, 0, 0, width, height);

                    const mimeType = 'image/jpeg';
                    const quality = 0.82;
                    const dataUrl = canvas.toDataURL(mimeType, quality);

                    // Set base64 input
                    if (base64Input) {
                        base64Input.value = dataUrl;
                    }

                    // Display preview
                    preview.src = dataUrl;
                    preview.classList.remove('hidden');
                    if (placeholder) placeholder.classList.add('hidden');
                    if (clearNewBtn) clearNewBtn.classList.remove('hidden');

                    if (statusEl) {
                        const origSize = file.size > 1024 * 1024 
                            ? (file.size / (1024 * 1024)).toFixed(1) + ' MB'
                            : (file.size / 1024).toFixed(0) + ' KB';
                        const compSizeKb = Math.round((dataUrl.length * (3/4)) / 1024);
                        statusEl.innerHTML = `✓ Auto-compressed from ${origSize} down to ${compSizeKb} KB (${width}×${height}px)`;
                        statusEl.classList.remove('hidden');
                    }
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        }

        function clearNewImageSelection() {
            const input = document.getElementById('image_file_input');
            const base64Input = document.getElementById('image_base64');
            const preview = document.getElementById('image_preview');
            const placeholder = document.getElementById('preview_placeholder');
            const clearNewBtn = document.getElementById('clear_new_image_btn');
            const statusEl = document.getElementById('compression_status');

            if (input) input.value = '';
            if (base64Input) base64Input.value = '';
            if (clearNewBtn) clearNewBtn.classList.add('hidden');
            if (statusEl) {
                statusEl.innerHTML = '';
                statusEl.classList.add('hidden');
            }

            if (originalImageUrl) {
                preview.src = originalImageUrl;
                preview.classList.remove('hidden', 'opacity-30');
                if (placeholder) placeholder.classList.add('hidden');
            } else {
                preview.src = '';
                preview.classList.add('hidden');
                if (placeholder) placeholder.classList.remove('hidden');
            }
        }

        function toggleRemoveImage(checkbox) {
            const preview = document.getElementById('image_preview');
            const placeholder = document.getElementById('preview_placeholder');
            const statusEl = document.getElementById('compression_status');

            if (checkbox.checked) {
                clearNewImageSelection();
                preview.classList.add('opacity-30');
            } else {
                preview.classList.remove('opacity-30');
            }
        }
    </script>
</x-app-layout>
