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
    public function getPriceSum($formatted = true)
    {
        return Helpers::formatValue($this->price * $this->quantity, $formatted, $this->config);

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
        return $this['conditions'];
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
                    ( $processed > 0 ) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                    $newPrice = $condition->applyCondition($toBeCalculated);
                    $processed++;
                }
            }
            else
            {
                $newPrice = $this['conditions']->applyCondition($originalPrice);
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

    /**
     * get the sum of condition type
     * @param string $type
     * @param bool $multiple
     * @param bool $formatted
     * @return mixed|null
     */
    public function getPriceOfCondition($type, $multiple = true, $formatted = true)
    {
        $zero = 0.00;
        $quantity = ($multiple == true) ? $this->quantity : 1;

        if ($this->hasConditions()) {
            if (is_array($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    if ($condition->getType() == $type) {
                        $conditionValue = $condition->getValue() * $quantity;
                    }
                }
            } else {
                $conditionValue = $this['conditions']->getValue() * $quantity;
            }

            return Helpers::formatValue($conditionValue, $formatted, $this->config);
        }

        return Helpers::formatValue($zero, $formatted, $this->config);
    }
}
