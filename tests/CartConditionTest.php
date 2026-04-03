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

class CartConditionTest extends PHPUnit\Framework\TestCase  {

    /**
     * @var Darryldecode\Cart\Cart
     */
    protected $cart;

    public function setUp(): void
    {
        $events = m::mock('Illuminate\Contracts\Events\Dispatcher');
        $events->shouldReceive('dispatch');

        $this->cart = new Cart(
            new SessionMock(),
            $events,
            'shopping',
            'SAMPLESESSIONKEY',
             require(__DIR__.'/helpers/configMock.php')
        );
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function test_subtotal()
    {
        $this->fillCart();

        // add condition to subtotal
        $condition = new CartCondition(array(
            'name' => 'VAT 12.5%',
            'type' => 'tax',
            'target' => 'subtotal',
            'value' => '-5',
        ));

        $this->cart->condition($condition);

        $this->assertEquals(182.49,$this->cart->getSubTotal());

        // the total is also should be the same with sub total since our getTotal
        // also depends on what is the value of subtotal
        $this->assertEquals(182.49,$this->cart->getTotal());
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
        $this->cart->setDecimals(5);
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

        // no changes in subtotal as the condition's target added was for subtotal
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
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

        // no changes in subtotal as the condition's target added was for subtotal
        $this->assertEquals(187.49, $this->cart->getSubTotal(), 'Cart should have sub total of 187.49');

        // total should be changed
        $this->cart->setDecimals(5);
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
        $this->cart->setDecimals(5);
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
        $this->cart->setDecimals(5);
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
        $this->cart->setDecimals(5);
        $this->assertEquals(179.05375, $this->cart->getTotal(), 'Cart should have a total of 179.05375');
    }

    public function test_add_item_with_condition()
    {
        $condition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'tax',
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

        $this->assertEquals(95, $this->cart->get(456)->getPriceSumWithConditions());
        $this->assertEquals(95, $this->cart->getSubTotal());
    }

    public function test_add_item_with_multiple_item_conditions_in_multiple_condition_instance()
    {
        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '-25',
        ));
        $itemCondition3 = new CartCondition(array(
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

        $this->cart->add($item);

        $this->assertEquals(80.00, $this->cart->get(456)->getPriceSumWithConditions(), 'Item subtotal with 1 item should be 80');
        $this->assertEquals(80.00, $this->cart->getSubTotal(), 'Cart subtotal with 1 item should be 80');
    }

    public function test_add_item_with_multiple_item_conditions_with_target_omitted()
    {
        // NOTE:
        // $condition1 and $condition4 should not be included in calculation
        // as the target is not for item, remember that when adding
        // conditions in per-item bases, the condition's target should
        // have a value of item

        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '-25',
        ));
        $itemCondition3 = new CartCondition(array(
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
            'conditions' => [$itemCondition2, $itemCondition3]
        );

        $this->cart->add($item);

        $this->assertEquals(85.00, $this->cart->get(456)->getPriceSumWithConditions(), 'Cart subtotal with 1 item should be 85');
        $this->assertEquals(85.00, $this->cart->getSubTotal(), 'Cart subtotal with 1 item should be 85');
    }

