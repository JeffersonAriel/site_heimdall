<?php

namespace Modules\Fiscal\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Fiscal\Models\Invoice;
use Modules\Orders\Models\Order;
use Illuminate\Support\Facades\Log;

class FiscalController extends Controller
{
    /**
     * List all invoices.
     */
    public function index()
    {
        $invoices = Invoice::with('order.customer')->orderBy('created_at', 'desc')->get();
        return response()->json($invoices);
    }

    /**
     * Issue invoice for a specific order.
     */
    public function issue(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
        ]);

        // Check if invoice already issued for this order
        $existing = Invoice::where('order_id', $request->order_id)
            ->where('status', 'issued')
            ->first();

        if ($existing) {
            return response()->json(['message' => 'Nota fiscal já emitida para este pedido.'], 400);
        }

        // Stub: simulate fiscal issuance (in production, integrate with SEFAZ provider)
        $invoiceNumber = 'NF-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT);
        $accessKey = strtoupper(bin2hex(random_bytes(22))); // Stub NFe key

        $invoice = Invoice::create([
            'order_id' => $request->order_id,
            'invoice_number' => $invoiceNumber,
            'key' => $accessKey,
            'status' => 'issued',
            'xml_path' => null, // In production, save the XML file path
        ]);

        Log::info("Fiscal: NF-e emitida #{$invoiceNumber} para Pedido #{$request->order_id}");

        return response()->json([
            'message' => 'Nota fiscal emitida com sucesso!',
            'invoice' => $invoice->load('order'),
        ], 201);
    }

    /**
     * Cancel an invoice.
     */
    public function cancel($id)
    {
        $invoice = Invoice::findOrFail($id);

        if ($invoice->status !== 'issued') {
            return response()->json(['message' => 'Somente notas emitidas podem ser canceladas.'], 400);
        }

        $invoice->update(['status' => 'canceled']);

        Log::info("Fiscal: NF-e #{$invoice->invoice_number} cancelada.");

        return response()->json(['message' => 'Nota fiscal cancelada com sucesso.', 'invoice' => $invoice]);
    }

    /**
     * Get invoice by order.
     */
    public function showByOrder($orderId)
    {
        $invoice = Invoice::where('order_id', $orderId)->latest()->first();
        return response()->json($invoice);
    }
}
