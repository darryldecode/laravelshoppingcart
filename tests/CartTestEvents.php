<?php

use Ozanmuyes\Cart\Cart;
use Mockery as m;

require_once __DIR__ . '/helpers/SessionMock.php';

class CartTestEvents extends PHPUnit_Framework_TestCase {

    const CART_INSTANCE_NAME = 'shopping';

    public function setUp()
    {
    }

    public function tearDown()
    {
        m::close();
    }

    public function test_event_cart_created()
    {
        $events = m::mock('Illuminate\Contracts\Events\Dispatcher');
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.created', m::type('array'));

        $cart = new Cart(
            new SessionMock(),
            $events,
            self::CART_INSTANCE_NAME,
            'SAMPLESESSIONKEY'
        );
    }

    public function test_event_cart_adding()
    {
        $events = m::mock('Illuminate\Events\Dispatcher');
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.created', m::type('array'));
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.adding', m::type('array'));
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.added', m::type('array'));

        $cart = new Cart(
            new SessionMock(),
            $events,
            self::CART_INSTANCE_NAME,
            'SAMPLESESSIONKEY'
        );

        $cart->add(455, 'Sample Item', 100.99, 2, array());
    }

    public function test_event_cart_adding_multiple_times()
    {
        $events = m::mock('Illuminate\Events\Dispatcher');
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.created', m::type('array'));
        $events->shouldReceive('fire')->times(2)->with(self::CART_INSTANCE_NAME.'.adding', m::type('array'));
        $events->shouldReceive('fire')->times(2)->with(self::CART_INSTANCE_NAME.'.added', m::type('array'));

        $cart = new Cart(
            new SessionMock(),
            $events,
            self::CART_INSTANCE_NAME,
            'SAMPLESESSIONKEY'
        );

        $cart->add(455, 'Sample Item 1', 100.99, 2, array());
        $cart->add(562, 'Sample Item 2', 100.99, 2, array());
    }

    public function test_event_cart_adding_multiple_times_scenario_two()
    {
        $events = m::mock('Illuminate\Events\Dispatcher');
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.created', m::type('array'));
        $events->shouldReceive('fire')->times(3)->with(self::CART_INSTANCE_NAME.'.adding', m::type('array'));
        $events->shouldReceive('fire')->times(3)->with(self::CART_INSTANCE_NAME.'.added', m::type('array'));

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

        $cart = new Cart(
            new SessionMock(),
            $events,
            self::CART_INSTANCE_NAME,
            'SAMPLESESSIONKEY'
        );

        $cart->add($items);
    }

    public function test_event_cart_remove_item()
    {
        $events = m::mock('Illuminate\Events\Dispatcher');
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.created', m::type('array'));
        $events->shouldReceive('fire')->times(3)->with(self::CART_INSTANCE_NAME.'.adding', m::type('array'));
        $events->shouldReceive('fire')->times(3)->with(self::CART_INSTANCE_NAME.'.added', m::type('array'));
        $events->shouldReceive('fire')->times(1)->with(self::CART_INSTANCE_NAME.'.removing', m::type('array'));
        $events->shouldReceive('fire')->times(1)->with(self::CART_INSTANCE_NAME.'.removed', m::type('array'));

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

        $cart = new Cart(
            new SessionMock(),
            $events,
            self::CART_INSTANCE_NAME,
            'SAMPLESESSIONKEY'
        );

        $cart->add($items);

        $cart->remove(456);
    }

    public function test_event_cart_clear()
    {
        $events = m::mock('Illuminate\Events\Dispatcher');
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.created', m::type('array'));
        $events->shouldReceive('fire')->times(3)->with(self::CART_INSTANCE_NAME.'.adding', m::type('array'));
        $events->shouldReceive('fire')->times(3)->with(self::CART_INSTANCE_NAME.'.added', m::type('array'));
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.clearing', m::type('array'));
        $events->shouldReceive('fire')->once()->with(self::CART_INSTANCE_NAME.'.cleared', m::type('array'));

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

        $cart = new Cart(
            new SessionMock(),
            $events,
            self::CART_INSTANCE_NAME,
            'SAMPLESESSIONKEY'
        );

        $cart->add($items);

        $cart->clear();
    }
}