    public function test_add_item_condition()
    {
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'value' => '-25',
        ));
        $coupon101 = new CartCondition(array(
            'name' => 'COUPON 101',
            'type' => 'coupon',
            'value' => '-5%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => [$itemCondition2]
        );

        $this->cart->add($item);

        // let's prove first we have 1 condition on this item
        $this->assertCount(1, $this->cart->get($item['id'])['conditions'], "Item should have 1 condition");

        // now let's insert a condition on an existing item on the cart
        $this->cart->addItemCondition($item['id'], $coupon101);

        $this->assertCount(2, $this->cart->get($item['id'])['conditions'], "Item should have 2 conditions");
    }

    public function test_add_item_condition_restrict_negative_price()
    {
        $condition = new CartCondition([
            'name' => 'Substract amount but prevent negative value',
            'type' => 'promo',
            'value' => '-25',
        ]);

        $item = [
            'id' => 789,
            'name' => 'Sample Item 1',
            'price' => 20,
            'quantity' => 1,
            'attributes' => [],
            'conditions' => [
                $condition,
            ]
        ];

        $this->cart->add($item);

        // Since the product price is 20 and the condition reduces it by 25,
        // check that the item's price has been prevented from dropping below zero.
        $this->assertEquals(0.00, $this->cart->get($item['id'])->getPriceSumWithConditions(), "The item's price should be prevented from going below zero.");
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
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
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

    public function test_remove_item_condition_by_condition_name_scenario_two()
    {
        // NOTE: in this scenario, we will add the conditions not in array format

        $itemCondition = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '-5%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
            'conditions' => $itemCondition // <--not in array format
        );

        $this->cart->add($item);

        // let's very first the item has 2 conditions in it
        $this->assertNotEmpty($this->cart->get(456)['conditions'], 'Item should have one condition in it.');

        // now let's remove a condition on that item using the condition name
        $this->cart->removeItemCondition(456, 'SALE 5%');

        // now we should have only 1 condition left on that item
        $this->assertEmpty($this->cart->get(456)['conditions'], 'Item should have no condition now');
    }

    public function test_clear_item_conditions()
    {
        $itemCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'value' => '-5%',
        ));
        $itemCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
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
        $this->assertCount(2, $this->cart->get(456)['conditions'], 'Item should have two conditions');

        // now let's remove all condition on that item
        $this->cart->clearItemConditions(456);

        // now we should have only 0 condition left on that item
        $this->assertCount(0, $this->cart->get(456)['conditions'], 'Item should have no conditions now');
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

    public function test_get_calculated_value_of_a_condition()
    {
        $cartCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        ));
        $cartCondition2 = new CartCondition(array(
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

        $this->cart->condition([$cartCondition1, $cartCondition2]);

        $subTotal = $this->cart->getSubTotal();

        $this->assertEquals(100, $subTotal, 'Subtotal should be 100');

        // way 1
        // now we will get the calculated value of the condition 1
        $cond1 = $this->cart->getCondition('SALE 5%');
        $this->assertEquals(5,$cond1->getCalculatedValue($subTotal), 'The calculated value must be 5');

        // way 2
        // get all cart conditions and get their calculated values
        $conditions = $this->cart->getConditions();
        $this->assertEquals(5, $conditions['SALE 5%']->getCalculatedValue($subTotal),'First condition calculated value must be 5');
        $this->assertEquals(25, $conditions['Item Gift Pack 25.00']->getCalculatedValue($subTotal),'First condition calculated value must be 5');
    }

    public function test_get_conditions_by_type()
    {
        $cartCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        ));
        $cartCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 25.00',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
        ));
        $cartCondition3 = new CartCondition(array(
            'name' => 'Item Less 8%',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-8%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$cartCondition1, $cartCondition2, $cartCondition3]);

        // now lets get all conditions added in the cart with the type "promo"
        $promoConditions = $this->cart->getConditionsByType('promo');

        $this->assertEquals(2, $promoConditions->count(), "We should have 2 items as promo condition type.");
    }

    public function test_remove_conditions_by_type()
    {
        // NOTE:
        // when add a new condition, the condition's name will be the key to be use
        // to access the condition. For some reasons, if the condition name contains
        // a "dot" on it ("."), for example adding a condition with name "SALE 35.00"
        // this will cause issues when removing this condition by name, this will not be removed
        // so when adding a condition, the condition name should not contain any "period" (.)
        // to avoid any issues removing it using remove method: removeCartCondition($conditionName);

        $cartCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
        ));
        $cartCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 20',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
        ));
        $cartCondition3 = new CartCondition(array(
            'name' => 'Item Less 8%',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-8%',
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$cartCondition1, $cartCondition2, $cartCondition3]);

        // now lets remove all conditions added in the cart with the type "promo"
        $this->cart->removeConditionsByType('promo');

        $this->assertEquals(1, $this->cart->getConditions()->count(), "We should have 1 condition remaining as promo conditions type has been removed.");
    }

    public function test_add_cart_condition_without_condition_attributes()
    {
        $cartCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%'
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$cartCondition1]);

        // prove first we have now the condition on the cart
        $contition = $this->cart->getCondition("SALE 5%");
        $this->assertEquals('SALE 5%',$contition->getName());

        // when get attribute is called and there is no attributes added,
        // it should return an empty array
        $conditionAttribute = $contition->getAttributes();
        $this->assertIsArray($conditionAttribute);
    }

    public function test_add_cart_condition_with_condition_attributes()
    {
        $cartCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
            'attributes' => array(
                'description' => 'october fest promo sale',
                'sale_start_date' => '2015-01-20',
                'sale_end_date' => '2015-01-30',
            )
        ));

        $item = array(
            'id' => 456,
            'name' => 'Sample Item 1',
            'price' => 100,
            'quantity' => 1,
            'attributes' => array(),
        );

        $this->cart->add($item);

        $this->cart->condition([$cartCondition1]);

        // prove first we have now the condition on the cart
        $contition = $this->cart->getCondition("SALE 5%");
        $this->assertEquals('SALE 5%',$contition->getName());

        // when get attribute is called and there is no attributes added,
        // it should return an empty array
        $conditionAttributes = $contition->getAttributes();
        $this->assertIsArray($conditionAttributes);
        $this->assertArrayHasKey('description',$conditionAttributes);
        $this->assertArrayHasKey('sale_start_date',$conditionAttributes);
        $this->assertArrayHasKey('sale_end_date',$conditionAttributes);
        $this->assertEquals('october fest promo sale',$conditionAttributes['description']);
        $this->assertEquals('2015-01-20',$conditionAttributes['sale_start_date']);
        $this->assertEquals('2015-01-30',$conditionAttributes['sale_end_date']);
    }

    public function test_get_order_from_condition()
    {
        $cartCondition1 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
            'order' => 2
        ));
        $cartCondition2 = new CartCondition(array(
            'name' => 'Item Gift Pack 20',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
            'order' => '3'
        ));
        $cartCondition3 = new CartCondition(array(
            'name' => 'Item Less 8%',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-8%',
            'order' => 'first'
        ));

        $this->assertEquals(2, $cartCondition1->getOrder());
        $this->assertEquals(3, $cartCondition2->getOrder()); // numeric string is converted to integer
        $this->assertEquals(0, $cartCondition3->getOrder()); // no numeric string is converted to 0

        $this->cart->condition($cartCondition1);
        $this->cart->condition($cartCondition2);
        $this->cart->condition($cartCondition3);

        $conditions = $this->cart->getConditions();

        $this->assertEquals('sale', $conditions->shift()->getType());
        $this->assertEquals('promo', $conditions->shift()->getType());
        $this->assertEquals('tax', $conditions->shift()->getType());
    }

    public function test_condition_ordering()
    {
        $cartCondition1 = new CartCondition(array(
            'name' => 'TAX',
            'type' => 'tax',
            'target' => 'total',
            'value' => '-8%',
            'order' => 5
        ));
        $cartCondition2 = new CartCondition(array(
            'name' => 'SALE 5%',
            'type' => 'sale',
            'target' => 'total',
            'value' => '-5%',
            'order' => 2
        ));
        $cartCondition3 = new CartCondition(array(
            'name' => 'Item Gift Pack 20',
            'type' => 'promo',
            'target' => 'total',
            'value' => '-25',
            'order' => 1
        ));

        $this->fillCart();

        $this->cart->condition($cartCondition1);
        $this->cart->condition($cartCondition2);
        $this->cart->condition($cartCondition3);

        $this->assertEquals('Item Gift Pack 20',$this->cart->getConditions()->first()->getName());
        $this->assertEquals('TAX',$this->cart->getConditions()->last()->getName());
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
