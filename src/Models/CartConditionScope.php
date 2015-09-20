<?php

namespace Ozanmuyes\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class CartConditionScope extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "cart_condition_scopes";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
    ];

    public function cart_conditions()
    {
        return $this->belongsToMany("Ozanmuyes\Cart\Models\CartConditions");
    }
}
