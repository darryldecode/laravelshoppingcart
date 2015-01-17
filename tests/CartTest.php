<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/12/2015
 * Time: 9:59 PM
 */

use Darryldecode\Cart\Cart;
use Mockery as m;

require_once __DIR__.'/helpers/SessionMock.php';

class CartTest extends PHPUnit_Framework_TestCase  {

    /**
     * @var Darryldecode\Cart\Cart
     */
    protected $cart;

    public function setUp()
    {
        $events = m::mock('Illuminate\Events\Dispatcher');
        $events->shouldReceive('fire');

        $this->cart = new Cart(
            new SessionMock(),
            $events,
            'shopping',
            'SAMPLESESSIONKEY'
        );
    }

    public function tearDown()
    {
        m::close();
    }

    public function test_cart_can_add_item()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array());

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertEquals(1, $this->cart->getContent()->count(),'Cart content should be 1');
        $this->assertEquals(455, $this->cart->getContent()->first()['id'], 'Item added has ID of 455 so first content ID should be 455');
        $this->assertEquals(100.99, $this->cart->getContent()->first()['price'], 'Item added has price of 100.99 so first content price should be 100.99');
    }

    public function test_cart_can_add_items_as_array()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => array()
        );

        $this->cart->add($item);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertEquals(1, $this->cart->getContent()->count(), 'Cart should have 1 item on it');
        $this->assertEquals(456, $this->cart->getContent()->first()['id'], 'The first content must have ID of 456');
        $this->assertEquals('Sample Item', $this->cart->getContent()->first()['name'], 'The first content must have name of "Sample Item"');
    }

    public function test_cart_can_add_items_with_multidimensional_array()
    {
        $items = array(
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
                'attributes' => array()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 4,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertCount(3, $this->cart->getContent()->toArray(), 'Cart should have 3 items');
    }

    public function test_cart_can_add_item_without_attributes()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4
        );

        $this->cart->add($item);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
    }

    public function test_cart_items_attributes()
    {
        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 67.99,
            'quantity' => 4,
            'attributes' => array(
                'size' => 'L',
                'color' => 'blue'
            )
        );

        $this->cart->add($item);

        $this->assertFalse($this->cart->isEmpty(), 'Cart should not be empty');
        $this->assertCount(2, $this->cart->getContent()->first()['attributes'], 'Item\'s attribute should have two');
        $this->assertEquals('L', $this->cart->getContent()->first()['attributes']['size'], 'Item should have attribute size of L');
        $this->assertEquals('blue', $this->cart->getContent()->first()['attributes']['color'], 'Item should have attribute color of blue');
    }

    public function test_cart_update_existing_item()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        $itemIdToEvaluate = 456;

        $item = $this->cart->get($itemIdToEvaluate);
        $this->assertEquals('Sample Item 1', $item['name'], 'Item name should be "Sample Item 1"');
        $this->assertEquals(67.99, $item['price'], 'Item price should be "67.99"');
        $this->assertEquals(3, $item['quantity'], 'Item quantity should be 3');

        // when cart's item quantity is updated, the subtotal should be updated as well
        $this->cart->update(456, array(
            'name' => 'Renamed',
            'quantity' => 2,
            'price' => 105,
        ));

        $item = $this->cart->get($itemIdToEvaluate);
        $this->assertEquals('Renamed', $item['name'], 'Item name should be "Renamed"');
        $this->assertEquals(105, $item['price'], 'Item price should be 105');
        $this->assertEquals(2, $item['quantity'], 'Item quantity should be 2');
    }

    public function test_item_price_should_be_normalized_when_added_to_cart()
    {
        // add a price in a string format should be converted to float
        $this->cart->add(455, 'Sample Item', '100.99', 2, array());

        $this->assertInternalType('float',$this->cart->getContent()->first()['price'], 'Cart price should be a float');
    }

    public function test_it_removes_an_item_on_cart_by_item_id()
    {
        $items = array(
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
                'attributes' => array()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 4,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        $removeItemId = 456;

        $this->cart->remove($removeItemId);

        $this->assertCount(2, $this->cart->getContent()->toArray(), 'Cart must have 2 items left');
        $this->assertFalse($this->cart->getContent()->hasItem($removeItemId), 'Cart must have not contain the remove item anymore');
    }

    public function test_cart_sub_total()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 1,
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array()
            ),
            array(
                'id' => 856,
                'name' => 'Sample Item 3',
                'price' => 50.25,
                'quantity' => 1,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // if we remove an item, the sub total should be updated as well
        $this->cart->remove(456);

        $this->assertEquals(119.5, $this->cart->getSubTotal(), 'Cart should have sub total of 119.5');
    }

    public function test_sub_total_when_item_quantity_is_updated()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        $this->assertEquals(273.22, $this->cart->getSubTotal(), 'Cart should have sub total of 273.22');

        // when cart's item quantity is updated, the subtotal should be updated as well
        $this->cart->update(456, array('quantity' => 2));

        $this->assertEquals(205.23, $this->cart->getSubTotal(), 'Cart should have sub total of 205.23');
    }

    public function test_should_throw_exception_when_provided_invalid_values_scenario_one()
    {
        $this->setExpectedException('Darryldecode\Cart\Exceptions\InvalidItemException');

        $this->cart->add(455, 'Sample Item', 100.99, 0, array());
    }

    public function test_should_throw_exception_when_provided_invalid_values_scenario_two()
    {
        $this->setExpectedException('Darryldecode\Cart\Exceptions\InvalidItemException');

        $this->cart->add('', 'Sample Item', 100.99, 2, array());
    }

    public function test_should_throw_exception_when_provided_invalid_values_scenario_three()
    {
        $this->setExpectedException('Darryldecode\Cart\Exceptions\InvalidItemException');

        $this->cart->add(523, '', 100.99, 2, array());
    }

    public function test_clearing_cart()
    {
        $items = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 3,
                'attributes' => array()
            ),
            array(
                'id' => 568,
                'name' => 'Sample Item 2',
                'price' => 69.25,
                'quantity' => 1,
                'attributes' => array()
            ),
        );

        $this->cart->add($items);

        $this->assertFalse($this->cart->isEmpty());
    }
}