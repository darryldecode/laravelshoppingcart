<?php

namespace Ozanmuyes\Cart\Events;

use App\Events\Event;

use Ozanmuyes\Cart\Cart;

class CartCreated extends Event
{
    public $cart;

    /**
     * Create a new event instance.
     *
     * @param  Cart  $cart
     *
     * @return void
     */
    public function __construct(Cart $cart)
    {
        $this->cart = $cart;
    }
}
