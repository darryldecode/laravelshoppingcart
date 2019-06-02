<?php
namespace Darryldecode\Tests\helpers;

use Illuminate\Contracts\Database\ModelIdentifier;

class MockProduct extends ModelIdentifier
{
    /**
     * @var int|string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var float
     */
    public $price;

    /**
     * @var float
     */
    public $weight;

    /**
     * Product constructor.
     *
     * @param int|string $id
     * @param string     $name
     * @param float      $price
     */
    public function __construct($id = 455, $name = 'Sample Item', $price = 100.99, $weight = 0)
    {
        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->weight = $weight;
    }

    /**
     * Get the identifier of the  item.
     *
     * @return int|string
     */
    public function getIdentifier($options = null)
    {
        return $this->id;
    }

    /**
     * Get the description or title of the  item.
     *
     * @return string
     */
    public function getDescription($options = null)
    {
        return $this->name;
    }

    /**
     * Get the price of the  item.
     *
     * @return float
     */
    public function getPrice($options = null)
    {
        return $this->price;
    }

    /**
     * Get the price of the  item.
     *
     * @return float
     */
    public function getWeight($options = null)
    {
        return $this->weight;
    }

    public function find($id)
    {
        return $this;
    }
}
