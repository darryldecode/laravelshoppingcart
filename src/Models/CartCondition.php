<?php

namespace Ozanmuyes\Cart\Models;

use Illuminate\Database\Eloquent\Model;

class CartCondition extends Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = "cart_conditions";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        "name",
    ];

    public function cart_condition_scopes()
    {
        return $this->belongsToMany("Ozanmuyes\Cart\Models\CartConditionScopes");
    }
}
