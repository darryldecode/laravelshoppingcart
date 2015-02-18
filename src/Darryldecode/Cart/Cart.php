<?php namespace Darryldecode\Cart;

use Darryldecode\Cart\Exceptions\CartInstanceException;
use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Darryldecode\Cart\Exceptions\InvalidItemException;
use Darryldecode\Cart\Exceptions\InvalidItemFieldException;
use Darryldecode\Cart\Helpers\Helpers;
use Darryldecode\Cart\Validators\CartItemValidator;

class Cart {

    /**
     * the cart conditions
     *
     * @var CartConditionCollection
     */
    protected $conditions;

    /**
     * the item storage
     *
     * @var
     */
    protected $session;

    /**
     * the event dispatcher
     *
     * @var
     */
    protected $events;

    /**
     * the cart session key
     *
     * @var
     */
    protected $instanceName;

    /**
     * the session key use as storage
     *
     * @var
     */
    protected $sessionKey;

    /**
     * our object constructor
     *
     * @param $session
     * @param $events
     * @param $instanceName
     * @param $session_key
     */
    public function __construct($session, $events, $instanceName, $session_key)
    {
        $this->events = $events;
        $this->session = $session;
        $this->instanceName = $instanceName;
        $this->sessionKey = $session_key;
        $this->conditions = new CartConditionCollection();
        $this->events->fire($this->getInstanceName().'.created', array($this));
    }

    /**
     * get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }

    /**
     * get an item on a cart by item ID
     *
     * @param $itemId
     * @return mixed
     */
    public function get($itemId)
    {
        $contents = $this->getContent();

        return $contents->get($itemId);
    }

    /**
     * add item to the cart, it can be an array or multi dimensional array
     *
     * @param string|array $id
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @param array $attributes
     * @param CartCondition|array $conditions
     * @return $this
     * @throws InvalidItemException
     */
    public function add($id, $name = null, $price = null, $quantity = null, $attributes = array(), $conditions = array())
    {
        // if the first argument is an array,
        // we will need to call add again
        if( is_array($id) )
        {
            // the first argument is an array, now we will need to check if it is a multi dimensional
            // array, if so, we will iterate through each item and call add again
            if( Helpers::isMultiArray($id) )
            {
                foreach($id as $item)
                {
                    $this->add(
                        $item['id'],
                        $item['name'],
                        $item['price'],
                        $item['quantity'],
                        Helpers::issetAndHasValueOrAssignDefault($item['attributes'], array()),
                        Helpers::issetAndHasValueOrAssignDefault($item['conditions'], array())
                    );
                }
            }
            else
            {
                $this->add(
                    $id['id'],
                    $id['name'],
                    $id['price'],
                    $id['quantity'],
                    Helpers::issetAndHasValueOrAssignDefault($id['attributes'], array()),
                    Helpers::issetAndHasValueOrAssignDefault($id['conditions'], array())
                );
            }

            return $this;
        }

        // validate data
        $item = $this->validate(array(
            'id' => $id,
            'name' => $name,
            'price' => Helpers::normalizePrice($price),
            'quantity' => $quantity,
            'attributes' => new ItemAttributeCollection($attributes),
            'conditions' => $conditions,
        ));

        // get the cart
        $cart = $this->getContent();

        // if the item is already in the cart we will just update it
        if( $cart->has($id) )
        {
            $this->events->fire($this->getInstanceName().'.updating', array($item, $this));

            $this->update($id, $item);

            $this->events->fire($this->getInstanceName().'.updated', array($item, $this));
        }
        else
        {
            $this->events->fire($this->getInstanceName().'.adding', array($item, $this));

            $this->addRow($id, $item);

            $this->events->fire($this->getInstanceName().'.added', array($item, $this));
        }

        return $this;
    }

    /**
     * update a cart
     *
     * @param $id
     * @param $data
     *
     * the $data will be an associative array, you don't need to pass all the data, only the key value
     * of the item you want to update on it
     */
    public function update($id, $data)
    {
        $cart = $this->getContent();

        $item = $cart->pull($id);

        foreach($data as $key => $value)
        {
            $item[$key] = $value;
        }

        $cart->put($id, $item);

        $this->save($cart);
    }

