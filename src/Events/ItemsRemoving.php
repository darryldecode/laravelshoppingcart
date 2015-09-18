<?php

namespace Ozanmuyes\Cart\Events;

use App\Events\Event;

use Ozanmuyes\Cart\Cart;

class ItemsRemoving extends Event
{
    public $cart;
    public $item;

    /**
     * Create a new event instance.
     *
     * @param  Cart  $cart
     * @param  array  $item
     *
     * @return void
     */
    public function __construct(Cart $cart, $item)
    {
        $this->cart = $cart;
        $this->item = $item;
    }
}
