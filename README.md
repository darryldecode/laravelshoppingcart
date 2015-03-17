# Laravel 5 Shopping Cart
[![Build Status](https://travis-ci.org/darryldecode/laravelshoppingcart.svg?branch=master)](https://travis-ci.org/darryldecode/laravelshoppingcart)
[![Scrutinizer Quality Score](https://scrutinizer-ci.com/g/darryldecode/laravelshoppingcart/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/darryldecode/laravelshoppingcart/)
[![Total Downloads](https://poser.pugx.org/darryldecode/cart/downloads.svg)](https://packagist.org/packages/darryldecode/cart)
[![License](https://poser.pugx.org/darryldecode/cart/license.svg)](https://packagist.org/packages/darryldecode/cart)

A Shopping Cart Implementation for Laravel Framework

##INSTALLATION

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

### Laravel 5

```php
"require": {
	"laravel/framework": "5.0.*",
	"darryldecode/cart": "dev-master"
}
```

Next, run the Composer update command from the Terminal:

    composer update
    
    or
    
    composer update "darryldecode/cart"

##CONFIGURATION

1. Open config/app.php and addd this line to your Service Providers Array
  
  'Darryldecode\Cart\CartServiceProvider'
  
2. Open config/app.php and addd this line to your Aliases

  'Cart' => 'Darryldecode\Cart\Facades\CartFacade'
  
## HOW TO USE
* [Usage](#usage)
* [Conditions](#conditions)
* [Instances](#instances)
* [Exceptions](#exceptions)
* [Events](#events)
* [Examples](#examples)
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
));

Cart::condition($condition);

// or add multiple conditions from different condition instances
$condition1 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'subtotal',
    'value' => '12.5%',
));
$condition2 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'Express Shipping $15',
    'type' => 'shipping',
    'target' => 'subtotal',
    'value' => '+15',
));
Cart::condition($condition1);
Cart::condition($condition2);

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
}

// You can also get a condition that has been applied on the cart by using its name, use below:
$condition = Cart::getCondition('VAT 12.5%');
$condition->getTarget(); // the target of which the condition was applied
$condition->getName(); // the name of the condition
$condition->getType(); // the type
$condition->getValue(); // the value of the condition
```

NOTE: All cart based conditions should be applied before calling **Cart::getTotal()**

Then Finally you can call **Cart::getTotal()** to get the Cart Total with the applied conditions.
```php
$cartTotal = Cart::getTotal(); // the total will be calculated based on the conditions you ave provided
```

Next is the Condition on Per-Item Bases.

This is very useful if you have coupons to be applied specifically on an item and not on the whole cart value.

NOTE: When adding a condition on a per-item bases, the 'target' should have value of 'subtotal'.

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
```

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
    $item->price; // the price
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
    $item->price; // the price
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

## Changelogs

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
