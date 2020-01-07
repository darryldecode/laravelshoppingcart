<?php
/**
 * Created by PhpStorm.
 * User: darryl
 * Date: 1/16/2015
 * Time: 1:45 PM
 */

use Darryldecode\Cart\Cart;
use Mockery as m;

require_once __DIR__.'/helpers/SessionMock.php';

class CartTestMultipleInstances extends PHPUnit\Framework\TestCase {

    /**
     * @var Darryldecode\Cart\Cart
     */
    protected $cart1;

    /**
     * @var Darryldecode\Cart\Cart
     */
    protected $cart2;

    public function setUp(): void
    {
        $events = m::mock('Illuminate\Contracts\Events\Dispatcher');
        $events->shouldReceive('dispatch');

        $this->cart1 = new Cart(
            new SessionMock(),
            $events,
            'shopping',
            'uniquesessionkey123',
            require(__DIR__.'/helpers/configMock.php')
        );

        $this->cart2 = new Cart(
            new SessionMock(),
            $events,
            'wishlist',
            'uniquesessionkey456',
            require(__DIR__.'/helpers/configMock.php')
        );
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function test_cart_multiple_instances()
    {
        // add 3 items on cart 1
        $itemsForCart1 = array(
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

        $this->cart1->add($itemsForCart1);

        $this->assertFalse($this->cart1->isEmpty(), 'Cart should not be empty');
        $this->assertCount(3, $this->cart1->getContent()->toArray(), 'Cart should have 3 items');
        $this->assertEquals('shopping', $this->cart1->getInstanceName(), 'Cart 1 should have instance name of "shopping"');

        // add 1 item on cart 2
        $itemsForCart2 = array(
            array(
                'id' => 456,
                'name' => 'Sample Item 1',
                'price' => 67.99,
                'quantity' => 4,
                'attributes' => array()
            ),
        );

        $this->cart2->add($itemsForCart2);

        $this->assertFalse($this->cart2->isEmpty(), 'Cart should not be empty');
        $this->assertCount(1, $this->cart2->getContent()->toArray(), 'Cart should have 3 items');
        $this->assertEquals('wishlist', $this->cart2->getInstanceName(), 'Cart 2 should have instance name of "wishlist"');
    }
}