<?php

namespace Ozanmuyes\Cart;

use Event;

use Ozanmuyes\Cart\Exceptions\InvalidConditionException;
use Ozanmuyes\Cart\Exceptions\InvalidItemException;
use Ozanmuyes\Cart\Validators\CartItemValidator;

class Cart
{
    /**
     * The item storage
     *
     * @var
     */
    protected $session;

    /**
     * The cart session key
     *
     * @var
     */
    protected $instanceName;

    /**
     * The session key use to persist cart items
     *
     * @var
     */
    protected $sessionKeyCartItems;

    /**
     * The session key use to persist cart conditions
     *
     * @var
     */
    protected $sessionKeyCartConditions;

    /**
     * Our object constructor
     *
     * @param $session
     * @param $instanceName
     * @param $session_key
     */
    public function __construct($session, $instanceName, $session_key)
    {
        $this->session = $session;
        $this->instanceName = $instanceName;
        $this->sessionKeyCartItems = $session_key . "_cart_items";
        $this->sessionKeyCartConditions = $session_key . "_cart_conditions";

        Event::fire(new Events\CartCreated($this));
    }

    /**
     * Get instance name of the cart
     *
     * @return string
     */
    public function getInstanceName()
    {
        return $this->instanceName;
    }

    /**
     * Get an item on a cart by item ID
     *
     * @param $itemId
     *
     * @return mixed
     */
    public function get($itemId)
    {
        return $this->getContent()->get($itemId);
    }

    /**
     * Check if an item exists by item ID
     *
     * @param $itemId
     *
     * @return bool
     */
    public function has($itemId)
    {
        return $this->getContent()->has($itemId);
    }

    /**
     * Add item to the cart, it can be an array or multi dimensional array
     *
     * @param string|array $id
     * @param string $name
     * @param float $price
     * @param int $quantity
     * @param array $attributes
     * @param CartCondition|array $conditions
     *
     * @return $this
     *
     * @throws InvalidItemException
     */
    public function add(
        $id,
        $name = null,
        $price = null,
        $quantity = null,
        $attributes = [],
        $conditions = []
    ) {
        // TODO Sift id, name, price and quantity and add remainings to the attirbutes array.

        // If the first argument is an array, we will need to call add again
        if (is_array($id)) {
            // The first argument is an array, now we will need to check if it is a multi dimensional
            // array, if so, we will iterate through each item and call add again
            if (isMultiArray($id)) {
                foreach($id as $item) {
                    $this->add(
                        $item["id"],
                        $item["name"],
                        $item["price"],
                        $item["quantity"],
                        issetAndHasValueOrAssignDefault($item["attributes"], []),
                        issetAndHasValueOrAssignDefault($item["conditions"], [])
                    );
                }
            } else {
                $this->add(
                    $id["id"],
                    $id["name"],
                    $id["price"],
                    $id["quantity"],
                    issetAndHasValueOrAssignDefault($id["attributes"], []),
                    issetAndHasValueOrAssignDefault($id["conditions"], [])
                );
            }

            return $this;
        }

        // Validate data
        $item = $this->validate([
            "id" => $id,
            "name" => $name,
            "price" => normalizePrice($price),
            "quantity" => $quantity,
            "attributes" => new ItemAttributeCollection($attributes),
            "conditions" => $conditions,
        ]);

        // Get the cart
        $cart = $this->getContent();

        // If the item is already in the cart we will just update it
        if ($cart->has($id)) {
            Event::fire(new Events\ItemsUpdating($this, [$item]));

            // Check if $item changed
            $this->update($id, $item);

            Event::fire(new Events\ItemsUpdated($this, [$item]));
        } else {
            Event::fire(new Events\ItemsAdding($this, [$item]));

            // Check if $item changed
            $this->addRow($id, $item);

            Event::fire(new Events\ItemsAdded($this, [$item]));
        }

        return $this;
    }

    /**
     * Update a cart
     *
     * @param $id
     * @param $data
     *
     * The $data will be an associative array, you don't need to pass all the data, only the key value
     * of the item you want to update on it
     */
    public function update($id, $data)
    {
        $cart = $this->getContent();
        $item = $cart->pull($id);

        foreach($data as $key => $value) {
            // If the key is currently "quantity" we will need to check if an arithmetic
            // symbol is present so we can decide if the update of quantity is being added
            // or being reduced.
            if ($key == "quantity") {
                // We will check if quantity value provided is array,
                // if it is, we will need to check if a key "relative" is set
                // and we will evaluate its value if true or false,
                // this tells us how to treat the quantity value if it should be updated
                // relatively to its current quantity value or just totally replace the value
                if (is_array($value)) {
                    if (isset($value["relative"])) {
                        if ((bool) $value["relative"]) {
                            $item = $this->updateQuantityRelative($item, $key, $value["value"]);
                        } else {
                            $item = $this->updateQuantityNotRelative($item, $key, $value["value"]);
                        }
                    }
                } else {
                    $item = $this->updateQuantityRelative($item, $key, $value);
                }
            } elseif ($key == "attributes") {
                $item[$key] = new ItemAttributeCollection($value);
            } else {
                $item[$key] = $value;
            }
        }

        $cart->put($id, $item);
        $this->save($cart);
    }

