<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Ozanmuyes\Cart\Events\CartCreated;

class CartCreatedListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  CartCreated  $event
     *
     * @return void
     */
    public function handle(CartCreated $event)
    {
        //
    }
}
