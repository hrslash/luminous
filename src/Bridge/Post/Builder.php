<?php

namespace Luminous\Bridge\Post;

use Luminous\Bridge\Builder as BaseBuilder;
use Luminous\Bridge\WP;
use Luminous\Bridge\Exceptions\MissingEntityException;
use Luminous\Bridge\Post\Query\Builder as QueryBuilder;

/**
 * @method \Luminous\Bridge\Post\Entity get(int|\WP_Post $id) Get an entity instance.
 * @method \Luminous\Bridge\Post\Type getType(string $name) Get a type instance.
 */
class Builder extends BaseBuilder
{
    /**
     * Get an original object.
     *
     * @uses \get_post()
     * @uses \get_page_by_path()
     * @uses \OBJECT
     *
     * @param int|string|\WP_Post $id
     * @param \Luminous\Bridge\Post\Type|string $type
     * @return \WP_Post|null
     */
    protected function getOriginal($id, $type = null)
    {
        if ($type instanceof Type) {
            $type = $type->name;
        }

        if (is_string($id)) {
            return get_page_by_path($id, \OBJECT, $type ?: 'page');
        }

        // WordPress uses global $post when $id is null.
        return $id && ($original = get_post($id)) ? $original : null;
    }

    /**
     * Hydrate an original object.
     *
     * @param \WP_Post $original
     * @return \Luminous\Bridge\Post\Entity
     *
     * @throws \Luminous\Bridge\Exceptions\MissingEntityException
     */
    public function make($original)
    {
        $type = $this->getType($original->post_type);

        if (! $this->container->bound($abstract = "wp.post.entities.{$type->name}")) {
            $abstract = $type->hierarchical
                ? 'Luminous\Bridge\Post\Entities\HierarchicalEntity'
                : 'Luminous\Bridge\Post\Entities\NonHierarchicalEntity';

            if (! $this->container->bound($abstract)) {
                throw new MissingEntityException($abstract);
            }
        }

        return $this->container->make($abstract, [$type, $original]);
    }

    /**
     * Get an original type object.
     *
     * @uses \get_post_type_object()
     *
     * @param string $name
     * @return \stdClass|null
     */
    protected function getOriginalType($name)
    {
        return get_post_type_object($name) ?: null;
    }

    /**
     * Hydrate an original type object.
     *
     * @param \stdClass $original
     * @return \Luminous\Bridge\Post\Type
     */
    protected function makeType($original)
    {
        return new Type($original);
    }

    /**
     * Create a new query instance.
     *
     * @return \Luminous\Bridge\Post\Query\Builder
     */
    public function query()
    {
        return new QueryBuilder($this);
    }
}
