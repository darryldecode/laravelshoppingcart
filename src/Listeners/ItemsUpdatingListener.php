<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Ozanmuyes\Cart\Events\ItemsUpdating;

class ItemsUpdatingListener
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
     * @param  ItemsUpdating  $event
     *
     * @return void
     */
    public function handle(ItemsUpdating $event)
    {
        //
    }
}
