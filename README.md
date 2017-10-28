# Laravel 5 Shopping Cart
[![Build Status](https://travis-ci.org/darryldecode/laravelshoppingcart.svg?branch=master)](https://travis-ci.org/darryldecode/laravelshoppingcart)
[![Total Downloads](https://poser.pugx.org/darryldecode/cart/d/total.svg)](https://packagist.org/packages/darryldecode/cart)
[![License](https://poser.pugx.org/darryldecode/cart/license.svg)](https://packagist.org/packages/darryldecode/cart)

A Shopping Cart Implementation for Laravel Framework

## QUICK PARTIAL DEMO

Demo: https://shoppingcart-demo.darrylfernandez.com/cart

Git repo of the demo: https://github.com/darryldecode/laravelshoppingcart-demo

## INSTALLATION

Install the package through [Composer](http://getcomposer.org/). 

For Laravel 5.1~:
```composer require "darryldecode/cart:~2.0"```
    
For Laravel 5.4~:
```composer require "darryldecode/cart:~3.0"```

## CONFIGURATION

1. Open config/app.php and add this line to your Service Providers Array
  ```php
  Darryldecode\Cart\CartServiceProvider::class
  ```

2. Open config/app.php and add this line to your Aliases

```php
  'Cart' => Darryldecode\Cart\Facades\CartFacade::class
  ```

## HOW TO USE
* [Usage](#usage)
* [Conditions](#conditions)
* [Items](#items)
* [Instances](#instances)
* [Exceptions](#exceptions)
* [Events](#events)
* [Format Response](#format)
* [Examples](#examples)
* [Using Different Storage](#storage)
* [Changelogs](#changelogs)
* [License](#license)

## Usage

Adding Item on Cart: **Cart::add()**

There are several ways you can add items on your cart, see below:

```php
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

// Simplest form to add item on your cart
Cart::add(455, 'Sample Item', 100.99, 2, array());

// array format
Cart::add(array(
    'id' => 456,
    'name' => 'Sample Item',
    'price' => 67.99,
    'quantity' => 4,
    'attributes' => array()
));

// add multiple items at one time
Cart::add(array(
  array(
      'id' => 456,
      'name' => 'Sample Item 1',
      'price' => 67.99,
      'quantity' => 4,
      'attributes' => array()
  ),
  array(
      'id' => 568,
      'name' => 'Sample Item 2',
      'price' => 69.25,
      'quantity' => 4,
      'attributes' => array(
        'size' => 'L',
        'color' => 'blue'
      )
  ),
));

// NOTE:
// Please keep in mind that when adding an item on cart, the "id" should be unique as it serves as
// row identifier as well. If you provide same ID, it will assume the operation will be an update to its quantity
// to avoid cart item duplicates
```

Updating an item on a cart: **Cart::update()**

Updating an item on a cart is very simple:

```php
/**
 * update a cart
 *
 * @param $id (the item ID)
 * @param array $data
 *
 * the $data will be an associative array, you don't need to pass all the data, only the key value
 * of the item you want to update on it
 */

Cart::update(456, array(
  'name' => 'New Item Name', // new item name
  'price' => 98.67, // new item price, price can also be a string format like so: '98.67'
));

// you may also want to update a product's quantity
Cart::update(456, array(
  'quantity' => 2, // so if the current product has a quantity of 4, another 2 will be added so this will result to 6
));

// you may also want to update a product by reducing its quantity, you do this like so:
Cart::update(456, array(
  'quantity' => -1, // so if the current product has a quantity of 4, it will subtract 1 and will result to 3
));

// NOTE: as you can see by default, the quantity update is relative to its current value
// if you want to just totally replace the quantity instead of incrementing or decrementing its current quantity value
// you can pass an array in quantity value like so:
Cart::update(456, array(
  'quantity' => array(
      'relative' => false,
      'value' => 5
  ),
));
// so with that code above as relative is flagged as false, if the item's quantity before is 2 it will now be 5 instead of
// 5 + 2 which results to 7 if updated relatively..
```

Removing an item on a cart: **Cart::remove()**

Removing an item on a cart is very easy:

```php
/**
 * removes an item on cart by item ID
 *
 * @param $id
 */

Cart::remove(456);
```

Getting an item on a cart: **Cart::get()**

```php

/**
 * get an item on a cart by item ID
 * if item ID is not found, this will return null
 *
 * @param $itemId
 * @return null|array
 */

$itemId = 456;

Cart::get($itemId);

// You can also get the sum of the Item multiplied by its quantity, see below:
$summedPrice = Cart::get($itemId)->getPriceSum();
```

Getting cart's contents and count: **Cart::getContent()**

```php

/**
 * get the cart
 *
 * @return CartCollection
 */

$cartCollection = Cart::getContent();

// NOTE: Because cart collection extends Laravel's Collection
// You can use methods you already know about Laravel's Collection
// See some of its method below:

// count carts contents
$cartCollection->count();

// transformations
$cartCollection->toArray();
$cartCollection->toJson();
```

Check if cart is empty: **Cart::isEmpty()**

```php
/**
* check if cart is empty
*
* @return bool
*/
Cart::isEmpty();
```

Get cart total quantity: **Cart::getTotalQuantity()**

```php
/**
* get total quantity of items in the cart
*
* @return int
*/
$cartTotalQuantity = Cart::getTotalQuantity();
```

Get cart subtotal: **Cart::getSubTotal()**

```php
/**
* get cart sub total
*
* @return float
*/
$subTotal = Cart::getSubTotal();
```

Get cart total: **Cart::getTotal()**

```php
/**
 * the new total in which conditions are already applied
 *
 * @return float
 */
$total = Cart::getTotal();
```

Clearing the Cart: **Cart::clear()**

```php
/**
* clear cart
*
* @return void
*/
Cart::clear();
```

## Conditions

Laravel Shopping Cart supports cart conditions.
Conditions are very useful in terms of (coupons,discounts,sale,per-item sale and discounts etc.)
See below carefully on how to use conditions.

Conditions can be added on:

1.) Whole Cart Value bases

2.) Per-Item Bases

First let's add a condition on a Cart Bases:

There are also several ways of adding a condition on a cart:
NOTE:

When adding a condition on a cart bases, the 'target' should have value of 'subtotal'.
And when adding a condition on an item, the 'target' should be 'item'.
The order of operation also during calculation will vary on the order you have added the conditions.

Also, when adding conditions, the 'value' field will be the bases of calculation.

```php

// add single condition on a cart bases
$condition = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '12.5%',
    'attributes' => array( // attributes field is optional
    	'description' => 'Value added tax',
    	'more_data' => 'more data here'
    )
));

Cart::condition($condition);

// or add multiple conditions from different condition instances
$condition1 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '12.5%',
    'order' => 2
));
$condition2 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'Express Shipping $15',
    'type' => 'shipping',
    'target' => 'subtotal',
    'value' => '+15',
    'order' => 1
));
Cart::condition($condition1);
Cart::condition($condition2);

// The property 'Order' lets you add different conditions through for example a shopping process with multiple
// pages and still be able to set an order to apply the conditions. If no order is defined defaults to 0 

// or add multiple conditions as array
Cart::condition([$condition1, $condition2]);

// To get all applied conditions on a cart, use below:
$cartConditions = Cart::getConditions();
foreach($carConditions as $condition)
{
    $condition->getTarget(); // the target of which the condition was applied
    $condition->getName(); // the name of the condition
    $condition->getType(); // the type
    $condition->getValue(); // the value of the condition
    $condition->getOrder(); // the order of the condition
    $condition->getAttributes(); // the attributes of the condition, returns an empty [] if no attributes added
}

// You can also get a condition that has been applied on the cart by using its name, use below:
$condition = Cart::getCondition('VAT 12.5%');
$condition->getTarget(); // the target of which the condition was applied
$condition->getName(); // the name of the condition
$condition->getType(); // the type
$condition->getValue(); // the value of the condition
$condition->getAttributes(); // the attributes of the condition, returns an empty [] if no attributes added

// You can get the conditions calculated value by providing the subtotal, see below:
$subTotal = Cart::getSubTotal();
$condition = Cart::getCondition('VAT 12.5%');
$conditionCalculatedValue = $condition->getCalculatedValue($subTotal);
```

NOTE: All cart based conditions should be applied before calling **Cart::getTotal()**

Then Finally you can call **Cart::getTotal()** to get the Cart Total with the applied conditions.
```php
$cartTotal = Cart::getTotal(); // the total will be calculated based on the conditions you ave provided
```

Next is the Condition on Per-Item Bases.

This is very useful if you have coupons to be applied specifically on an item and not on the whole cart value.

NOTE: When adding a condition on a per-item bases, the 'target' should have value of 'item'.

Now let's add condition on an item.

```php

// lets create first our condition instance
$saleCondition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'tax',
            'target' => 'item',
            'value' => '-5%',
        ));

// now the product to be added on cart
$product = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => $saleCondition
        );

// finally add the product on the cart
Cart::add($product);

// you may also add multiple condition on an item
$itemCondition1 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'SALE 5%',
    'type' => 'sale',
    'target' => 'item',
    'value' => '-5%',
));
$itemCondition2 = new CartCondition(array(
    'name' => 'Item Gift Pack 25.00',
    'type' => 'promo',
    'target' => 'item',
    'value' => '-25',
));
$itemCondition3 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'MISC',
    'type' => 'misc',
    'target' => 'item',
    'value' => '+10',
));

$item = array(
          'id' => 456,
          'name' => 'Sample Item 1',
          'price' => 100,
          'quantity' => 1,
          'attributes' => array(),
          'conditions' => [$itemCondition1, $itemCondition2, $itemCondition3]
      );

Cart::add($item);
```

NOTE: All cart per-item conditions should be applied before calling **Cart::getSubTotal()**

Then Finally you can call **Cart::getSubTotal()** to get the Cart sub total with the applied conditions.
```php
$cartSubTotal = Cart::getSubTotal(); // the subtotal will be calculated based on the conditions you have provided
```

Add condition to exisiting Item on the cart: **Cart::addItemCondition($productId, $itemCondition)**

Adding Condition to an existing Item on the cart is simple as well.

This is very useful when adding new conditions on an item during checkout process like coupons and promo codes.
Let's see the example how to do it:

```php
$productID = 456;
$coupon101 = new CartCondition(array(
            'name' => 'COUPON 101',
            'type' => 'coupon',
            'target' => 'item',
            'value' => '-5%',
        ));

Cart::addItemCondition($productID, $coupon101);
```

Clearing Cart Conditions: **Cart::clearCartConditions()**
```php
/**
* clears all conditions on a cart,
* this does not remove conditions that has been added specifically to an item/product.
* If you wish to remove a specific condition to a product, you may use the method: removeItemCondition($itemId,$conditionName)
*
* @return void
*/
Cart::clearCartConditions()
```

Remove Specific Cart Condition: **Cart::removeCartCondition($conditionName)**
```php
/**
* removes a condition on a cart by condition name,
* this can only remove conditions that are added on cart bases not conditions that are added on an item/product.
* If you wish to remove a condition that has been added for a specific item/product, you may
* use the removeItemCondition(itemId, conditionName) method instead.
*
* @param $conditionName
* @return void
*/
$conditionName = 'Summer Sale 5%';

Cart::removeCartCondition($conditionName)
```

Remove Specific Item Condition: **Cart::removeItemCondition($itemId, $conditionName)**
```php
/**
* remove a condition that has been applied on an item that is already on the cart
*
* @param $itemId
* @param $conditionName
* @return bool
*/
Cart::removeItemCondition($itemId, $conditionName)
```

Clear all Item Conditions: **Cart::clearItemConditions($itemId)**
```php
/**
* remove all conditions that has been applied on an item that is already on the cart
*
* @param $itemId
* @return bool
*/
Cart::clearItemConditions($itemId)
```

Get conditions by type: **Cart::getConditionsByType($type)**
```php
/**
* Get all the condition filtered by Type
* Please Note that this will only return condition added on cart bases, not those conditions added
* specifically on an per item bases
*
* @param $type
* @return CartConditionCollection
*/
public function getConditionsByType($type)
```

Remove conditions by type: **Cart::removeConditionsByType($type)**
```php
/**
* Remove all the condition with the $type specified
* Please Note that this will only remove condition added on cart bases, not those conditions added
* specifically on an per item bases
*
* @param $type
* @return $this
*/
public function removeConditionsByType($type)
```

## Items

The method **Cart::getContent()** returns a collection of items. 

To get the id of an item, use the property **$item->id**.

To get the name of an item, use the property **$item->name**.

To get the quantity of an item, use the property **$item->quantity**.

To get the attributes of an item, use the property **$item->attributes**.

To get the price of a single item without the conditions applied, use the property **$item->price**.

To get the subtotal of an item without the conditions applied, use the method **$item->getPriceSum()**. 
```php
/**
* get the sum of price
*
* @return mixed|null
*/
public function getPriceSum()

```

To get the price of a single item without the conditions applied, use the method 

**$item->getPriceWithConditions()**.
```php
/**
* get the single price in which conditions are already applied
*
* @return mixed|null
*/
public function getPriceWithConditions() 

```

To get the subtotal of an item with the conditions applied, use the method 

**$item->getPriceSumWithConditions()**
```php
/**
* get the sum of price in which conditions are already applied
*
* @return mixed|null
*/
public function getPriceSumWithConditions()

```

**NOTE**: When you get price with conditions applied, only the conditions assigned to the current item will be calculated. 
Cart conditions won't be applied to price.

## Instances

You may also want multiple cart instances on the same page without conflicts.
To do that,

Create a new Service Provider and then on register() method, you can put this like so:
```php
$this->app['wishlist'] = $this->app->share(function($app)
		{
			$storage = $app['session']; // laravel session storage
			$events = $app['events']; // laravel event handler
			$instanceName = 'wishlist'; // your cart instance name
			$session_key = 'AsASDMCks0ks1'; // your unique session key to hold cart items

			return new Cart(
				$storage,
				$events,
				$instanceName,
				$session_key
			);
		});
		
// for 5.4 or newer
use Darryldecode\Cart\Cart;
use Illuminate\Support\ServiceProvider;

class WishListProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('wishlist', function($app)
        {
            $storage = $app['session'];
            $events = $app['events'];
            $instanceName = 'cart_2';
            $session_key = '88uuiioo99888';
            return new Cart(
                $storage,
                $events,
                $instanceName,
                $session_key,
                config('shopping_cart')
            );
        });
    }
}
```

IF you are having problem with multiple cart instance, please see the codes on
this demo repo here: [DEMO](https://github.com/darryldecode/laravelshoppingcart-demo)

## Exceptions

There are currently only two exceptions.

| Exception                             | Description                                                                           |
| ------------------------------------- | --------------------------------------------------------------------------------- |
| *InvalidConditionException*           | When there is an invalid field value during instantiating a new Condition         |
| *InvalidItemException*                | When a new product has invalid field values (id,name,price,quantity)              |


## Events

The cart has currently 9 events you can listen and hook some actons.

| Event                            | Fired                                   |
| -------------------------------- | --------------------------------------- |
| cart.created($cart)              | When a cart is instantiated             |
| cart.adding($items, $cart)       | When an item is attempted to be added   |
| cart.added($items, $cart)        | When an item is added on cart           |
| cart.updating($items, $cart)     | When an item is being updated           |
| cart.updated($items, $cart)      | When an item is updated                 |
| cart.removing($id, $cart)        | When an item is being remove            |
| cart.removed($id, $cart)         | When an item is removed                 |
| cart.clearing($cart)             | When a cart is attempted to be cleared  |
| cart.cleared($cart)              | When a cart is cleared                  |

**NOTE**: For different cart instance, dealing events is simple. For example you have created another cart instance which
you have given an instance name of "wishlist". The Events will be something like: {$instanceName}.created($cart)

So for you wishlist cart instance, events will look like this:

* wishlist.created($cart)
* wishlist.adding($items, $cart)
* wishlist.added($items, $cart) and so on..

## Format Response

Now you can format all the responses. You can publish the config file from the package or use env vars to set the configuration.
The options you have are:

* format_numbers or env('SHOPPING_FORMAT_VALUES', false) => Activate or deactivate this feature. Default to false,
* decimals or env('SHOPPING_DECIMALS', 0) => Number of decimals you want to show. Defaults to 0.
* dec_point or env('SHOPPING_DEC_POINT', '.') => Decimal point type. Defaults to a '.'.
* thousands_sep or env('SHOPPING_THOUSANDS_SEP', ',') => Thousands separator for value. Defaults to ','.
 
## Examples

```php

// add items to cart
Cart::add(array(
  array(
      'id' => 456,
      'name' => 'Sample Item 1',
      'price' => 67.99,
      'quantity' => 4,
      'attributes' => array()
  ),
  array(
      'id' => 568,
      'name' => 'Sample Item 2',
      'price' => 69.25,
      'quantity' => 4,
      'attributes' => array(
        'size' => 'L',
        'color' => 'blue'
      )
  ),
));

// then you can:
$items = Cart::getContent();

foreach($items as $item)
{
    $item->id; // the Id of the item
    $item->name; // the name
    $item->price; // the single price without conditions applied
    $item->getPriceSum(); // the subtotal without conditions applied
    $item->getPriceWithConditions(); // the single price with conditions applied
    $item->getPriceSumWithConditions(); // the subtotal with conditions applied
    $item->quantity; // the quantity
    $item->attributes; // the attributes

    // Note that attribute returns ItemAttributeCollection object that extends the native laravel collection
    // so you can do things like below:

    if( $item->attributes->has('size') )
    {
        // item has attribute size
    }
    else
    {
        // item has no attribute size
    }
}

// or
$items->each(function($item)
{
    $item->id; // the Id of the item
    $item->name; // the name
    $item->price; // the single price without conditions applied
    $item->getPriceSum(); // the subtotal without conditions applied
    $item->getPriceWithConditions(); // the single price with conditions applied
    $item->getPriceSumWithConditions(); // the subtotal with conditions applied
    $item->quantity; // the quantity
    $item->attributes; // the attributes

    if( $item->attributes->has('size') )
    {
        // item has attribute size
    }
    else
    {
        // item has no attribute size
    }
});

```

## Storage

Using different storage for the carts items is pretty straight forward. The storage 
class that is injected to the Cart's instance will only need methods.

Example we will need a wishlist, and we want to store its key value pair in database instead
of the default session. We do this using below:

Create a new class for your storage:

Eg.
```
class WishListDBStorage {

    public function has($key)
    {
        // your logic here to check if storage has the given key
    }
    
    public function get($key)
    {
        // your logic here to get an item using its key
    }
    
    public function put($key, $value)
    {
        // your logic here to put an item with key value pair
    }
}
```

Then in your service provider for your wishlist cart, you replace the storage
to use your custom storage.

```
use Darryldecode\Cart\Cart;
use Illuminate\Support\ServiceProvider;

class WishListProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('wishlist', function($app)
        {
            $storage = new WishListDBStorage(); <-- Your new custom storage
            $events = $app['events'];
            $instanceName = 'cart_2';
            $session_key = '88uuiioo99888';
            return new Cart(
                $storage,
                $events,
                $instanceName,
                $session_key,
                config('shopping_cart')
            );
        });
    }
}
```


## Changelogs

**2.4.0
- added new method on a condition: $condition->getAttributes(); (Please see [Conditions](#conditions) section)

**2.3.0
- added new Cart Method: Cart::addItemCondition($productId, $itemCondition)
- added new Cart Method: Cart::getTotalQuantity()

**2.2.1
- bug fixes

**2.2.0
- added new Cart Method: Cart::getConditionsByType($type)
- added new Item Method: Cart::removeConditionsByType($type)

**2.1.1
- when a new product with the same ID is added on a cart and a quantity is provided, it will increment its current quantity instead of overwriting it. There's a chance that you will also need to update an item's quantity but not incrementing it but reducing it, please just see the documentation (Please see Cart::update() section, and read carefully)

**2.1.0
- added new Cart Method: getCalculatedValue($totalOrSubTotalOrPrice)
- added new Item Method: getPriceSum()

**2.0.0 (breaking change)
- major changes in dealing with conditions (Please see [Conditions](#conditions) section, and read carefully)
- All conditions added on per item bases should have now target => 'item' instead of 'subtotal'
- All conditions added on per cart bases should have now target => 'subtotal' instead of 'total'

**1.1.0
- added new method: clearCartConditions()
- added new method: removeItemCondition($itemId, $conditionName)
- added new method: removeCartCondition($conditionName)

## License

The Laravel Shopping Cart is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Disclaimer

THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR, OR ANY OF THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
