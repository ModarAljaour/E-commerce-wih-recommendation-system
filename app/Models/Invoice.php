<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $appends = ['status'];
    protected $fillable = [
        'invoice_number',
        'customer_id',
        'order_id',
        'total',
        'due_date',
        'paid_at',
    ];

    // Relations :
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }


    public static function boot()
    {
        parent::boot();

        static::creating(function ($invoice) {
            $lastInvoice = self::latest()->first();
            $lastInvoiceNumber = $lastInvoice ? intval(explode('-', $lastInvoice->invoice_number)[2]) : 0;
            $newInvoiceNumber = str_pad($lastInvoiceNumber + 1, 4, '0', STR_PAD_LEFT);
            $invoice->invoice_number = 'INV-' . now()->year . '-' . $newInvoiceNumber;
        });
    }


    public function getStatusAttribute($value)
    {
        if ($this->paid_at) {
            return 'paid';
        } elseif (now()->gt($this->due_date)) {
            return 'overdue';
        } else {
            return 'pending';
        }
    }
}
