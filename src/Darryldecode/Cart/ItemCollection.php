<?php namespace Darryldecode\Cart;

/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/17/2015
 * Time: 11:03 AM
 */

use Darryldecode\Cart\Helpers\Helpers;
use Illuminate\Support\Collection;

class ItemCollection extends Collection
{

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
    public function __construct($items, $config = [])
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
        if ($this->has($name) || $name == 'model') {
            return !is_null($this->get($name)) ? $this->get($name) : $this->getAssociatedModel();
        }
        return null;
    }

    /**
     * return the associated model of an item
     *
     * @return bool
     */
    protected function getAssociatedModel()
    {
        if (!$this->has('associatedModel')) {
            return null;
        }

        $associatedModel = $this->get('associatedModel');

        return with(new $associatedModel())->find($this->get('id'));
    }

    /**
     * check if item has conditions
     *
     * @return bool
     */
    public function hasConditions()
    {
        if (!isset($this['conditions'])) return false;
        if (is_array($this['conditions'])) {
            return count($this['conditions']) > 0;
        }
        $conditionInstance = "Darryldecode\\Cart\\CartCondition";
        if ($this['conditions'] instanceof $conditionInstance) return true;

        return false;
    }

    /**
     * check if item has conditions
     *
     * @return mixed|null
     */
    public function getConditions()
    {
        if (!$this->hasConditions()) return [];
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

        if ($this->hasConditions()) {
            if (is_array($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    ($processed > 0) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                    $newPrice = $condition->applyCondition($toBeCalculated);
                    $processed++;
                }
            } else {
                $newPrice = $this['conditions']->applyCondition($originalPrice);
            }

            return Helpers::formatValue($newPrice, $formatted, $this->config);
        }
        return Helpers::formatValue($originalPrice, $formatted, $this->config);
    }

    /**
     * get the sum of price in which conditions are already applied
     * If "unitary" condition, price of unitary added for product without multiplying ProductQuantity
     * @param bool $formatted
     * @return mixed|null
     */
    public function getPriceSumWithConditions($formatted = true)
    {
        $originalPrice = $this->price;
        $newPrice = 0.00;
        $price_summ = 0.00;
        $unitary_summ = 0.00;
        $processed = 0;

        if ($this->hasConditions()) {
            if (is_array($this->conditions)) {
                foreach ($this->conditions as $condition) {
                    ($processed > 0) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;
                    if($condition->getType() == 'unitary'){
                        $unitary_summ += $condition->applyCondition(0);
                        $newPrice = ($processed > 0) ? $newPrice : $originalPrice;
                    }
                    else{
                        $newPrice = $condition->applyCondition($toBeCalculated);
                    }
                    $processed++;
                }
                $price_summ = Helpers::formatValue( ($newPrice * $this->quantity) + $unitary_summ, $formatted, $this->config);
            } else {
                if($this['conditions']->getType() == 'unitary'){
                    $unitary_summ = $this['conditions']->applyCondition(0);
                    $newPrice = ($originalPrice * $this->quantity) + $unitary_summ;
                }
                else{
                    $newPrice = $this['conditions']->applyCondition($originalPrice);
                    $newPrice = $newPrice * $this->quantity;
                }
                $price_summ = Helpers::formatValue($newPrice, $formatted, $this->config);
            }
        }
        else{
            $price_summ = Helpers::formatValue($originalPrice * $this->quantity, $formatted, $this->config);
        }
        return Helpers::formatValue($price_summ, $formatted, $this->config);
    }
}
