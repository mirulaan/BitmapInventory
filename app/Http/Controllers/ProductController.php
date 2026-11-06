<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    /**
     * Display a paginated listing of catalog products with filters, sorting, and stock counts.
     */
    public function index(Request $request)
    {
        $query = Product::withCount([
            'inventoryItems as total_qty',
            'inventoryItems as in_stock_qty' => function ($q) {
                $q->where('status', 'in_stock');
            },
            'inventoryItems as on_loan_qty' => function ($q) {
                $q->where('status', 'on_loan');
            },
            'inventoryItems as sold_qty' => function ($q) {
                $q->where('status', 'sold');
            },
        ]);

        $like = \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search, $like) {
                $q->where('name', $like, "%{$search}%")
                    ->orWhere('sku', $like, "%{$search}%")
                    ->orWhere('brand', $like, "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('brand')) {
            $query->where('brand', $request->brand);
        }

        if ($request->get('stock_alert') === 'low_stock') {
            $query->whereRaw('(SELECT COUNT(*) FROM inventory_items WHERE inventory_items.product_id = products.id AND inventory_items.status = \'in_stock\') <= products.min_stock_level AND products.min_stock_level > 0');
        }

        $sort = $request->get('sort', 'name_asc');
        if ($sort === 'name_desc') {
            $query->orderBy('name', 'desc');
        } elseif ($sort === 'brand_asc') {
            $query->orderBy('brand', 'asc')->orderBy('name', 'asc');
        } elseif ($sort === 'brand_desc') {
            $query->orderBy('brand', 'desc')->orderBy('name', 'asc');
        } elseif ($sort === 'price_high') {
            $query->orderBy('unit_price', 'desc');
        } elseif ($sort === 'price_low') {
            $query->orderBy('unit_price', 'asc');
        } else {
            $query->orderBy('name', 'asc');
        }

        $products = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->pluck('name');
        if ($brands->isEmpty()) {
            $brands = Product::whereNotNull('brand')
                ->where('brand', '!=', '')
                ->distinct()
                ->orderBy('brand')
                ->pluck('brand');
        }

        return view('products.index', compact('products', 'categories', 'brands'));
    }

    /**
     * Show the form for creating a new product model.
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('products.create', compact('categories', 'brands'));
    }

    /**
     * Store a newly created product in the catalog with validated image upload.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku' => 'required|string|unique:products,sku|max:255',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'unit_price' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:32768',
            'image_base64' => 'nullable|string',
        ], [
            'image.image' => 'The file uploaded must be a valid image (JPEG, PNG, WEBP, or SVG).',
        ]);

        $imagePath = null;
        if ($request->filled('image_base64')) {
            $imagePath = $this->storeBase64Image($request->image_base64);
        } elseif ($request->hasFile('image')) {
            $imagePath = $this->storeOptimizedImage($request->file('image'));
        }

        Product::create([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'category' => $validated['category'],
            'unit_price' => $validated['unit_price'] ?? null,
            'min_stock_level' => (int) ($validated['min_stock_level'] ?? 0),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('products.index')
            ->with('success', 'New product model successfully added to catalog.');
    }

    /**
     * Show the form for editing an existing product.
     */
    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        $brands = Brand::orderBy('name')->get();

        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    /**
     * Update an existing product, handling new image upload or removal.
     */
    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku' => 'required|string|max:255|unique:products,sku,' . $product->id,
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'category' => 'required|string|max:255',
            'unit_price' => 'nullable|numeric|min:0',
            'min_stock_level' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:32768',
            'image_base64' => 'nullable|string',
            'remove_image' => 'nullable|boolean',
        ], [
            'image.image' => 'The file uploaded must be a valid image (JPEG, PNG, WEBP, or SVG).',
        ]);

        $imagePath = $product->image_path;

        // Check if user requested to remove existing image
        if ($request->boolean('remove_image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = null;
        }

        // Check if new image was uploaded (compressed base64 or file upload)
        if ($request->filled('image_base64')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->storeBase64Image($request->image_base64);
        } elseif ($request->hasFile('image')) {
            if ($imagePath && Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }
            $imagePath = $this->storeOptimizedImage($request->file('image'));
        }

        $product->update([
            'sku' => $validated['sku'],
            'name' => $validated['name'],
            'brand' => $validated['brand'] ?? null,
            'category' => $validated['category'],
            'unit_price' => $validated['unit_price'] ?? null,
            'min_stock_level' => (int) ($validated['min_stock_level'] ?? 0),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
        ]);

        return redirect()->route('products.index')->with('success', 'Product catalog updated successfully.');
    }

    /**
     * Decode and store client-side compressed base64 image data URL.
     */
    protected function storeBase64Image(string $base64Data): ?string
    {
        // Expecting data:image/jpeg;base64,... or data:image/png;base64,... or data:image/svg+xml;base64,...
        if (!preg_match('/^data:image\/([a-zA-Z0-9\+\-\.]+);base64,/', $base64Data, $type)) {
            return null;
        }

        $imageType = strtolower($type[1]);
        if (!in_array($imageType, ['jpg', 'jpeg', 'png', 'webp', 'svg', 'svg+xml'])) {
            return null;
        }

        $data = substr($base64Data, strpos($base64Data, ',') + 1);
        $decoded = base64_decode($data);
        if ($decoded === false) {
            return null;
        }

        $extension = ($imageType === 'jpeg' || $imageType === 'jpg') ? 'jpg' : (($imageType === 'svg' || $imageType === 'svg+xml') ? 'svg' : $imageType);
        $filename = 'products/' . uniqid('prod_', true) . '.' . $extension;

        Storage::disk('public')->put($filename, $decoded);

        return $filename;
    }

    /**
     * Store and optionally optimize/compress uploaded product image.
     */
    protected function storeOptimizedImage($file): string
    {
        // If GD is available on the host environment, optimize and resize to web standard
        if (extension_loaded('gd') && function_exists('imagecreatefromstring')) {
            $mime = $file->getMimeType();
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                try {
                    $imageContent = file_get_contents($file->getRealPath());
                    $source = @imagecreatefromstring($imageContent);
                    if ($source !== false) {
                        $width = imagesx($source);
                        $height = imagesy($source);
                        $maxDim = 1200;

                        if ($width > $maxDim || $height > $maxDim) {
                            if ($width > $height) {
                                $newHeight = (int) round(($height * $maxDim) / $width);
                                $newWidth = $maxDim;
                            } else {
                                $newWidth = (int) round(($width * $maxDim) / $height);
                                $newHeight = $maxDim;
                            }

                            $resized = imagecreatetruecolor($newWidth, $newHeight);
                            if ($mime === 'image/png' || $mime === 'image/webp') {
                                imagealphablending($resized, false);
                                imagesavealpha($resized, true);
                            }

                            imagecopyresampled($resized, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                            imagedestroy($source);
                            $source = $resized;
                        }

                        $filename = 'products/' . uniqid('prod_', true) . '.jpg';
                        ob_start();
                        imagejpeg($source, null, 82);
                        $compressedData = ob_get_clean();
                        imagedestroy($source);

                        Storage::disk('public')->put($filename, $compressedData);
                        return $filename;
                    }
                } catch (\Throwable $e) {
                    // Fall back to standard storage on unexpected error
                }
            }
        }

        return $file->store('products', 'public');
    }

    /**
     * Remove the specified product from catalog if no physical hardware is attached.
     */
    public function destroy(Product $product)
    {
        $hasInventory = InventoryItem::where('product_id', $product->id)->exists();

        if ($hasInventory) {
            return redirect()->route('products.index')
                ->with('error', 'Cannot delete this product because it has physical hardware registered in the inventory. Delete or reassign the hardware first.');
        }

        // Delete associated image file from disk
        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product model deleted successfully.');
    }
}
