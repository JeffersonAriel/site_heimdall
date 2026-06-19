<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Models\Order;
use Modules\Orders\Models\OrderItem;
use Modules\Orders\Events\OrderCreated;
use Modules\Products\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class CheckoutController extends Controller
{
    /**
     * Process checkout with guest customer creation.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        // Guest checkout: create customer silently if not exists
        $wasRecentlyCreated = !Customer::where('email', $request->email)->exists();
        $customer = Customer::firstOrCreate(
            ['email' => $request->email],
            ['name' => $request->name, 'status' => 'active']
        );

        if ($wasRecentlyCreated) {
            event(new \Modules\CRM\Events\CustomerConverted($customer));
        }

        // Calculate total from product prices
        $total = 0;
        $itemsData = [];
        foreach ($request->items as $item) {
            $product = Product::findOrFail($item['product_id']);
            $total += $product->price * $item['quantity'];
            $itemsData[] = [
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ];
        }

        // Apply discount coupon if provided
        $discount = 0;
        if ($request->filled('coupon_code')) {
            $coupon = \Modules\Orders\Models\Coupon::where('code', $request->coupon_code)
                ->where(function ($query) {
                    $query->whereNull('expires_at')
                          ->orWhere('expires_at', '>=', now()->toDateString());
                })
                ->first();

            if ($coupon && $total >= $coupon->min_order_value) {
                if ($coupon->type === 'percentage') {
                    $discount = $total * ($coupon->value / 100);
                } else {
                    $discount = $coupon->value;
                }
            }
        }

        $total = max(0, $total - $discount);

        // Create order
        $order = Order::create([
            'customer_id' => $customer->id,
            'total' => $total,
            'status' => 'pending',
        ]);

        // Create items
        foreach ($itemsData as $itemData) {
            $order->items()->create($itemData);
        }

        // Load items for the event
        $order->load('items');

        // Fire event — triggers StockReserve + FinancialCreation
        OrderCreated::dispatch($order);

        return response()->json([
            'message' => 'Pedido criado com sucesso!',
            'order' => $order->load('items', 'customer'),
        ], 201);
    }
}
