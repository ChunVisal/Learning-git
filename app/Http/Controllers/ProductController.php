<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Redirect;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')->get();

        if ($request->ajax()) {
            return response()->json($products);
        }

        return view('products.index');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
        ]);

        $category = Category::firstOrCreate(['name' => $validated['category']]);

        $product = Product::create([
            ...$validated,
            'category_id' => $category->id,
        ]);

        if ($product->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'type'       => 'in',
                'quantity'      => $product->stock,
                'reason'        => 'Initial stock',
                'balance_after' => $product->stock,
            ]);
        }

        return response()->json($product->load('category'));
    }

    public function update(Request $request, int $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name'  => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'category' => 'required|string|max:255',
        ]);

        $category = Category::firstOrCreate(['name' => $validated['category']]);

        $oldStock = $product->stock;
        $product->update([
            ...$validated,
            'category_id' => $category->id,
        ]);

        $newStock = $product->stock;

        if ($newStock !== $oldStock) {
            $diff = $newStock - $oldStock;

            StockMovement::create([
                'product_id'    => $product->id,
                'type'          => $diff > 0 ? 'in' : 'out',
                'quantity'      => abs($diff),
                'reason'        => 'Manual adjustment',
                'balance_after' => $newStock,
            ]);
        }

        return response()->json($product->load('category'));
    }

    public function destroy(int $id)
    {
        $product = Product::findOrFail($id);

        if ($product->stockMovements()->exists()) {
            return response()->json([
                'message' => 'Cannot delete this product because it has stock movement history.'
            ], 422);
        }
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