    /**
     * Add condition on an existing item on the cart
     *
     * @param int|string $productId
     * @param CartCondition $itemCondition
     *
     * @return $this
     */
    public function addItemCondition($productId, $itemCondition)
    {
        if ($product = $this->get($productId)) {
            $conditionInstance = "\\Ozanmuyes\\Cart\\CartCondition";

            if ($itemCondition instanceof $conditionInstance) {
                // we need to copy first to a temporary variable to hold the conditions
                // to avoid hitting this error "Indirect modification of overloaded element of Ozanmuyes\Cart\ItemCollection has no effect"
                // this is due to laravel Collection instance that implements Array Access
                // // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect
                $itemConditionTempHolder = $product["conditions"];

                if (is_array($itemConditionTempHolder)) {
                    array_push($itemConditionTempHolder, $itemCondition);
                } else {
                    $itemConditionTempHolder = $itemCondition;
                }

                $this->update($productId, [
                    "conditions" => $itemConditionTempHolder // the newly updated conditions
                ]);
            }
        }

        return $this;
    }

    /**
     * Removes an item on cart by item ID
     *
     * @param $id
     */
    public function remove($id)
    {
        $cart = $this->getContent();
        $item = $cart->get($id);

        Event::fire(new Events\ItemsRemoving($this, [$item]));

        $cart->forget($id);
        $this->save($cart);

        Event::fire(new Events\ItemsRemoved($this, [$item]));
    }

    /**
     * Clear cart
     */
    public function clear()
    {
        Event::fire(new Events\CartClearing($this));

        $this->session->put($this->sessionKeyCartItems, []);

        Event::fire(new Events\CartCleared($this));
    }

    /**
     * Add a condition on the cart
     *
     * @param CartCondition|array $condition
     *
     * @return $this
     *
     * @throws InvalidConditionException
     */
    public function condition($condition)
    {
        if (is_array($condition)) {
            foreach($condition as $c) {
                $this->condition($c);
            }

            return $this;
        }

        if (!($condition instanceof CartCondition)) {
            throw new InvalidConditionException("Argument 1 must be an instance of 'Ozanmuyes\Cart\CartCondition'");
        }

        $conditions = $this->getConditions();
        $conditions->put($condition->getName(), $condition);
        $this->saveConditions($conditions);

        return $this;
    }

    /**
     * Get conditions applied on the cart
     *
     * @return CartConditionCollection
     */
    public function getConditions()
    {
        return new CartConditionCollection($this->session->get($this->sessionKeyCartConditions));
    }

    /**
     * Get condition applied on the cart by its name
     *
     * @param $conditionName
     *
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
    *
    * @return CartConditionCollection
    */
    public function getConditionsByType($type)
    {
        return $this->getConditions()->filter(function(CartCondition $condition) use ($type) {
            return ($condition->getType() == $type);
        });
    }


    /**
     * Remove all the condition with the $type specified
     * Please Note that this will only remove condition added on cart bases, not those conditions added
     * specifically on an per item bases
     *
     * @param $type
     *
     * @return $this
     */
    public function removeConditionsByType($type)
    {
        $this->getConditionsByType($type)->each(function($condition) {
            $this->removeCartCondition($condition->getName());
        });
    }


    /**
     * Removes a condition on a cart by condition name,
     * this can only remove conditions that are added on cart bases not conditions that are added on an item/product.
     * If you wish to remove a condition that has been added for a specific item/product, you may
     * Use the removeItemCondition(itemId, conditionName) method instead.
     *
     * @param $conditionName
     *
     * @return void
     */
    public function removeCartCondition($conditionName)
    {
        $conditions = $this->getConditions();
        $conditions->pull($conditionName);
        $this->saveConditions($conditions);
    }

    /**
     * Remove a condition that has been applied on an item that is already on the cart
     *
     * @param $itemId
     * @param $conditionName
     *
     * @return bool
     */
    public function removeItemCondition($itemId, $conditionName)
    {
        if ($item != $this->getContent()->get($itemId)) {
            return false;
        }

        if ($this->itemHasConditions($item)) {
            // NOTE:
            // we do it this way, we get first conditions and store
            // it in a temp variable $originalConditions, then we will modify the array there
            // and after modification we will store it again on $item['conditions']
            // This is because of ArrayAccess implementation
            // see link for more info: http://stackoverflow.com/questions/20053269/indirect-modification-of-overloaded-element-of-splfixedarray-has-no-effect

            $tempConditionsHolder = $item["conditions"];

            if (is_array($tempConditionsHolder)) {
                // if the item's conditions is in array format
                // we will iterate through all of it and check if the name matches
                // to the given name the user wants to remove, if so, remove it

                foreach($tempConditionsHolder as $k => $condition) {
                    if ($condition->getName() == $conditionName) {
                        unset($tempConditionsHolder[$k]);
                    }
                }

                $item["conditions"] = $tempConditionsHolder;
            } else {
                // if the item condition is not an array, we will check if it is
                // an instance of a Condition, if so, we will check if the name matches
                // on the given condition name the user wants to remove, if so,
                // lets just make $item['conditions'] an empty array as there's just 1 condition on it anyway

                $conditionInstance = "Ozanmuyes\\Cart\\CartCondition";

                if ($item["conditions"] instanceof $conditionInstance) {
                    if ($tempConditionsHolder->getName() == $conditionName) {
                        $item["conditions"] = [];
                    }
                }
            }
        }

        $this->update($itemId, [
            "conditions" => $item["conditions"]
        ]);

        return true;
    }

