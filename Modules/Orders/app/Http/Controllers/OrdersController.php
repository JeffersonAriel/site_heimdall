<?php

namespace Modules\Orders\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Orders\Models\Order;

class OrdersController extends Controller
{
    /**
     * List orders for authenticated customer.
     */
    public function index(Request $request)
    {
        $orders = Order::with('items.product')
            ->where('customer_id', auth()->id())
            ->latest()
            ->get();

        return response()->json($orders);
    }

    /**
     * Show a single order.
     */
    public function show($id)
    {
        $order = Order::with('items.product')
            ->where('customer_id', auth()->id())
            ->findOrFail($id);

        return response()->json($order);
    }
}
