<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ReduceProductStock
{
    public function __construct()
    {
        //
    }

    public function handle(OrderCreated $event): void
    {
        foreach ($event->order->items as $item) {
            $item->product?->decrement('stock', $item->quantity);
        }
    }
}
