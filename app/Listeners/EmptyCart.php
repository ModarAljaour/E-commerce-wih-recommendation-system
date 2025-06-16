<?php

namespace App\Listeners;

use App\Repository\CartRepository;
use App\Events\EmptyCartEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class EmptyCart
{
    public $cart;
    public function __construct(CartRepository $cart)
    {
        $this->cart = $cart;
    }

    public function handle(EmptyCartEvent $event): void // handle event
    {
        $this->cart->clear();
    }
}
