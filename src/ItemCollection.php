<?php

namespace Ozanmuyes\Cart;

use Illuminate\Support\Collection;

class ItemCollection extends Collection
{
    /**
     * get the sum of price
     *
     * @return mixed|null
     */
    public function getPriceSum()
    {
        return ($this->price * $this->quantity);
    }

    public function __get($name)
    {
        return ($this->has($name))
                ? $this->get($name)
                : null;
    }
}
