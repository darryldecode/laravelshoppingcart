<?php namespace Darryldecode\Cart;

/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/17/2015
 * Time: 11:03 AM
 */

use Darryldecode\Cart\Helpers\Helpers;
use Illuminate\Support\Collection;

class ItemCollection extends Collection {

    /**
     * Sets the config parameters.
     *
     * @var
     */
    protected $config;

    /**
     * ItemCollection constructor.
     * @param array|mixed $items
     * @param $config
     */
    public function __construct($items, $config)
    {
        parent::__construct($items);

        $this->config = $config;
    }

    /**
     * get the sum of price
     *
     * @return mixed|null
     */
    public function getPriceSum()
    {
        return Helpers::formatValue($this->price * $this->quantity, $this->config['format_numbers'], $this->config);

    }

    public function __get($name)
    {
        if( $this->has($name) ) return $this->get($name);
        return null;
    }

    /**
     * check if item has conditions
     *
     * @return bool
     */
    public function hasConditions()
    {
        if( ! isset($this['conditions']) ) return false;
        if( is_array($this['conditions']) )
        {
            return count($this['conditions']) > 0;
        }
        $conditionInstance = "Darryldecode\\Cart\\CartCondition";
        if( $this['conditions'] instanceof $conditionInstance ) return true;

        return false;
    }

    /**
     * check if item has conditions
     *
     * @return mixed|null
     */
    public function getConditions()
    {
        if(! $this->hasConditions() ) return [];
        return new ItemConditionCollection($this['conditions']);
    }

    /**
     * get condition applied on the item by its name
     *
     * @param $conditionName
     * @return CartCondition
     */
    public function getCondition($conditionName)
    {
        return $this->getConditions()->first(function($c) use ($conditionName) {return $c->getName() == $conditionName;});
    }

    /**
     * get the single price in which conditions are already applied
     * @param bool $formatted
     * @return mixed|null
     */
    public function getPriceWithConditions($formatted = true)
    {
        $originalPrice = $this->price;
        $newPrice = 0.00;
        $processed = 0;

        if( $this->hasConditions() )
        {
            if( is_array($this->conditions) )
            {
                foreach($this->conditions as $condition)
                {
                    if( $condition->getTarget() === 'item' )
                    {
                        ( $processed > 0 ) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                        $newPrice = $condition->applyCondition($toBeCalculated);
                        $processed++;
                    }
                }
            }
            else
            {
                if( $this['conditions']->getTarget() === 'item' )
                {
                    $newPrice = $this['conditions']->applyCondition($originalPrice);
                }
            }

            return Helpers::formatValue($newPrice, $formatted, $this->config);
        }
        return Helpers::formatValue($originalPrice, $formatted, $this->config);
    }

    /**
     * get the sum of price in which conditions are already applied
     * @param bool $formatted
     * @return mixed|null
     */
    public function getPriceSumWithConditions($formatted = true)
    {
        return Helpers::formatValue($this->getPriceWithConditions(false) * $this->quantity, $formatted, $this->config);
    }
}
