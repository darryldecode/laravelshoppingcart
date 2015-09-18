<?php

namespace Ozanmuyes\Cart\Events;

use App\Events\Event;

use Ozanmuyes\Cart\Cart;

class ItemsRemoved extends Event
{
    public $cart;
    public $item_id;

    /**
     * Create a new event instance.
     *
     * @param  Cart  $cart
     * @param  integer  $item_id
     *
     * @return void
     */
    public function __construct(Cart $cart, $item_id)
    {
        $this->cart = $cart;
        $this->item_id = $item_id;
    }
}
