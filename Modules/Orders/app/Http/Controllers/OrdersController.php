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
        $query = Order::with(['items.product', 'customer']);

        if (!($request->user() instanceof \App\Models\User)) {
            $query->where('customer_id', $request->user()->id);
        }

        $orders = $query->latest()->get();
        return response()->json($orders);
    }

    /**
     * Show a single order.
     */
    public function show(Request $request, $id)
    {
        $query = Order::with(['items.product', 'customer']);

        if (!($request->user() instanceof \App\Models\User)) {
            $query->where('customer_id', $request->user()->id);
        }

        $order = $query->findOrFail($id);
        return response()->json($order);
    }
}
