<?php

namespace Luminous\Bridge\Post;

use WP_Post;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Luminous\Bridge\WP;
use Luminous\Bridge\Entity as BaseEntity;

abstract class Entity extends BaseEntity
{
    const PAGING_SEPALATOR = '/\n?<!--nextpage-->\n?/';
    const TEASER_SEPALATOR = '/<!--more(.*?)?-->/';
    const NO_TEASER_FLAG   = '/<!--noteaser-->/';

    /**
     * The accessors map for original instance.
     *
     * @var array
     */
    protected $accessors = [
        'id'            => 'ID',
        'slug'          => 'post_name',
        'title'         => 'post_title',
        'raw_content'   => 'post_content',
        'raw_excerpt'   => 'post_excerpt',
        'status'        => 'post_status',
        'order'         => 'menu_order',
        'created_at'    => 'post_date',
        'modified_at'   => 'post_modified',
    ];

    /**
     * The array of paged content.
     *
     * @var array
     */
    protected $cachedPagedContent;

    /**
     * Create a new post entity instance.
     *
     * @param \Luminous\Bridge\WP $wp
     * @param \WP_Post $original
     * @param \Luminous\Bridge\Post\Type $type
     * @return void
     */
    public function __construct(WP $wp, WP_Post $original, Type $type)
    {
        parent::__construct($wp, $original, $type);
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
            return $this->wp->post($id);
        }, get_post_ancestors($this->original));

        return new Collection($ancestors);
    }

    /**
     * Get the path.
     *
     * @uses \get_page_uri()
     *
     * @return string
     */
    protected function getPathAttribute()
    {
        return get_page_uri($this->original);
    }

    /**
     * Get the content.
     *
     * @todo Support teaser & more link.
     * @todo Support 'noteaser' flag.
     * @todo Support preview.
     *
     * @link https://developer.wordpress.org/reference/functions/get_the_content/ get_the_content()
     * @link https://developer.wordpress.org/reference/functions/the_content/ the_content()
     *
     * @uses \apply_filters()
     *
     * @param int $page
     * @return string HTML
     */
    public function content($page = 0)
    {
        if ($page > 0) {
            $content = $this->pagedContent($page) ?: '';
        } else {
            $content = $this->raw_content;
        }

        $content = apply_filters('the_content', $content);
        $content = str_replace(']]>', ']]&gt;', $content);

        return $content;
    }

    /**
     * Get the paged content.
     *
     * @link https://developer.wordpress.org/reference/classes/wp_query/setup_postdata/ setup_postdata()
     *
     * @param null|int $page
     * @return array|string|null
     */
    protected function pagedContent($page = null)
    {
        if (is_null($this->cachedPagedContent)) {
            $this->cachedPagedContent = preg_split(static::PAGING_SEPALATOR, $this->raw_content);
        }

        if (is_null($page)) {
            return $this->cachedPagedContent;
        }

        return isset($this->cachedPagedContent[$index = $page - 1]) ? $this->cachedPagedContent[$index] : null;
    }

    /**
     * Get the content.
     *
     * @return string HTML
     */
    protected function getContentAttribute()
    {
        return $this->content();
    }

    /**
     * Get the number of paged content.
     *
     * @return int
     */
    protected function getPagesAttribute()
    {
        return count($this->pagedContent());
    }

    /**
     * Get the excerpt.
     *
     * @uses \strip_shortcodes()
     *
     * @param int $length
     * @return string
     */
    public function excerpt($length = 120)
    {
        $filter = function ($excerpt) {
            $excerpt = strip_shortcodes($excerpt);
            $excerpt = strip_tags($excerpt);
            $excerpt = preg_replace('/<!--[^>]*-->/', '', $excerpt);
            $excerpt = preg_replace('/[\s|\x{3000}]+/u', ' ', $excerpt);
            return trim($excerpt);
        };

        $excerpt = $filter($this->raw_excerpt);

        if ($excerpt === '') {
            $excerpt = $filter($this->raw_content);
        }

        if ($length && mb_strlen($excerpt) > $length) {
            $excerpt = mb_substr($excerpt, 0, $length - 1, 'utf8') . '…';
        }

        return $excerpt;
    }

    /**
     * Get the excerpt.
     *
     * @return string HTML
     */
    protected function getExcerptAttribute()
    {
        return $this->excerpt();
    }

    /**
     * Get the time when the post was created at.
     *
     * @param string $value
     * @return \Carbon\Carbon
     */
    protected function getCreatedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value, WP::timezone());
    }

    /**
     * Get the time when the post was modified at.
     *
     * @param string $value
     * @return \Carbon\Carbon
     */
    protected function getModifiedAtAttribute($value)
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $value, WP::timezone());
    }

    /**
     * The alias to `created_at`.
     *
     * @return \Carbon\Carbon
     */
    protected function getDateAttribute()
    {
        return $this->created_at;
    }

    /**
     * Get the formatted time.
     *
     * @param string $format
     * @param bool $modified
     * @return string
     */
    public function date($format, $modified = false)
    {
        return $this->{$modified ? 'modified_at' : 'created_at'}->format($format);
    }

    /**
     * Get a URL parameter.
     *
     * @param string $key
     * @return string
     */
    public function urlParameter($key)
    {
        $dateFormats = [
            'year'  => 'Y',
            'month' => 'm',
            'day'   => 'd',
        ];

        if (array_key_exists($key, $dateFormats)) {
            return $this->date($dateFormats[$key]);
        }

        return $this->{$key};
    }
}