<?php namespace Darryldecode\Cart;

use Illuminate\Support\Collection;

class CartCollection extends Collection {

    /**
     * check if the cart contains an item with the given ID
     *
     * @param $itemId
     * @return bool
     */
    public function hasItem($itemId)
    {
        foreach($this->items as $item)
        {
            if( $item['id'] === $itemId ) return true;
        }
        return false;
    }

    /**
     * pull an item on cart content using item id and then remove it totally
     *
     * @param $itemId
     * @return null
     */
    public function pullItem($itemId)
    {
        $itemToBePulled = null;

        foreach($this->items as $k => $v)
        {
            if( $v['id'] === $itemId )
            {
                $this->forget($k);
                $itemToBePulled = $v;
            }
        }

        return $itemToBePulled;
    }

    /**
     * remove an item on the cart by item Id
     *
     * @param $itemId
     */
    public function removeItem($itemId)
    {
        foreach($this->items as $k => $v)
        {
            if( $v['id'] === $itemId )
            {
                $this->forget($k);
            }
        }
    }
}