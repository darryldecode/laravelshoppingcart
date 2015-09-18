<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Ozanmuyes\Cart\Events\ItemsUpdated;

class ItemsUpdatedListener
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
     * @param  ItemsUpdated  $event
     *
     * @return void
     */
    public function handle(ItemsUpdated $event)
    {
        //
    }
}
