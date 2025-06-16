<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Mail\InvoiceMail;
use App\Models\Invoice;
use App\Notifications\InvoiceNotification;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;

class GenerateInvoiceAndSendMail
{

    public function __construct()
    {
        //
    }
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        $invoice = Invoice::create([
            'invoice_number' => 'INV-' . strtoupper(uniqid()),
            'customer_id' => $order->customer_id,
            'total' => $order->total_price,
            'status' => $order->payment_status,
            'due_date' => now()->addDays(30),
        ]);

        $pdf = PDF::loadView('pdf.invoice', ['invoice' => $invoice]);

        Mail::to($order->customer->email)->send(new InvoiceMail($invoice));
    }
}
