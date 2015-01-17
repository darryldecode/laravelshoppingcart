<?php namespace Darryldecode\Cart;

/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/17/2015
 * Time: 11:03 AM
 */

use Illuminate\Support\Collection;

class ItemCollection extends Collection {

    public function __get($name)
    {
        if( $this->has($name) ) return $this->get($name);
        return null;
    }
}