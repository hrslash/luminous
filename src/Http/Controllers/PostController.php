<?php

namespace Luminous\Http\Controllers;

use Illuminate\Http\Request;
use Luminous\Bridge\Exceptions\EntityNotFoundException;
use Luminous\Bridge\Post\Type;
use Luminous\Bridge\Post\Entity;
use Luminous\Http\Queries\PostsQuery;
use Luminous\Http\Queries\PostQuery;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PostController extends Controller
{
    /**
     * Handle requests to posts.
     *
     * @return \Illuminate\View\View
     */
    public function posts()
    {
        return $this->getPostsView($this->getPostsQuery());
    }

    /**
     * Get the query for posts.
     *
     * @return \Luminous\Http\Queries\PostsQuery
     */
    protected function getPostsQuery()
    {
        return $this->app->make(PostsQuery::class);
    }

    /**
     * Get the view for posts.
     *
     * @param \Luminous\Http\Queries\PostsQuery $query
     * @return \Illuminate\View\View
     */
    protected function getPostsView(PostsQuery $query)
    {
        $postType = $query->postType;

        return $this->findView($postType, ['posts', 'base'])->with(compact('query'));
    }

    /**
     * Handle requests to the post.
     *
     * @return \Illuminate\View\View
     */
    public function post()
    {
        return $this->getPostView($this->getPostQuery());
    }

    /**
     * Get the query for the post.
     *
     * @return \Luminous\Http\Queries\PostQuery
     */
    protected function getPostQuery()
    {
        return $this->app->make(PostQuery::class);
    }

    /**
     * Get the view for the post.
     *
     * @param \Luminous\Http\Queries\PostsQuery $query
     * @return \Illuminate\View\View
     */
    protected function getPostView(PostQuery $query)
    {
        $postType = $query->postType;
        $post = $query->post;

        $files = [];

        if ($post && $postType->hierarchical) {
            $files[] = implode('.', $paths = explode('/', $post->path)).'.index';
            while ($paths) {
                $file = implode('.', $paths);
                array_push($files, "{$file}.base", $file);
                array_pop($paths);
            }
        } else {
            $files[] = 'post';
        }

        $files[] = 'base';

        return $this->findView($postType, $files)->with(compact('query'));
    }

    /**
     * Find the view.
     *
     * @param \Luminous\Bridge\Post\Type $postType
     * @param array $files
     * @return \Illuminate\View\View
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function findView(Type $postType, array $files)
    {
        $factory = view();

        foreach ($files as $file) {
            if ($factory->exists($name = "{$postType->name}.{$file}")) {
                return $factory->make($name);
            }
        }

        throw new NotFoundHttpException;
    }
}
