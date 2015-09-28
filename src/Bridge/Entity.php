<?php

namespace Luminous\Bridge;

use ArrayAccess;
use InvalidArgumentException;
use Illuminate\Contracts\Routing\UrlRoutable;

abstract class Entity implements ArrayAccess, UrlRoutable
{
    use DecoratorTrait;

    /**
     * The accessors map for original instance.
     *
     * @var array
     */
    protected $accessors = [];

    /**
     * The type.
     *
     * @var \Luminous\Bridge\Type
     */
    public $type;

    /**
     * Create a new entity instance.
     *
     * @param object $original
     * @param \Luminous\Bridge\Type $type
     * @return void
     */
    public function __construct($original, Type $type)
    {
        $this->original = $original;
        $this->accessorsForOriginal = $this->accessors;
        $this->type = $type;
    }

    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->{$this->getRouteKeyName()};
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKeyName()
    {
        return 'path';
    }
}