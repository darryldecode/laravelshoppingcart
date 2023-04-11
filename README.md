# Laravel 5 & 6 , 7 & 9 Shopping Cart
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
`composer require "darryldecode/cart:~2.0"`

For Laravel 5.5, 5.6, or 5.7~, 9:

```composer require "darryldecode/cart:~4.0"``` or 
```composer require "darryldecode/cart"```

## CONFIGURATION

1. Open config/app.php and add this line to your Service Providers Array.

```php
Darryldecode\Cart\CartServiceProvider::class
```

2. Open config/app.php and add this line to your Aliases

```php
  'Cart' => Darryldecode\Cart\Facades\CartFacade::class
```

3. Optional configuration file (useful if you plan to have full control)

```php
php artisan vendor:publish --provider="Darryldecode\Cart\CartServiceProvider" --tag="config"
```

## HOW TO USE

-   [Quick Usage](#usage-usage-example)
-   [Usage](#usage)
-   [Conditions](#conditions)
-   [Items](#items)
-   [Associating Models](#associating-models)
-   [Instances](#instances)
-   [Exceptions](#exceptions)
-   [Events](#events)
-   [Format Response](#format)
-   [Examples](#examples)
-   [Using Different Storage](#storage)
-   [License](#license)

## Quick Usage Example

```php
// Quick Usage with the Product Model Association & User session binding

$Product = Product::find($productId); // assuming you have a Product model with id, name, description & price
$rowId = 456; // generate a unique() row ID
$userID = 2; // the user ID to bind the cart contents

// add the product to cart
\Cart::session($userID)->add(array(
    'id' => $rowId,
    'name' => $Product->name,
    'price' => $Product->price,
    'quantity' => 4,
    'attributes' => array(),
    'associatedModel' => $Product
));

// update the item on cart
\Cart::session($userID)->update($rowId,[
	'quantity' => 2,
	'price' => 98.67
]);

// delete an item on cart
\Cart::session($userID)->remove($rowId);

// view the cart items
$items = \Cart::getContent();
foreach($items as $row) {

	echo $row->id; // row ID
	echo $row->name;
	echo $row->qty;
	echo $row->price;
	
	echo $item->associatedModel->id; // whatever properties your model have
        echo $item->associatedModel->name; // whatever properties your model have
        echo $item->associatedModel->description; // whatever properties your model have
}

// FOR FULL USAGE, SEE BELOW..
```

## Usage

### IMPORTANT NOTE!

By default, the cart has a default sessionKey that holds the cart data. This
also serves as a cart unique identifier which you can use to bind a cart to a specific user.
To override this default session Key, you will just simply call the `\Cart::session($sessionKey)` method
BEFORE ANY OTHER METHODS!!.

Example:

```php
$userId // the current login user id

// This tells the cart that we only need or manipulate
// the cart data of a specific user. It doesn't need to be $userId,
// you can use any unique key that represents a unique to a user or customer.
// basically this binds the cart to a specific user.
\Cart::session($userId);

// then followed by the normal cart usage
\Cart::add();
\Cart::update();
\Cart::remove();
\Cart::condition($condition1);
\Cart::getTotal();
\Cart::getSubTotal();
\Cart::getSubTotalWithoutConditions();
\Cart::addItemCondition($productID, $coupon101);
// and so on..
```

See More Examples below:

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

 # ALWAYS REMEMBER TO BIND THE CART TO A USER BEFORE CALLING ANY CART FUNCTION
 # SO CART WILL KNOW WHO'S CART DATA YOU WANT TO MANIPULATE. SEE IMPORTANT NOTICE ABOVE.
 # EXAMPLE: \Cart::session($userId); then followed by cart normal usage.
 
 # NOTE:
 # the 'id' field in adding a new item on cart is not intended for the Model ID (example Product ID)
 # instead make sure to put a unique ID for every unique product or product that has it's own unique prirce, 
 # because it is used for updating cart and how each item on cart are segregated during calculation and quantities. 
 # You can put the model_id instead as an attribute for full flexibility.
 # Example is that if you want to add same products on the cart but with totally different attribute and price.
 # If you use the Product's ID as the 'id' field in cart, it will result to increase in quanity instead
 # of adding it as a unique product with unique attribute and price.

// Simplest form to add item on your cart
Cart::add(455, 'Sample Item', 100.99, 2, array());

// array format
Cart::add(array(
    'id' => 456, // inique row ID
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

// add cart items to a specific user
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->add(array(
    'id' => 456, // inique row ID
    'name' => 'Sample Item',
    'price' => 67.99,
    'quantity' => 4,
    'attributes' => array(),
    'associatedModel' => $Product
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

// updating a cart for a specific user
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->update(456, array(
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

// removing cart item for a specific user's cart
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->remove(456);
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

// get an item on a cart by item ID for a specific user's cart
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->get($itemId);
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

// Getting cart's contents for a specific user
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->getContent($itemId);
```

Check if cart is empty: **Cart::isEmpty()**

```php
/**
* check if cart is empty
*
* @return bool
*/
Cart::isEmpty();

// Check if cart's contents is empty for a specific user
$userId = auth()->user()->id; // or any string represents user identifier
Cart::session($userId)->isEmpty();
```

Get cart total quantity: **Cart::getTotalQuantity()**

```php
/**
* get total quantity of items in the cart
*
* @return int
*/
$cartTotalQuantity = Cart::getTotalQuantity();

// for a specific user
$cartTotalQuantity = Cart::session($userId)->getTotalQuantity();
```

Get cart subtotal: **Cart::getSubTotal()**

```php
/**
* get cart sub total
*
* @return float
*/
$subTotal = Cart::getSubTotal();

// for a specific user
$subTotal = Cart::session($userId)->getSubTotal();
```

Get cart subtotal with out conditions: **Cart::getSubTotalWithoutConditions()**

```php
/**
* get cart sub total with out conditions
*
* @param bool $formatted
* @return float
*/
$subTotalWithoutConditions = Cart::getSubTotalWithoutConditions();

// for a specific user
$subTotalWithoutConditions = Cart::session($userId)->getSubTotalWithoutConditions();
```

Get cart total: **Cart::getTotal()**

```php
/**
 * the new total in which conditions are already applied
 *
 * @return float
 */
$total = Cart::getTotal();

// for a specific user
$total = Cart::session($userId)->getTotal();
```

Clearing the Cart: **Cart::clear()**

```php
/**
* clear cart
*
* @return void
*/
Cart::clear();
Cart::session($userId)->clear();
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

When adding a condition on a cart bases, the 'target' should have value of 'subtotal' or 'total'.
If the target is "subtotal" then this condition will be applied to subtotal.
If the target is "total" then this condition will be applied to total.
The order of operation also during calculation will vary on the order you have added the conditions.

Also, when adding conditions, the 'value' field will be the bases of calculation. You can change this order
by adding 'order' parameter in CartCondition.

```php

// add single condition on a cart bases
$condition = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'subtotal', // this condition will be applied to cart's subtotal when getSubTotal() is called.
    'value' => '12.5%',
    'attributes' => array( // attributes field is optional
    	'description' => 'Value added tax',
    	'more_data' => 'more data here'
    )
));

Cart::condition($condition);
Cart::session($userId)->condition($condition); // for a speicifc user's cart

// or add multiple conditions from different condition instances
$condition1 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'VAT 12.5%',
    'type' => 'tax',
    'target' => 'subtotal', // this condition will be applied to cart's subtotal when getSubTotal() is called.
    'value' => '12.5%',
    'order' => 2
));
$condition2 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'Express Shipping $15',
    'type' => 'shipping',
    'target' => 'subtotal', // this condition will be applied to cart's subtotal when getSubTotal() is called.
    'value' => '+15',
    'order' => 1
));
Cart::condition($condition1);
Cart::condition($condition2);

// Note that after adding conditions that are targeted to be applied on subtotal, the result on getTotal()
// will also be affected as getTotal() depends in getSubTotal() which is the subtotal.

// add condition to only apply on totals, not in subtotal
$condition = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'Express Shipping $15',
    'type' => 'shipping',
    'target' => 'total', // this condition will be applied to cart's total when getTotal() is called.
    'value' => '+15',
    'order' => 1 // the order of calculation of cart base conditions. The bigger the later to be applied.
));
Cart::condition($condition);

// The property 'order' lets you control the sequence of conditions when calculated. Also it lets you add different conditions through for example a shopping process with multiple
// pages and still be able to set an order to apply the conditions. If no order is defined defaults to 0

// NOTE!! On current version, 'order' parameter is only applicable for conditions for cart bases. It does not support on per item conditions.

// or add multiple conditions as array
Cart::condition([$condition1, $condition2]);

// To get all applied conditions on a cart, use below:
$cartConditions = Cart::getConditions();
foreach($cartConditions as $condition)
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

> NOTE: All cart based conditions should be added to cart's conditions before calling **Cart::getTotal()**
> and if there are also conditions that are targeted to be applied to subtotal, it should be added to cart's conditions
> before calling **Cart::getSubTotal()**

```php
$cartTotal = Cart::getSubTotal(); // the subtotal with the conditions targeted to "subtotal" applied
$cartTotal = Cart::getTotal(); // the total with the conditions targeted to "total" applied
$cartTotal = Cart::session($userId)->getSubTotal(); // for a specific user's cart
$cartTotal = Cart::session($userId)->getTotal(); // for a specific user's cart
```

Next is the Condition on Per-Item Bases.

This is very useful if you have coupons to be applied specifically on an item and not on the whole cart value.

> NOTE: When adding a condition on a per-item bases, the 'target' parameter is not needed or can be omitted.
> unlike when adding conditions or per cart bases.

Now let's add condition on an item.

```php

// lets create first our condition instance
$saleCondition = new \Darryldecode\Cart\CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'tax',
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
    'value' => '-5%',
));
$itemCondition2 = new CartCondition(array(
    'name' => 'Item Gift Pack 25.00',
    'type' => 'promo',
    'value' => '-25',
));
$itemCondition3 = new \Darryldecode\Cart\CartCondition(array(
    'name' => 'MISC',
    'type' => 'misc',
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

> NOTE: All cart per-item conditions should be added before calling **Cart::getSubTotal()**

Then Finally you can call **Cart::getSubTotal()** to get the Cart sub total with the applied conditions on each of the items.

```php
// the subtotal will be calculated based on the conditions added that has target => "subtotal"
// and also conditions that are added on per item
$cartSubTotal = Cart::getSubTotal();
```

Add condition to existing Item on the cart: **Cart::addItemCondition($productId, $itemCondition)**

Adding Condition to an existing Item on the cart is simple as well.

This is very useful when adding new conditions on an item during checkout process like coupons and promo codes.
Let's see the example how to do it:

```php
$productID = 456;
$coupon101 = new CartCondition(array(
            'name' => 'COUPON 101',
            'type' => 'coupon',
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

Remove Specific Cart Condition: **Cart::removeCartCondition(\$conditionName)**

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

Clear all Item Conditions: **Cart::clearItemConditions(\$itemId)**

```php
/**
* remove all conditions that has been applied on an item that is already on the cart
*
* @param $itemId
* @return bool
*/
Cart::clearItemConditions($itemId)
```

Get conditions by type: **Cart::getConditionsByType(\$type)**

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

Remove conditions by type: **Cart::removeConditionsByType(\$type)**

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

To get the id of an item, use the property **\$item->id**.

To get the name of an item, use the property **\$item->name**.

To get the quantity of an item, use the property **\$item->quantity**.

To get the attributes of an item, use the property **\$item->attributes**.

To get the price of a single item without the conditions applied, use the property **\$item->price**.

To get the subtotal of an item without the conditions applied, use the method **\$item->getPriceSum()**.

```php
/**
* get the sum of price
*
* @return mixed|null
*/
public function getPriceSum()

```

To get the price of a single item without the conditions applied, use the method

**\$item->getPriceWithConditions()**.

```php
/**
* get the single price in which conditions are already applied
*
* @return mixed|null
*/
public function getPriceWithConditions()

```

To get the subtotal of an item with the conditions applied, use the method

**\$item->getPriceSumWithConditions()**

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

## Associating Models

One can associate a cart item to a model. Let's say you have a `Product` model in your application. With the `associate()` method, you can tell the cart that an item in the cart, is associated to the `Product` model.

That way you can access your model using the property **\$item->model**.

Here is an example:

```php

// add the item to the cart.
$cartItem = Cart::add(455, 'Sample Item', 100.99, 2, array())->associate('Product');

// array format
Cart::add(array(
    'id' => 456,
    'name' => 'Sample Item',
    'price' => 67.99,
    'quantity' => 4,
    'attributes' => array(),
    'associatedModel' => 'Product'
));

// add multiple items at one time
Cart::add(array(
  array(
      'id' => 456,
      'name' => 'Sample Item 1',
      'price' => 67.99,
      'quantity' => 4,
      'attributes' => array(),
      'associatedModel' => 'Product'
  ),
  array(
      'id' => 568,
      'name' => 'Sample Item 2',
      'price' => 69.25,
      'quantity' => 4,
      'attributes' => array(
        'size' => 'L',
        'color' => 'blue'
      ),
      'associatedModel' => 'Product'
  ),
));

// Now, when iterating over the content of the cart, you can access the model.
foreach(Cart::getContent() as $row) {
	echo 'You have ' . $row->qty . ' items of ' . $row->model->name . ' with description: "' . $row->model->description . '" in your cart.';
}
```

**NOTE**: This only works when adding an item to cart.

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

| Exception                   | Description                                                               |
| --------------------------- | ------------------------------------------------------------------------- |
| _InvalidConditionException_ | When there is an invalid field value during instantiating a new Condition |
| _InvalidItemException_      | When a new product has invalid field values (id,name,price,quantity)      |
| _UnknownModelException_     | When you try to associate a none existing model to a cart item.           |

## Events

The cart has currently 9 events you can listen and hook some actons.

| Event                        | Fired                                  |
| ---------------------------- | -------------------------------------- |
| cart.created(\$cart)         | When a cart is instantiated            |
| cart.adding($items, $cart)   | When an item is attempted to be added  |
| cart.added($items, $cart)    | When an item is added on cart          |
| cart.updating($items, $cart) | When an item is being updated          |
| cart.updated($items, $cart)  | When an item is updated                |
| cart.removing($id, $cart)    | When an item is being remove           |
| cart.removed($id, $cart)     | When an item is removed                |
| cart.clearing(\$cart)        | When a cart is attempted to be cleared |
| cart.cleared(\$cart)         | When a cart is cleared                 |

**NOTE**: For different cart instance, dealing events is simple. For example you have created another cart instance which
you have given an instance name of "wishlist". The Events will be something like: {$instanceName}.created($cart)

So for you wishlist cart instance, events will look like this:

-   wishlist.created(\$cart)
-   wishlist.adding($items, $cart)
-   wishlist.added($items, $cart) and so on..

## Format Response

Now you can format all the responses. You can publish the config file from the package or use env vars to set the configuration.
The options you have are:

-   format_numbers or env('SHOPPING_FORMAT_VALUES', false) => Activate or deactivate this feature. Default to false,
-   decimals or env('SHOPPING_DECIMALS', 0) => Number of decimals you want to show. Defaults to 0.
-   dec_point or env('SHOPPING_DEC_POINT', '.') => Decimal point type. Defaults to a '.'.
-   thousands_sep or env('SHOPPING_THOUSANDS_SEP', ',') => Thousands separator for value. Defaults to ','.

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
of the default session.

To do this, we will need first a database table that will hold our cart data.
Let's create it by issuing `php artisan make:migration create_cart_storage_table`

Example Code:

```php
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCartStorageTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_storage', function (Blueprint $table) {
            $table->string('id')->index();
            $table->longText('cart_data');
            $table->timestamps();

            $table->primary('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_storage');
    }
}
```

Next, lets create an eloquent Model on this table so we can easily deal with the data. It is up to you where you want
to store this model. For this example, lets just assume to store it in our App namespace.

Code:

```php
namespace App;

use Illuminate\Database\Eloquent\Model;


class DatabaseStorageModel extends Model
{
    protected $table = 'cart_storage';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id', 'cart_data',
    ];

    public function setCartDataAttribute($value)
    {
        $this->attributes['cart_data'] = serialize($value);
    }

    public function getCartDataAttribute($value)
    {
        return unserialize($value);
    }
}
```

Next, Create a new class for your storage to be injected to our cart instance:

Eg.

```php
class DBStorage {

    public function has($key)
    {
        return DatabaseStorageModel::find($key);
    }

    public function get($key)
    {
        if($this->has($key))
        {
            return new CartCollection(DatabaseStorageModel::find($key)->cart_data);
        }
        else
        {
            return [];
        }
    }

    public function put($key, $value)
    {
        if($row = DatabaseStorageModel::find($key))
        {
            // update
            $row->cart_data = $value;
            $row->save();
        }
        else
        {
            DatabaseStorageModel::create([
                'id' => $key,
                'cart_data' => $value
            ]);
        }
    }
}
```

For example you can also leverage Laravel's Caching (redis, memcached, file, dynamo, etc) using the example below. Example also includes cookie persistance, so that cart would be still available for 30 days. Sessions by default persists only 20 minutes. 

```php
namespace App\Cart;

use Carbon\Carbon;
use Cookie;
use Darryldecode\Cart\CartCollection;

class CacheStorage
{
    private $data = [];
    private $cart_id;

    public function __construct()
    {
        $this->cart_id = \Cookie::get('cart');
        if ($this->cart_id) {
            $this->data = \Cache::get('cart_' . $this->cart_id, []);
        } else {
            $this->cart_id = uniqid();
        }
    }

    public function has($key)
    {
        return isset($this->data[$key]);
    }

    public function get($key)
    {
        return new CartCollection($this->data[$key] ?? []);
    }

    public function put($key, $value)
    {
        $this->data[$key] = $value;
        \Cache::put('cart_' . $this->cart_id, $this->data, Carbon::now()->addDays(30));

        if (!Cookie::hasQueued('cart')) {
            Cookie::queue(
                Cookie::make('cart', $this->cart_id, 60 * 24 * 30)
            );
        }
    }
}
```

To make this the cart's default storage, let's update the cart's configuration file.
First, let us publish first the cart config file for us to enable to override it.
`php artisan vendor:publish --provider="Darryldecode\Cart\CartServiceProvider" --tag="config"`

after running that command, there should be a new file on your config folder name `shopping_cart.php`

Open this file and let's update the storage use. Find the key which says `'storage' => null,`
And update it to your newly created DBStorage Class, which on our example,
`'storage' => \App\DBStorage::class,`

OR If you have multiple cart instance (example WishList), you can inject the custom database storage
to your cart instance by injecting it to the service provider of your wishlist cart, you replace the storage
to use your custom storage. See below:

```php
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
            $storage = new DBStorage(); <-- Your new custom storage
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

Still feeling confuse on how to do custom database storage? Or maybe doing multiple cart instances?
See the demo repo to see the codes and how you can possibly do it and expand base on your needs or make it
as a guide & reference. See links below:

[See Demo App Here](https://shoppingcart-demo.darrylfernandez.com/cart)

OR

[See Demo App Repo Here](https://github.com/darryldecode/laravelshoppingcart-demo)

## License

The Laravel Shopping Cart is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)

### Disclaimer

THIS SOFTWARE IS PROVIDED "AS IS" AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE AUTHOR, OR ANY OF THE CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
