<?php

namespace Luminous\Bridge;

use DateTimeZone;
use RuntimeException;
use Carbon\Carbon;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Collection;
use Luminous\Bridge\Post\Builder as PostBuilder;
use Luminous\Bridge\Term\Builder as TermBuilder;

class WP
{
    /**
     * The option key for the time when posts were modified at.
     *
     * @var string
     */
    const OPTION_LAST_MODIFIED = 'luminous_last_modified';

    /**
     * The IoC container instance.
     *
     * @var \Illuminate\Contracts\Container\Container
     */
    protected static $container;

    /**
     * The map of methods to get a option value.
     *
     * @var array
     */
    protected static $optionMethods = [
        'last_modified' => 'lastModified',
        'timezone' => 'timezone',
    ];

    /**
     * The map of option aliases.
     *
     * @var array
     */
    protected static $optionAliases = [
        'url'           => 'home',
        'name'          => 'blogname',
        'description'   => 'blogdescription',
    ];

    /**
     * Get the value from the options database table.
     *
     * @uses \get_option()
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public static function option($key, $default = null)
    {
        if (array_key_exists($key, static::$optionMethods)) {
            return call_user_func([get_called_class(), static::$optionMethods[$key]]);
        }

        if (array_key_exists($key, static::$optionAliases)) {
            $key = static::$optionAliases[$key];
        }

        return get_option($key, $default);
    }

    /**
     * Get the time when the site was modified at.
     *
     * @return \Carbon\Carbon
     *
     * @throws \Exception
     */
    public static function lastModified()
    {
        static $value = null;

        if (is_null($value)) {
            if (! $timestamp = static::option(static::OPTION_LAST_MODIFIED)) {
                throw new RuntimeException("Option [".static::OPTION_LAST_MODIFIED."] could not be found.");
            }
            $value = Carbon::createFromTimeStamp((int) $timestamp, static::timezone());
        }

        return $value;
    }

    /**
     * Get the timezone for display.
     *
     * @return \DateTimeZone
     */
    public static function timezone()
    {
        static $value = null;
        return ! is_null($value) ? $value : ($value = new DateTimeZone(static::option('timezone_string')));
    }

    /**
     * Whether this site is public (`blog_public`).
     *
     * @return bool
     */
    public static function isPublic()
    {
        static $value = null;
        return ! is_null($value) ? $value : ($value = (bool) static::option('blog_public'));
    }

    /**
     * Get all post type collection.
     *
     * @uses \get_post_types()
     *
     * @return \Illuminate\Support\Collection|\Luminous\Bridge\Post\Type[]
     */
    public static function postTypes()
    {
        $types = array_merge(['page', 'post'], get_post_types(['public' => true, '_builtin' => false]));

        return new Collection(array_map([get_called_class(), 'postType'], $types));
    }

    /**
     * Get the post type instance.
     *
     * @param string|\Luminous\Bridge\Post\Type $name
     * @return \Luminous\Bridge\Post\Type
     */
    public static function postType($name)
    {
        return static::getPostBuilder()->getType($name);
    }

    /**
     * Get the post query instance.
     *
     * @param \Luminous\Bridge\Post\Type|string|array $type
     * @return \Luminous\Bridge\Post\Query\Builder
     */
    public static function posts($type = null)
    {
        $query = static::getPostBuilder()->query();

        return $type ? $query->type($type) : $query;
    }

    /**
     * Get the post entity instance.
     *
     * @param int|string|\WP_Post $id
     * @param \Luminous\Bridge\Post\Type|string $type
     * @return \Luminous\Bridge\Post\Entity
     */
    public static function post($id, $type = null)
    {
        return static::getPostBuilder()->get($id, $type);
    }

    /**
     * Get the term type (taxonomy) instance.
     *
     * @param string|\Luminous\Bridge\Term\Type $name
     * @return \Luminous\Bridge\Term\Type
     */
    public static function termType($name)
    {
        return static::getTermBuilder()->getType($name);
    }

    /**
     * Get the term entity instance.
     *
     * @param int|\stdClass $id
     * @param \Luminous\Bridge\Term\Type|string $type
     * @return \Luminous\Bridge\Term\Entity
     */
    public static function term($id, $type = null)
    {
        return static::getTermBuilder()->get($id, $type);
    }

    /**
     * Get the term query instance.
     *
     * @param \Luminous\Bridge\Term\Type|string $type
     * @return \Luminous\Bridge\Term\Query\Builder
     */
    public static function terms($type = null)
    {
        $query = static::getTermBuilder()->query();

        return $type ? $query->type($type) : $query;
    }

    /**
     * Set the container.
     *
     * @param \Illuminate\Contracts\Container\Container $container
     */
    public static function setContainer(Container $container)
    {
        static::$container = $container;
    }

    /**
     * Get the container.
     *
     * @return \Illuminate\Contracts\Container\Container $container
     */
    public static function getContainer()
    {
        return static::$container;
    }

    /**
     * Get the post builder.
     *
     * @return \Luminous\Post\Builder
     */
    protected static function getPostBuilder()
    {
        return static::$container->make(PostBuilder::class);
    }

    /**
     * Get the term builder.
     *
     * @return \Luminous\Term\Builder
     */
    protected static function getTermBuilder()
    {
        return static::$container->make(TermBuilder::class);
    }
}
