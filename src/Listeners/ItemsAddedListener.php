<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Ozanmuyes\Cart\Events\ItemsAdded;

class ItemsAddedListener
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
     * @param  ItemsAdded  $event
     *
     * @return void
     */
    public function handle(ItemsAdded $event)
    {
        //
    }
}
