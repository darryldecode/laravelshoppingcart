<?php

namespace Ozanmuyes\Cart;

use Illuminate\Support\Collection;

class ItemAttributeCollection extends Collection
{
    public function __get($name)
    {
        return ($this->has($name))
                ? $this->get($name)
                : null;
    }
}
