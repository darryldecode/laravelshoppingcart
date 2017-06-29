<?php namespace Darryldecode\Cart;

use Illuminate\Support\Collection;

class CartCollection extends Collection {

    public function associate($model, $field = 'id'){

        $associated = new $model;

        $this->transform(function($item, $key) use ($associated, $field) {
            $item['association'] = $associated->where($field,$key)->first();
            return $item;
        });

        return $this;
    }

}