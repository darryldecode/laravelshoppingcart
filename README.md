# Laravel Shopping Cart
[![Build Status](https://travis-ci.org/darryldecode/laravelshoppingcart.svg?branch=master)](https://travis-ci.org/darryldecode/laravelshoppingcart)
[![Total Downloads](https://poser.pugx.org/darryldecode/cart/downloads.svg)](https://packagist.org/packages/darryldecode/cart)
[![License](https://poser.pugx.org/darryldecode/cart/license.svg)](https://packagist.org/packages/darryldecode/cart)

A Shopping Cart Implementation for Laravel Framework

##INSTALLATION

Install the package through [Composer](http://getcomposer.org/). Edit your project's `composer.json` file by adding:

```php
"require": {
	"laravel/framework": "4.2.*",
	"darryldecode/laravelshoppingcart": "dev-master"
}
```

Next, run the Composer update command from the Terminal:

    composer update "darryldecode/laravelshoppingcart"

##CONFIGURATION

1. Open app/config/app.php and addd this line to your Service Providers Array
  
  'Darryldecode\Cart\CartServiceProvider'
  
2. Open app/config/app.php and addd this line to your Aliases

  'Cart' => 'Darryldecode\Cart\Facades\CartFacade'
  
## HOW TO USE
* [Usage](#usage)
* [Conditions](#conditions)
* [Instances](#instances)
* [Exceptions](#exceptions)
* [Events](#events)
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

$cartCollection = Cart::getContent($itemId);

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
$subTotal = Cart::getSubTotal();
```

## Conditions

Laravel Shopping Cart supports cart conditions.
Condiitons are very useful in terms of (coupons,discounts,sale,per-item sale and discounts etc.)
See below carefully on how to use conditions.

Conditions can be added on:
1.) Cart
2.) Item

First let's add a condition on a Cart Bases:

There are also several ways of adding a condtion on a cart:
NOTE: When adding a condition on a cart bases, the 'target' should have value of 'total'.

```php

// add condition single condition
$condition = new CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'total',
    'value' => '12.5%',
));

Cart::condition($condition);

// or add multiple conditions from different condition instances
$condition1 = new CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'total',
    'value' => '12.5%',
));
$condition2 = new CartCondition(array(
    'name' => 'Express Shipping $15',
    'type' => 'shipping',
    'target' => 'total',
    'value' => '+15',
));
Cart::condition($condition1);
Cart::condition($condition2);

// or add multiple conditions one condition instances
$condition = new CartCondition(array(
        array(
            'name' => 'COUPON LESS 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-12.5%',
        ),
        array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '+15',
        )
    )
);
Cart::condition($condition);
```

NOTE: All cart based conditions should be applied before calling **Cart::getTotal()**

Then Finaly you can call **Cart::getTotal()** to get the Cart Total with the applied conditions.
```php
$cartTotal = Cart::getTotal(); // the total will be calculated based on the conditions you ave provided
```

Next is the Condition on Per-Item Bases.

This is very useful if you have coupons to be applied specifically on an item and not on the whole cart value.

NOTE: When adding a condition on a per-item bases, the 'target' should have value of 'subtotal'.

Now let's add condition on an item.

```php

// lets create first our condition instance
$saleCondition = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'tax',
            'target' => 'subtotal',
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
$itemConditions = new CartCondition(array(
            array(
                'name' => 'SALE 5%',
                'type' => 'sale',
                'target' => 'subtotal',
                'value' => '-5%',
            ),
            array(
                'name' => 'Item Gift Pack 25.00',
                'type' => 'promo',
                'target' => 'subtotal',
                'value' => '-25',
            ),
            array(
                'name' => 'MISC',
                'type' => 'misc',
                'target' => 'subtotal',
                'value' => '+10',
            )
        ));
        
$item = array(
          'id' => 456,
          'name' => 'Sample Item 1',
          'price' => 100,
          'quantity' => 1,
          'attributes' => array(),
          'conditions' => $itemConditions
      );
      
Cart::add($item);
  
// This is also valid
$itemCondition1 = new CartCondition(array(
          array(
              'name' => 'SALE 5%',
              'type' => 'sale',
              'target' => 'subtotal',
              'value' => '-5%',
          )
      ));
$itemCondition2 = new CartCondition(array(
    'name' => 'Item Gift Pack 25.00',
    'type' => 'promo',
    'target' => 'subtotal',
    'value' => '-25',
));
$itemCondition3 = new CartCondition(array(
    'name' => 'MISC',
    'type' => 'misc',
    'target' => 'subtotal',
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

Then Finaly you can call **Cart::getSubTotal()** to get the Cart sub total with the applied conditions.
```php
$cartSubTotal = Cart::getSubTotal(); // the subtotal will be calculated based on the conditions you have provided
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

## License

The Laravel Shopping Cart is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Disclaimer

THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR, OR ANY OF THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
