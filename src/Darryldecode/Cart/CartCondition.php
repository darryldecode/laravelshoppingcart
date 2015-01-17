<?php namespace Darryldecode\Cart;
use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Darryldecode\Cart\Helpers\Helpers;
use Darryldecode\Cart\Validators\CartConditionValidator;

/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/15/2015
 * Time: 9:02 PM
 */

class CartCondition {

    /**
     * @var array
     */
    private $args;

    /**
     * @param array $args (name, type, target, value)
     */
    public function __construct(array $args)
    {
        $this->args = $args;

        if( Helpers::isMultiArray($args) )
        {
            foreach($args as $arg)
            {
                $this->validate($arg);
            }
        }
        else
        {
            $this->validate($this->args);
        }
    }

    /**
     * the target of where the condition is applied
     *
     * @param null $conditionName | if no conditionName provided, it will only pull the first item on conditions array
     * @return mixed
     */
    public function getTarget($conditionName = null)
    {
        if( Helpers::isMultiArray($this->args) )
        {
            if( $conditionName )
            {
                foreach($this->args as $condData)
                {
                    if( $condData['name'] == $conditionName )
                    {
                        return $condData['target'];
                    }
                }
            }
            else
            {
                return $this->args[0]['target'];
            }
        } else {
            return $this->args['target'];
        }
    }

    /**
     * the name of the condition
     *
     * @param null $key
     * @return mixed
     */
    public function getName($key = null)
    {
        if( Helpers::isMultiArray($this->args) )
        {
            if( $key )
            {
                foreach($this->args as $k => $v)
                {
                    if( $k == $key )
                    {
                        return $v['name'];
                    }
                }
            }
            else
            {
                return $this->args[0]['name'];
            }
        } else {
            return $this->args['name'];
        }
    }

    /**
     * the type of the condition, if no condition name provided it will just pull
     * the first condition argument
     *
     * @param null $conditionName
     * @return mixed
     */
    public function getType($conditionName = null)
    {
        if( Helpers::isMultiArray($this->args) )
        {
            if( $conditionName )
            {
                foreach($this->args as $condData)
                {
                    if( $condData['name'] == $conditionName )
                    {
                        return $condData['type'];
                    }
                }
            }
            else
            {
                return $this->args[0]['type'];
            }
        } else {
            return $this->args['type'];
        }
    }

    /**
     * the value of this the condition
     *
     * @param null $conditionName
     * @return mixed
     */
    public function getValue($conditionName = null)
    {
        if( Helpers::isMultiArray($this->args) )
        {
            if( $conditionName )
            {
                foreach($this->args as $condData)
                {
                    if( $condData['name'] == $conditionName )
                    {
                        return $condData['value'];
                    }
                }
            }
            else
            {
                return $this->args[0]['value'];
            }
        } else {
            return $this->args['value'];
        }
    }

    /**
     * apply condition to total or subtotal
     *
     * @param $totalOrSubTotalOrPrice
     * @return float
     */
    public function applyCondition($totalOrSubTotalOrPrice)
    {
        if( Helpers::isMultiArray($this->args) )
        {
            $originalPrice = $totalOrSubTotalOrPrice;

            $newPrice = 0.00;

            $processed = 0;

            foreach($this->args as $arg)
            {
                ( $processed > 0 ) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;

                $newPrice = $this->apply($toBeCalculated, $arg['value']);

                $processed++;
            }

            return $newPrice;
        }
        else
        {
            return $this->apply($totalOrSubTotalOrPrice, $this->getValue());
        }
    }

    /**
     * apply condition
     *
     * @param $totalOrSubTotalOrPrice
     * @param $conditionValue
     * @return float
     */
    protected function apply($totalOrSubTotalOrPrice, $conditionValue)
    {
        // if value has a percentage sign on it, we will get first
        // its percentage then we will evaluate again if the value
        // has a minus or plus sign so we can decide what to do with the
        // percentage, whether to add or subtract it to the total/subtotal/price
        // if we can't find any plus/minus sign, we will assume it as plus sign
        if( $this->valueIsPercentage($conditionValue) )
        {
            if( $this->valueIsToBeSubtracted($conditionValue) )
            {
                $value = Helpers::normalizePrice( $this->cleanValue($conditionValue) );

                $valueToBeSubtracted = $totalOrSubTotalOrPrice * ($value / 100);

                return floatval($totalOrSubTotalOrPrice - $valueToBeSubtracted);
            }
            else if ( $this->valueIsToBeAdded($conditionValue) )
            {
                $value = Helpers::normalizePrice( $this->cleanValue($conditionValue) );

                $valueToBeSubtracted = $totalOrSubTotalOrPrice * ($value / 100);

                return floatval($totalOrSubTotalOrPrice - $valueToBeSubtracted);
            }
            else
            {
                $value = Helpers::normalizePrice($conditionValue);

                $valueToBeSubtracted = $totalOrSubTotalOrPrice * ($value / 100);

                return floatval($totalOrSubTotalOrPrice + $valueToBeSubtracted);
            }
        }

        // if the value has no percent sign on it, the operation will not be a percentage
        // next is we will check if it has a minus/plus sign so then we can just deduct it to total/subtotal/price
        else
        {
            if( $this->valueIsToBeSubtracted($conditionValue) )
            {
                $value = Helpers::normalizePrice( $this->cleanValue($conditionValue) );

                return floatval($totalOrSubTotalOrPrice - $value);
            }
            else if ( $this->valueIsToBeAdded($conditionValue) )
            {
                $value = Helpers::normalizePrice( $this->cleanValue($conditionValue) );

                return floatval($totalOrSubTotalOrPrice + $value);
            }
            else
            {
                $value = Helpers::normalizePrice($conditionValue);

                return floatval($totalOrSubTotalOrPrice + $value);
            }
        }
    }

    /**
     * check if value is a percentage
     *
     * @param $value
     * @return bool
     */
    protected function valueIsPercentage($value)
    {
        return (preg_match('/%/', $value) == 1);
    }

    /**
     * check if value is a subtract
     *
     * @param $value
     * @return bool
     */
    protected function valueIsToBeSubtracted($value)
    {
        return (preg_match('/\-/', $value) == 1);
    }

    /**
     * check if value is to be added
     *
     * @param $value
     * @return bool
     */
    protected function valueIsToBeAdded($value)
    {
        return (preg_match('/\+/', $value) == 1);
    }

    /**
     * removes some arithmetic signs (%,+,-) only
     *
     * @param $value
     * @return mixed
     */
    protected function cleanValue($value)
    {
        return str_replace(array('%','-','+'),'',$value);
    }

    /**
     * validates condition arguments
     *
     * @param $args
     * @throws InvalidConditionException
     */
    protected function validate($args)
    {
        $rules = array(
            'name' => 'required',
            'type' => 'required',
            'target' => 'required',
            'value' => 'required',
        );

        $validator = CartConditionValidator::make($args, $rules);

        if( $validator->fails() )
        {
            throw new InvalidConditionException($validator->messages()->first());
        }
    }
}