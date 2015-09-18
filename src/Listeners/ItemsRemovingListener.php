<?php

namespace App\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use Ozanmuyes\Cart\Events\ItemsRemoving;

class ItemsRemovingListener
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
     * @param  ItemsRemoving  $event
     *
     * @return void
     */
    public function handle(ItemsRemoving $event)
    {
        //
    }
}
