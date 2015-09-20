<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartConditionCartConditionScopeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_condition_cart_condition_scope', function (Blueprint $table) {
            $table->increments('id')->unsigned();
            $table->integer("cart_condition_id")->unsigned();
            $table->integer("cart_condition_scope_id")->unsigned();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('cart_condition_cart_condition_scope');
    }
}
