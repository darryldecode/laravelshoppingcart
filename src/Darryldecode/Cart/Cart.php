<?php namespace Darryldecode\Cart;

use Darryldecode\Cart\Exceptions\InvalidConditionException;
use Darryldecode\Cart\Exceptions\InvalidItemException;
use Darryldecode\Cart\Helpers\Helpers;
use Darryldecode\Cart\Validators\CartItemValidator;

/**
 * Class Cart
 * @package Darryldecode\Cart
 */
class Cart {

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
     * the session key use to persist cart items
     *
     * @var
     */
    protected $sessionKeyCartItems;

    /**
     * the session key use to persist cart conditions
     *
     * @var
     */
    protected $sessionKeyCartConditions;

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
        $this->sessionKeyCartItems = $session_key.'_cart_items';
        $this->sessionKeyCartConditions = $session_key.'_cart_conditions';
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
        return $this->getContent()->get($itemId);
    }

    /**
     * check if an item exists by item ID
     *
     * @param $itemId
     * @return bool
     */
    public function has($itemId)
    {
        return $this->getContent()->has($itemId);
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

            $this->update($id, $item);
        }
        else
        {

            $this->addRow($id, $item);

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
        $this->events->fire($this->getInstanceName().'.updating', array($data, $this));

        $cart = $this->getContent();

        $item = $cart->pull($id);

        foreach($data as $key => $value)
        {
            // if the key is currently "quantity" we will need to check if an arithmetic
            // symbol is present so we can decide if the update of quantity is being added
            // or being reduced.
            if( $key == 'quantity' )
            {
                // we will check if quantity value provided is array,
                // if it is, we will need to check if a key "relative" is set
                // and we will evaluate its value if true or false,
                // this tells us how to treat the quantity value if it should be updated
                // relatively to its current quantity value or just totally replace the value
                if( is_array($value) )
                {
                    if( isset($value['relative']) )
                    {
                        if( (bool) $value['relative'] )
                        {
                            $item = $this->updateQuantityRelative($item, $key, $value['value']);
                        }
                        else
                        {
                            $item = $this->updateQuantityNotRelative($item, $key, $value['value']);
                        }
                    }
                }
                else
                {
                    $item = $this->updateQuantityRelative($item, $key, $value);
                }
            }
            elseif( $key == 'attributes' )
            {
                $item[$key] = new ItemAttributeCollection($value);
            }
            else
            {
                $item[$key] = $value;
            }
        }

        $cart->put($id, $item);

        $this->save($cart);

        $this->events->fire($this->getInstanceName().'.updated', array($item, $this));
    }

    /**
     * add condition on an existing item on the cart
     *
     * @param int|string $productId
     * @param CartCondition $itemCondition
     * @return $this
     */
    public function addItemCondition($productId, $itemCondition)
    {
        if( $product = $this->get($productId) )
        {
            $conditionInstance = "\\Darryldecode\\Cart\\CartCondition";

            if( $itemCondition instanceof $conditionInstance )
            {
                // we need to copy first to a temporary variable to hold the conditions
                // to avoid hitting this error "Indirect modification of overloaded element of Darryldecode\Cart\ItemCollection has no effect"
                // this is due to laravel Collection instance that implements Array Access
                // // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
                $itemConditionTempHolder = $product['conditions'];

                if( is_array($itemConditionTempHolder) )
                {
                    array_push($itemConditionTempHolder, $itemCondition);
                }
                else
                {
                    $itemConditionTempHolder = $itemCondition;
                }

                $this->update($productId, array(
                    'conditions' => $itemConditionTempHolder // the newly updated conditions
                ));
            }
        }

        return $this;
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
            $this->sessionKeyCartItems,
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

        $conditions = $this->getConditions();

        $conditions->put($condition->getName(), $condition);

        $this->saveConditions($conditions);

        return $this;
    }

    /**
     * get conditions applied on the cart
     *
     * @return CartConditionCollection
     */
    public function getConditions()
    {
        return new CartConditionCollection($this->session->get($this->sessionKeyCartConditions));
    }

    /**
     * get condition applied on the cart by its name
     *
     * @param $conditionName
     * @return CartCondition
     */
    public function getCondition($conditionName)
    {
        return $this->getConditions()->get($conditionName);
    }

    /**
    * Get all the condition filtered by Type
    * Please Note that this will only return condition added on cart bases, not those conditions added
    * specifically on an per item bases
    *
    * @param $type
    * @return CartConditionCollection
    */
    public function getConditionsByType($type)
    {
        return $this->getConditions()->filter(function(CartCondition $condition) use ($type)
        {
            return $condition->getType() == $type;
        });
    }


    /**
     * Remove all the condition with the $type specified
     * Please Note that this will only remove condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @param $type
     * @return $this
     */
    public function removeConditionsByType($type)
    {
        $this->getConditionsByType($type)->each(function($condition)
        {
            $this->removeCartCondition($condition->getName());
        });
    }


    /**
     * removes a condition on a cart by condition name,
     * this can only remove conditions that are added on cart bases not conditions that are added on an item/product.
     * If you wish to remove a condition that has been added for a specific item/product, you may
     * use the removeItemCondition(itemId, conditionName) method instead.
     *
     * @param $conditionName
     * @return void
     */
    public function removeCartCondition($conditionName)
    {
        $conditions = $this->getConditions();

        $conditions->pull($conditionName);

        $this->saveConditions($conditions);
    }

    /**
     * remove a condition that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @param $conditionName
     * @return bool
     */
    public function removeItemCondition($itemId, $conditionName)
    {
        if( ! $item = $this->getContent()->get($itemId) )
        {
            return false;
        }

        if( $this->itemHasConditions($item) )
        {
            // NOTE:
            // we do it this way, we get first conditions and store
            // it in a temp variable $originalConditions, then we will modify the array there
            // and after modification we will store it again on $item['conditions']
            // This is because of ArrayAccess implementation
            // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect

            $tempConditionsHolder = $item['conditions'];

            // if the item's conditions is in array format
            // we will iterate through all of it and check if the name matches
            // to the given name the user wants to remove, if so, remove it
            if( is_array($tempConditionsHolder) )
            {
                foreach($tempConditionsHolder as $k => $condition)
                {
                    if( $condition->getName() == $conditionName )
                    {
                        unset($tempConditionsHolder[$k]);
                    }
                }

                $item['conditions'] = $tempConditionsHolder;
            }

            // if the item condition is not an array, we will check if it is
            // an instance of a Condition, if so, we will check if the name matches
            // on the given condition name the user wants to remove, if so,
            // lets just make $item['conditions'] an empty array as there's just 1 condition on it anyway
            else
            {
                $conditionInstance = "Darryldecode\\Cart\\CartCondition";

                if ($item['conditions'] instanceof $conditionInstance)
                {
                    if ($tempConditionsHolder->getName() == $conditionName)
                    {
                        $item['conditions'] = array();
                    }
                }
            }
        }

        $this->update($itemId, array(
            'conditions' => $item['conditions']
        ));

        return true;
    }

    /**
     * remove all conditions that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @return bool
     */
    public function clearItemConditions($itemId)
    {
        if( ! $item = $this->getContent()->get($itemId) )
        {
            return false;
        }

        $this->update($itemId, array(
            'conditions' => array()
        ));

        return true;
    }

    /**
     * clears all conditions on a cart,
     * this does not remove conditions that has been added specifically to an item/product.
     * If you wish to remove a specific condition to a product, you may use the method: removeItemCondition($itemId, $conditionName)
     *
     * @return void
     */
    public function clearCartConditions()
    {
        $this->session->put(
            $this->sessionKeyCartConditions,
            array()
        );
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
            return $item->getPriceSumWithConditions();
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

        // if no conditions were added, just return the sub total
        if( ! $conditions->count() ) return $subTotal;

        $conditions->each(function($cond) use ($subTotal, &$newTotal, &$process)
        {
            if( $cond->getTarget() === 'subtotal' )
            {
                ( $process > 0 ) ? $toBeCalculated = $newTotal : $toBeCalculated = $subTotal;

                $newTotal = $cond->applyCondition($toBeCalculated);

                $process++;
            }
        });

        return $newTotal;
    }

    /**
     * get total quantity of items in the cart
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        $items = $this->getContent();

        if( $items->isEmpty() ) return 0;

        $count = $items->sum(function($item)
        {
            return $item['quantity'];
        });

        return $count;
    }

    /**
     * get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return (new CartCollection($this->session->get($this->sessionKeyCartItems)));
    }

    /**
     * check if cart is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        $cart = new CartCollection($this->session->get($this->sessionKeyCartItems));

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
        $this->events->fire($this->getInstanceName().'.adding', array($item, $this));

        $cart = $this->getContent();

        $cart->put($id, new ItemCollection($item));

        $this->save($cart);

        $this->events->fire($this->getInstanceName().'.added', array($item, $this));
    }

    /**
     * save the cart
     *
     * @param $cart CartCollection
     */
    protected function save($cart)
    {
        $this->session->put($this->sessionKeyCartItems, $cart);
    }

    /**
     * save the cart conditions
     *
     * @param $conditions
     */
    protected function saveConditions($conditions)
    {
        $this->session->put($this->sessionKeyCartConditions, $conditions);
    }

    /**
     * check if an item has condition
     *
     * @param $item
     * @return bool
     */
    protected function itemHasConditions($item)
    {
        if( ! isset($item['conditions']) ) return false;

        if( is_array($item['conditions']) )
        {
            return count($item['conditions']) > 0;
        }

        $conditionInstance = "Darryldecode\\Cart\\CartCondition";

        if( $item['conditions'] instanceof $conditionInstance ) return true;

        return false;
    }

    /**
     * update a cart item quantity relative to its current quantity
     *
     * @param $item
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateQuantityRelative($item, $key, $value)
    {
        if( preg_match('/\-/', $value) == 1 )
        {
            $value = (int) str_replace('-','',$value);

            // we will not allowed to reduced quantity to 0, so if the given value
            // would result to item quantity of 0, we will not do it.
            if( ($item[$key] - $value) > 0 )
            {
                $item[$key] -= $value;
            }
        }
        elseif( preg_match('/\+/', $value) == 1 )
        {
            $item[$key] += (int) str_replace('+','',$value);
        }
        else
        {
            $item[$key] += (int) $value;
        }

        return $item;
    }

    /**
     * update cart item quantity not relative to its current quantity value
     *
     * @param $item
     * @param $key
     * @param $value
     * @return mixed
     */
    protected function updateQuantityNotRelative($item, $key, $value)
    {
        $item[$key] = (int) $value;

        return $item;
    }
}
