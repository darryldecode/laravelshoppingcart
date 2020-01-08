<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 3/18/2015
 * Time: 6:17 PM
 */

use Darryldecode\Cart\Cart;
use Mockery as m;
use Darryldecode\Cart\CartCondition;
use Darryldecode\Tests\helpers\MockProduct;

require_once __DIR__ . '/helpers/SessionMock.php';

class ItemTest extends PHPUnit\Framework\TestCase
{

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
            require(__DIR__ . '/helpers/configMock.php')
        );
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function test_item_get_sum_price_using_property()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array());

        $item = $this->cart->get(455);

        $this->assertEquals(201.98, $item->getPriceSum(), 'Item summed price should be 201.98');
    }

    public function test_item_get_sum_price_using_array_style()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array());

        $item = $this->cart->get(455);

        $this->assertEquals(201.98, $item->getPriceSum(), 'Item summed price should be 201.98');
    }

    public function test_item_get_conditions_empty()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array());

        $item = $this->cart->get(455);

        $this->assertEmpty($item->getConditions(), 'Item should have no conditions');
    }

    public function test_item_get_conditions_with_conditions()
    {
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

        $this->cart->add(455, 'Sample Item', 100.99, 2, array(), [$itemCondition1, $itemCondition2]);

        $item = $this->cart->get(455);

        $this->assertCount(2, $item->getConditions(), 'Item should have two conditions');
    }

    public function test_item_associate_model()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array())->associate(MockProduct::class);

        $item = $this->cart->get(455);

        $this->assertEquals(MockProduct::class, $item->associatedModel, 'Item assocaited model should be ' . MockProduct::class);
    }

    public function test_it_will_throw_an_exception_when_a_non_existing_model_is_being_associated()
    {
        $this->expectException(\Darryldecode\Cart\Exceptions\UnknownModelException::class);
        $this->expectExceptionMessage('The supplied model SomeModel does not exist.');

        $this->cart->add(1, 'Test item', 1, 10.00)->associate('SomeModel');
    }

    public function test_item_get_model()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array())->associate(MockProduct::class);

        $item = $this->cart->get(455);

        $this->assertInstanceOf(MockProduct::class, $item->model);
        $this->assertEquals('Sample Item', $item->model->name);
        $this->assertEquals(455, $item->model->id);
    }

    public function test_item_get_model_will_return_null_if_it_has_no_model()
    {
        $this->cart->add(455, 'Sample Item', 100.99, 2, array());

        $item = $this->cart->get(455);

        $this->assertEquals(null, $item->model);
    }
}
