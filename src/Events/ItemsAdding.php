<?php

namespace Ozanmuyes\Cart\Events;

use App\Events\Event;

use Ozanmuyes\Cart\Cart;

class ItemsAdding extends Event
{
    public $cart;
    public $items;

    /**
     * Create a new event instance.
     *
     * @param  Cart  $cart
     * @param  array  $items
     *
     * @return void
     */
    public function __construct(Cart $cart, $items)
    {
        $this->cart = $cart;
        $this->items = $items;
    }
}