    /**
     * removes an item on cart by item ID
     *
     * @param $id
     */
    public function remove($id)
    {
        $cart = $this->getContent();

        $this->events->fire($this->getInstanceName().'.removing', array($id, $this));

        $cart->forget($id);

        $this->save($cart);

        $this->events->fire($this->getInstanceName().'.removed', array($id, $this));
    }

    /**
     * clear cart
     */
    public function clear()
    {
        $this->events->fire($this->getInstanceName().'.clearing', array($this));

        $this->session->put(
            $this->sessionKey,
            array()
        );

        $this->events->fire($this->getInstanceName().'.cleared', array($this));
    }

    /**
     * add a condition on the cart
     *
     * @param CartCondition|array $condition
     * @return $this
     * @throws InvalidConditionException
     */
    public function condition($condition)
    {
        if( is_array($condition) )
        {
            foreach($condition as $c)
            {
                $this->condition($c);
            }

            return $this;
        }

        if( ! $condition instanceof CartCondition ) throw new InvalidConditionException('Argument 1 must be an instance of \'Darryldecode\Cart\CartCondition\'');

        $this->conditions->push($condition);

        return $this;
    }

    /**
     * get conditions applied on the cart
     *
     * @return CartConditionCollection
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * get cart sub total
     *
     * @return float
     */
    public function getSubTotal()
    {
        $cart = $this->getContent();

        $sum = $cart->sum(function($item)
        {
            $originalPrice = $item->price;

            $newPrice = 0.00;

            $processed = 0;

            if( $this->itemHasConditions($item) )
            {
                if( is_array($item->conditions) )
                {
                    foreach($item->conditions as $condition)
                    {
                        if( $condition->getTarget() === 'subtotal' )
                        {
                            ( $processed > 0 ) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;

                            $newPrice = $condition->applyCondition($toBeCalculated);

                            $processed++;
                        }
                    }
                }
                else
                {
                    if( $item['conditions']->getTarget() === 'subtotal' )
                    {
                        $newPrice = $item['conditions']->applyCondition($originalPrice);
                    }
                }

                return $newPrice * $item->quantity;
            }
            else
            {
                return $originalPrice * $item->quantity;
            }
        });

        return floatval($sum);
    }

    /**
     * the new total in which conditions are already applied
     *
     * @return float
     */
    public function getTotal()
    {
        $subTotal = $this->getSubTotal();

        $newTotal = 0.00;

        $process = 0;

        $conditions = $this->getConditions();

        $conditions->each(function($cond) use ($subTotal, &$newTotal, &$process)
        {
            if( $cond->getTarget() === 'total' )
            {
                ( $process > 0 ) ? $toBeCalculated = $newTotal : $toBeCalculated = $subTotal;

                $newTotal = $cond->applyCondition($toBeCalculated);

                $process++;
            }
        });

        return $newTotal;
    }

    /**
     * get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return (new CartCollection($this->session->get($this->sessionKey)));
    }

    /**
     * check if cart is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $cart = new CartCollection($this->session->get($this->sessionKey));

        return $cart->isEmpty();
    }

    /**
     * validate Item data
     *
     * @param $item
     * @return array $item;
     * @throws InvalidItemException
     */
    protected function validate($item)
    {
        $rules = array(
            'id' => 'required',
            'price' => 'required|numeric',
            'quantity' => 'required|numeric|min:1',
            'name' => 'required',
        );

        $validator = CartItemValidator::make($item, $rules);

        if( $validator->fails() )
        {
            throw new InvalidItemException($validator->messages()->first());
        }

        return $item;
    }

    /**
     * add row to cart collection
     *
     * @param $id
     * @param $item
     */
    protected function addRow($id, $item)
    {
        $cart = $this->getContent();

        $cart->put($id, new ItemCollection($item));

        $this->save($cart);
    }

    /**
     * save the cart
     *
     * @param $cart CartCollection
     */
    protected function save($cart)
    {
        $this->session->put($this->sessionKey, $cart);
    }

    /**
     * check if an item has condition
     *
     * @param $item
     * @return bool
     */
    protected function itemHasConditions($item)
    {
        return count($item['conditions']) > 0;
    }
}