    /**
     * Clears all conditions on a cart,
     * this does not remove conditions that has been added specifically to an item/product.
     * If you wish to remove a specific condition to a product, you may use the method: removeItemCondition($itemId, $conditionName)
     *
     * @return void
     */
    public function clearCartConditions()
    {
        $this->session->put($this->sessionKeyCartConditions, []);
    }

    /**
     * Get cart sub total
     *
     * @return float
     */
    public function getSubTotal()
    {
        $cart = $this->getContent();

        $sum = $cart->sum(function($item) {
            $originalPrice = $item->price;
            $newPrice = 0.00;
            $processed = 0;

            if ($this->itemHasConditions($item)) {
                if (is_array($item->conditions)) {
                    foreach($item->conditions as $condition) {
                        if ($condition->getTarget() === "item") {
                            ($processed > 0) ? $toBeCalculated = $newPrice : $toBeCalculated = $originalPrice;

                            $newPrice = $condition->applyCondition($toBeCalculated);

                            $processed++;
                        }
                    }
                } else {
                    if ($item["conditions"]->getTarget() === "item") {
                        $newPrice = $item["conditions"]->applyCondition($originalPrice);
                    }
                }

                return ($newPrice * $item->quantity);
            } else {
                return ($originalPrice * $item->quantity);
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
        $conditions = $this->getConditions();
        $subTotal = $this->getSubTotal();
        $newTotal = 0.00;
        $process = 0;

        // if no conditions were added, just return the sub total
        if (!$conditions->count()) {
            return $subTotal;
        }

        $conditions->each(function($condition) use ($subTotal, &$newTotal, &$process) {
            if ($condition->getTarget() === "subtotal") {
                ($process > 0) ? $toBeCalculated = $newTotal : $toBeCalculated = $subTotal;

                $newTotal = $condition->applyCondition($toBeCalculated);

                $process++;
            }
        });

        return $newTotal;
    }

    /**
     * Get total quantity of items in the cart
     *
     * @return int
     */
    public function getTotalQuantity()
    {
        $items = $this->getContent();

        if ($items->isEmpty()) {
            return 0;
        }

        $count = $items->sum(function($item) {
            return $item["quantity"];
        });

        return $count;
    }

    /**
     * Get the cart
     *
     * @return CartCollection
     */
    public function getContent()
    {
        return (new CartCollection($this->session->get($this->sessionKeyCartItems)));
    }

    /**
     * Check if cart is empty
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
     *
     * @return array $item;
     *
     * @throws InvalidItemException
     */
    protected function validate($item)
    {
        $rules = [
            "id" => "required",
            "price" => "required|numeric",
            "quantity" => "required|numeric|min:1",
            "name" => "required",
        ];

        $validator = CartItemValidator::make($item, $rules);

        if ($validator->fails()) {
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
     * Check if an item has condition
     *
     * @param $item
     *
     * @return bool
     */
    protected function itemHasConditions($item)
    {
        if (!isset($item["conditions"])) {
            return false;
        }

        if (is_array($item["conditions"])) {
            return count($item["conditions"]) > 0;
        }

        $conditionInstance = "Ozanmuyes\\Cart\\CartCondition";

        return ($item["conditions"] instanceof $conditionInstance);
    }

    /**
     * Update a cart item quantity relative to its current quantity
     *
     * @param $item
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    protected function updateQuantityRelative($item, $key, $value)
    {
        if (preg_match('/\-/', $value) == 1) {
            $value = (int) str_replace('-', '', $value);

            // we will not allowed to reduced quantity to 0, so if the given value
            // would result to item quantity of 0, we will not do it.
            if (($item[$key] - $value) > 0) {
                $item[$key] -= $value;
            }
        } elseif (preg_match('/\+/', $value) == 1) {
            $item[$key] += (int) str_replace('+', '', $value);
        } else {
            $item[$key] += (int) $value;
        }

        return $item;
    }

    /**
     * Update cart item quantity not relative to its current quantity value
     *
     * @param $item
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    protected function updateQuantityNotRelative($item, $key, $value)
    {
        $item[$key] = (int) $value;

        return $item;
    }
}
