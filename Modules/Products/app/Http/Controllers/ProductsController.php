<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Products\Models\Product;

class ProductsController extends Controller
{
    /**
     * Public list of active products for e-commerce.
     */
    public function index()
    {
        $products = Product::where('status', 'active')->get();
        return response()->json($products);
    }

    /**
     * Show a single product (public).
     */
    public function show($id)
    {
        $product = Product::where('status', 'active')->findOrFail($id);
        return response()->json($product);
    }

    /**
     * Create product (ERP only - requires auth).
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|unique:products,sku',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'stock_control' => 'boolean',
            'status' => 'in:active,inactive,draft',
        ]);

        $product = Product::create($request->all());

        event(new \Modules\Products\Events\ProductCreated($product));

        return response()->json($product, 201);
    }

    /**
     * Update product (ERP only).
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->all());
        return response()->json($product);
    }

    /**
     * Delete product (ERP only - soft delete).
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $product->delete();
        return response()->json(['message' => 'Produto removido.']);
    }
}
