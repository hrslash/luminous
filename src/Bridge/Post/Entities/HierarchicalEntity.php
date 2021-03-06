<?php

namespace Luminous\Bridge\Post\Entities;

use Illuminate\Support\Collection;
use Luminous\Bridge\WP;
use Luminous\Bridge\Post\Entity as BaseEntity;

class HierarchicalEntity extends BaseEntity
{
    /**
     * {@inheritdoc}
     */
    public function isPublic()
    {
        if ($parent = $this->parent) {
            return $parent->isPublic() && parent::isPublic();
        }

        return parent::isPublic();
    }

    /**
     * Get the ancestors.
     *
     * @uses \get_post_ancestors()
     *
     * @return \Illuminate\Support\Collection|\Luminous\Bridge\Post\Entity[]
     */
    protected function getAncestorsAttribute()
    {
        $ancestors = array_map(function ($id) {
            return WP::post($id);
        }, get_post_ancestors($this->original));

        return new Collection($ancestors);
    }

    /**
     * Get the parent.
     *
     * @return \Luminous\Bridge\Post\Entity|null
     */
    protected function getParentAttribute()
    {
        return $this->ancestors->first();
    }

    /**
     * Get the children.
     *
     * @return \Luminous\Bridge\Post\Query\Builder
     */
    protected function getChildrenAttribute()
    {
        return WP::posts($this->type)->wherePost('parent_id', $this->id);
    }
}
