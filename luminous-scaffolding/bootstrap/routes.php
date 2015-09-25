<?php

// -----------------------------------------------------------------------------
// Utility Responces
// -----------------------------------------------------------------------------

$app->get('sitemap.xml', 'Controller@sitemap');
$app->get('robots.txt', 'Controller@robots');

// -----------------------------------------------------------------------------
// for Post
// -----------------------------------------------------------------------------

$app->group(['prefix' => 'posts', 'namespace' => 'Luminous\Http\Controllers'], function ($app) {
    $postType = 'post';
    $limit = 10;

    $app->any('{year:\d{4}}/{month:\d{2}}/{day:\d{2}}/{path}', [
        'query' => ['postType' => $postType],
        'uses' => 'Controller@show',
        'as' => 'post',
    ]);

    $app->any('{year:\d{4}}/{month:\d{2}}/{day:\d{2}}', [
        'query' => ['postType' => $postType, 'limit' => $limit],
        'uses' => 'Controller@archive',
        'as' => 'post:archive:daily',
    ]);

    $app->any('{year:\d{4}}/{month:\d{2}}', [
        'query' => ['postType' => $postType, 'limit' => $limit],
        'uses' => 'Controller@archive',
        'as' => 'post:archive:monthly',
    ]);

    $app->any('{year:\d{4}}', [
        'query' => ['postType' => $postType, 'limit' => $limit],
        'uses' => 'Controller@archive',
        'as' => 'post:archive:yearly',
    ]);

    $app->any('/', [
        'query' => ['postType' => $postType, 'limit' => $limit],
        'uses' => 'Controller@archive',
        'as' => 'post:archive',
    ]);

    $app->any('category/{path:.+}', [
        'query' => ['postType' => $postType, 'limit' => $limit, 'termType' => 'category'],
        'uses' => 'Controller@archive',
        'as' => 'category:archive',
    ]);

    $app->any('tag/{path}', [
        'query' => ['postType' => $postType, 'limit' => $limit, 'termType' => 'post_tag'],
        'uses' => 'Controller@archive',
        'as' => 'post_tag:archive',
    ]);
});

// -----------------------------------------------------------------------------
// for Page
// -----------------------------------------------------------------------------

$app->any('{path:.+}', [
    'query' => ['postType' => 'page'],
    'uses' => 'Controller@show',
    'as' => 'page',
]);

// -----------------------------------------------------------------------------
// for Home
// -----------------------------------------------------------------------------

$app->any('/', [
    'uses' => 'Controller@home',
    'as' => 'home',
]);