<?php

namespace Luminous\Bridge\Term\Entities;

use stdClass;
use Illuminate\Support\Collection;
use Luminous\Bridge\WP;
use Luminous\Bridge\HasArchive;
use Luminous\Bridge\Entity as BaseEntity;
use Luminous\Bridge\Term\Type;

abstract class Entity extends BaseEntity implements HasArchive
{
    /**
     * The accessors map for original instance.
     *
     * @var array
     */
    protected $accessors = [
        'id' => 'term_id',
    ];

    /**
     * Create a new term entity instance.
     *
     * @param \Luminous\Bridge\Term\Type $type
     * @param \stdClass $original
     * @return void
     */
    public function __construct(Type $type, stdClass $original)
    {
        $this->type = $type;
        $this->original = $original;
        $this->accessorsForOriginal = $this->accessors;
    }

    /**
     * Get the ancestors.
     *
     * @uses \get_ancestors()
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getAncestorsAttribute()
    {
        if ($this->type->hierarchical) {
            $ancestors = get_ancestors($this->id, $this->type->name, 'taxonomy');
        } else {
            $ancestors = [];
        }

        return new Collection(array_map(function ($id) {
            return WP::term($id, $this->type->name);
        }, $ancestors));
    }

    /**
     * Get the path.
     *
     * @return string
     */
    protected function getPathAttribute()
    {
        $slugs = $this->ancestors->reverse()->pluck('slug')->push($this->slug);
        return implode('/', $slugs->all());
    }

    /**
     * Get the route prefix for archive of this instance.
     *
     * @return string
     */
    public function getRoutePrefix()
    {
        return $this->type->name;
    }

    /**
     * Wheter this instance has archive.
     *
     * @return bool
     */
    public function hasArchive()
    {
        return $this->type->public;
    }
}
