<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/12/2015
 * Time: 9:59 PM
 */

use Darryldecode\Cart\Cart;
use Darryldecode\Cart\CartCondition;
use Mockery as m;

require_once __DIR__.'/helpers/SessionMock.php';

class CartConditionTest extends PHPUnit_Framework_TestCase  {

    /**
     * @var Darryldecode\Cart\Cart
     */
    protected $cart;

    public function setUp()
    {
        $events = m::mock('Illuminate\Contracts\Events\Dispatcher');
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

    public function test_total_without_condition()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be the same as subtotal
        $this->assertEquals(187.49, $this->cart->getTotal(), 'Cart should have a total of 187.49');
    }

    public function test_total_with_condition()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $condition = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '12.5%',
        ));

        $this->cart->condition($condition);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->assertEquals(210.92625, $this->cart->getTotal(), 'Cart should have a total of 210.92625');
    }

    public function test_total_with_multiple_conditions_added_scenario_one()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
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

        $this->cart->condition($condition1);
        $this->cart->condition($condition2);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->assertEquals(225.92625, $this->cart->getTotal(), 'Cart should have a total of 225.92625');
    }

    public function test_total_with_multiple_conditions_added_scenario_two()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
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
            'value' => '-15',
        ));

        $this->cart->condition($condition1);
        $this->cart->condition($condition2);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->assertEquals(195.92625, $this->cart->getTotal(), 'Cart should have a total of 195.92625');
    }

    public function test_total_with_multiple_conditions_added_scenario_three()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $condition1 = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-12.5%',
        ));
        $condition2 = new CartCondition(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '-15',
        ));

        $this->cart->condition($condition1);
        $this->cart->condition($condition2);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->assertEquals(149.05375, $this->cart->getTotal(), 'Cart should have a total of 149.05375');
    }

    public function test_cart_multiple_conditions_can_be_added_once_by_array()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $condition1 = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-12.5%',
        ));
        $condition2 = new CartCondition(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '-15',
        ));

        $this->cart->condition([$condition1,$condition2]);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->assertEquals(149.05375, $this->cart->getTotal(), 'Cart should have a total of 149.05375');
    }

    public function test_total_with_multiple_conditions_added_scenario_four()
    {
        $this->fillCart();

        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // add condition
        $condition1 = new CartCondition(array(
            'name' => 'COUPON LESS 12.5%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-12.5%',
        ));
        $condition2 = new CartCondition(array(
            'name' => 'Express Shipping $15',
            'type' => 'shipping',
            'target' => 'total',
            'value' => '+15',
        ));

        $this->cart->condition($condition1);
        $this->cart->condition($condition2);

        // no changes in subtotal as the condition's target added was for total
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->assertEquals(179.05375, $this->cart->getTotal(), 'Cart should have a total of 179.05375');
    }

    public function test_add_item_with_condition()
    {
        $condition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-5%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => $condition1
        );

        $this->cart->add($item);

        $this->assertEquals(95, $this->cart->getSubTotal());
    }

    public function test_add_item_with_multiple_item_conditions_in_multiple_condition_instance()
    {
        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'subtotal',
            'value' => '-5%',
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

        $this->cart->add($item);

        $this->assertEquals(80.00, $this->cart->getSubTotal(), 'Cart subtotal with 1 item should be 70');
    }

    public function test_add_item_with_multiple_item_conditions_with_one_condition_wrong_target()
    {
        // NOTE:
        // $condition1 and $condition4 should not be included in calculation
        // as the target is not subtotal, remember that when adding
        // conditions in per-item bases, the condition's target should
        // have a value of subtotal

        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        )); // --> this should not be included in calculation
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
        $itemCondition4 = new CartCondition(array(
            'name' => 'MISC 2',
            'type' => 'misc2',
            'target' => 'total',
            'value' => '+10%',
        ));// --> this should not be included in calculation

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => [$itemCondition1, $itemCondition2, $itemCondition3, $itemCondition4]
        );

        $this->cart->add($item);

        $this->assertEquals(85.00, $this->cart->getSubTotal(), 'Cart subtotal with 1 item should be 70');
    }

    public function test_get_cart_condition_by_condition_name()
    {
        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$itemCondition1, $itemCondition2]);

        // get a condition applied on cart by condition name
        $condition = $this->cart->getCondition($itemCondition1->getName());

        $this->assertEquals($condition->getName(), 'SALE 5%');
        $this->assertEquals($condition->getTarget(), 'total');
        $this->assertEquals($condition->getType(), 'sale');
        $this->assertEquals($condition->getValue(), '-5%');
    }

    public function test_remove_cart_condition_by_condition_name()
    {
        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$itemCondition1, $itemCondition2]);

        // let's prove first we have now two conditions in the cart
        $this->assertEquals(2, $this->cart->getConditions()->count(), 'Cart should have two conditions');

        // now let's remove a specific condition by condition name
        $this->cart->removeCartCondition('SALE 5%');

        // cart should have now only 1 condition
        $this->assertEquals(1, $this->cart->getConditions()->count(), 'Cart should have one condition');
        $this->assertEquals('Item Gift Pack 25.00', $this->cart->getConditions()->first()->getName());
    }

    public function test_remove_item_condition_by_condition_name()
    {
        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'subtotal',
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'subtotal',
            'value' => '-25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => [$itemCondition1, $itemCondition2]
        );

        $this->cart->add($item);

        // let's very first the item has 2 conditions in it
        $this->assertCount(2,$this->cart->get(456)['conditions'], 'Item should have two conditions');

        // now let's remove a condition on that item using the condition name
        $this->cart->removeItemCondition(456, 'SALE 5%');

        // now we should have only 1 condition left on that item
        $this->assertCount(1,$this->cart->get(456)['conditions'], 'Item should have one condition left');
    }

    public function test_clear_cart_conditions()
    {
        // NOTE:
        // This only clears all conditions that has been added in a cart bases
        // this does not remove conditions on per item bases

        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$itemCondition1, $itemCondition2]);

        // let's prove first we have now two conditions in the cart
        $this->assertEquals(2, $this->cart->getConditions()->count(), 'Cart should have two conditions');

        // now let's clear cart conditions
        $this->cart->clearCartConditions();

        // cart should have now only 1 condition
        $this->assertEquals(0, $this->cart->getConditions()->count(), 'Cart should have no conditions now');
    }

    protected function fillCart()
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
    }
}