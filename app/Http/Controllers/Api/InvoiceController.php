<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade as PDF;

class InvoiceController extends Controller
{
    public function index()
    {
        $customerId = Auth::guard('customer')->user()->id;

        if (auth('admin')) {
            $invoices = Invoice::latest()->paginate(10);
        } else {
            $invoices = Invoice::where('customer_id', $customerId)->latest()->paginate(10);
        }

        return InvoiceResource::collection($invoices);
    }

    public function show(Invoice $invoice)
    {
        $user = Auth::user();
        if (request()->routeIs('api/customer/*') && $invoice->customer_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        return new InvoiceResource($invoice);
    }


    public function store(Request $request)
    {

        if (!auth('admin')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:users,id',
            'order_id' => 'nullable|exists:orders,id',
            'due_date' => 'required|date',
        ]);

        $order = Order::findOrFail($request->order_id);

        $invoice = Invoice::create([
            'customer_id' => $validated['customer_id'],
            'order_id' => $validated['order_id'] ?? null,
            'total' => $order->total_price,
            'due_date' => $validated['due_date'],
        ]);

        return new InvoiceResource($invoice);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if (auth('customer')) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'paid_at' => 'nullable|date',
        ]);

        $invoice->update([
            'paid_at' => $request->paid_at,
        ]);

        return new InvoiceResource($invoice);
    }

    // public function getInvoicePdf($orderId)
    // {
    //     $order = Order::findOrFail($orderId);
    //     $invoice = Invoice::where('order_id', $order->id)->first();

    //     $pdf = PDF::loadView('pdf.invoice', ['invoice' => $invoice]);

    //     return $pdf->download('invoice-' . $invoice->invoice_number . '.pdf');
    // }
}
