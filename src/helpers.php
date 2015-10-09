<?php

use Illuminate\Support\Str;
use Illuminate\Container\Container;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

// Application =================================================================

if (! function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param string $make
     * @param array $parameters
     * @return mixed|\Luminous\Application
     */
    function app($make = null, $parameters = [])
    {
        if (is_null($make)) {
            return Container::getInstance();
        }

        return Container::getInstance()->make($make, $parameters);
    }
}

if (! function_exists('base_path')) {
    /**
     * Get the path to the base of the install.
     *
     * @uses \app()
     *
     * @param string $path
     * @return string
     */
    function base_path($path = '')
    {
        return app()->basePath($path);
    }
}

if (! function_exists('framework_base_path')) {
    /**
     * Get the path to the Luminous framework directory.
     *
     * @uses \app()
     *
     * @param string $path
     * @return string
     */
    function framework_base_path($path = '')
    {
        return app()->frameworkBasePath($path);
    }
}

if (! function_exists('storage_path')) {
    /**
     * Get the path to the storage folder.
     *
     * @uses \app()
     *
     * @param string $path
     * @return string
     */
    function storage_path($path = '')
    {
        return app()->storagePath($path);
    }
}

// Utilities ===================================================================

if (! function_exists('is_wp')) {
    /**
     * Determine if the request should be handled by WordPress.
     *
     * @uses $pagenow
     * @uses \WP_INSTALLING
     * @uses \is_admin()
     *
     * @return bool
     */
    function is_wp()
    {
        global $pagenow;

        $scripts = [
            'wp-activate.php',
            'wp-comments-post.php',
            'wp-cron.php',
            'wp-links-opml.php',
            'wp-login.php',
            'wp-mail.php',
            'wp-signup.php',
            'wp-trackback.php',
            'xmlrpc.php',
        ];

        return defined('WP_INSTALLING') || in_array($pagenow, $scripts) || is_admin();
    }
}

if (! function_exists('config')) {
    /**
     * Get / set the specified configuration value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @uses \app()
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed
     */
    function config($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('config');
        }

        if (is_array($key)) {
            return app('config')->set($key);
        }

        return app('config')->get($key, $default);
    }
}

if (! function_exists('env')) {
    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @uses \value()
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return value($default);
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if (Str::startsWith($value, '"') && Str::endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (! function_exists('info')) {
    /**
     * Write some information to the log.
     *
     * @uses \app()
     *
     * @param string $message
     * @param array $context
     * @return void
     */
    function info($message, $context = [])
    {
        return app('Psr\Log\LoggerInterface')->info($message, $context);
    }
}

if (! function_exists('trans')) {
    /**
     * Translate the given message.
     *
     * @uses \app()
     *
     * @param string $id
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
    function trans($id = null, $parameters = [], $domain = 'messages', $locale = null)
    {
        if (is_null($id)) {
            return app('translator');
        }

        return app('translator')->trans($id, $parameters, $domain, $locale);
    }
}

if (! function_exists('trans_choice')) {
    /**
     * Translates the given message based on a count.
     *
     * @uses \app()
     *
     * @param string $id
     * @param int $number
     * @param array $parameters
     * @param string $domain
     * @param string $locale
     * @return string
     */
    function trans_choice($id, $number, array $parameters = [], $domain = 'messages', $locale = null)
    {
        return app('translator')->transChoice($id, $number, $parameters, $domain, $locale);
    }
}

if (! function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @uses \app()
     *
     * @param string $view
     * @param array $data
     * @param array $mergeData
     * @return \Illuminate\View\View
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app('view');

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

// HTTP ========================================================================

if (! function_exists('abort')) {
    /**
     * Throw an HttpException with the given data.
     *
     * @uses \app()
     *
     * @param int $code
     * @param string $message
     * @param array $headers
     * @return void
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    function abort($code, $message = '', array $headers = [])
    {
        if ($code == 404) {
            throw new NotFoundHttpException($message);
        }

        throw new HttpException($code, $message, null, $headers);
    }
}

if (! function_exists('redirect')) {
    /**
     * Get an instance of the redirector.
     *
     * @uses \app()
     *
     * @param string|null $to
     * @param int $status
     * @param array $headers
     * @param bool $secure
     * @return \Luminous\Http\Redirector|\Luminous\Http\RedirectResponse
     */
    function redirect($to = null, $status = 302, $headers = [], $secure = null)
    {
        $redirector = new Luminous\Http\Redirector(app());

        if (is_null($to)) {
            return $redirector;
        }

        return $redirector->to($to, $status, $headers, $secure);
    }
}

if (! function_exists('response')) {
    /**
     * Return a new response from the application.
     *
     * @uses \app()
     *
     * @param string $content
     * @param int $status
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\Response|\Luminous\Http\ResponseFactory
     */
    function response($content = '', $status = 200, array $headers = [])
    {
        $factory = new Luminous\Http\ResponseFactory(app());

        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($content, $status, $headers);
    }
}

if (! function_exists('cookie')) {
    /**
     * Create a new cookie instance.
     *
     * @uses \app()
     *
     * @param string $name
     * @param string $value
     * @param int $minutes
     * @param string $path
     * @param string $domain
     * @param bool $secure
     * @param bool $httpOnly
     * @return \Symfony\Component\HttpFoundation\Cookie
     */
    function cookie(
        $name = null,
        $value = null,
        $minutes = 0,
        $path = null,
        $domain = null,
        $secure = false,
        $httpOnly = true
    ) {
        $cookie = app('cookie');

        if (is_null($name)) {
            return $cookie;
        }

        return $cookie->make($name, $value, $minutes, $path, $domain, $secure, $httpOnly);
    }
}

if (! function_exists('session')) {
    /**
     * Get / set the specified session value.
     *
     * If an array is passed as the key, we will assume you want to set an array of values.
     *
     * @uses \app()
     *
     * @param array|string $key
     * @param mixed $default
     * @return mixed
     */
    function session($key = null, $default = null)
    {
        if (is_null($key)) {
            return app('session');
        }
        if (is_array($key)) {
            return app('session')->put($key);
        }

        return app('session')->get($key, $default);
    }
}

if (! function_exists('csrf_token')) {
    /**
     * Get the CSRF token value.
     *
     * @return string
     *
     * @uses \app()
     *
     * @throws RuntimeException
     */
    function csrf_token()
    {
        $session = app('session');
        if (isset($session)) {
            return $session->getToken();
        }
        throw new RuntimeException('Application session store not set.');
    }
}

if (! function_exists('old')) {
    /**
     * Retrieve an old input item.
     *
     * @uses \app()
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function old($key = null, $default = null)
    {
        return app('request')->old($key, $default);
    }
}

// URL Helpers =================================================================

if (! function_exists('url')) {
    /**
     * Get the URL.
     *
     * @uses \app()
     *
     * @param array|string|mixed $options
     * @param bool $full
     * @return string
     */
    function url($options = '', $full = false)
    {
        return app('router')->url($options, $full);
    }
}

if (! function_exists('posts_url')) {
    /**
     * Generate a URL to posts.
     *
     * @uses \url()
     *
     * @param string|\Luminous\Bridge\Post\Type $type
     * @param array|bool|mixed $options
     * @param bool $full
     * @return string
     */
    function posts_url($type, $options = [], $full = false)
    {
        if (is_bool($options)) {
            list($options, $full) = [[], $options];
        } elseif (! is_array($options)) {
            $options = [$options];
        }

        $options['post_type'] = $type;

        return url($options, $full);
    }
}

if (! function_exists('post_url')) {
    /**
     * Generate a URL to the post.
     *
     * @uses \url()
     *
     * @param \Luminous\Bridge\Post\Entity|int|string $post
     * @param array|bool $options
     * @param bool $full
     * @return string
     */
    function post_url($post, $options = [], $full = false)
    {
        if (!($post instanceof Luminous\Bridge\Post\Entity)) {
            $post = app('wp')->post($post, isset($options['post_type']) ? $options['post_type'] : null);
        }

        if (is_bool($options)) {
            list($options, $full) = [[], $options];
        }

        $options['post'] = $post;

        return url($options, $full);
    }
}

if (! function_exists('asset')) {
    /**
     * Get the path to a versioned file.
     *
     * @uses \config()
     * @uses \url()
     *
     * @param string $file
     * @param array|bool $options
     * @param bool $full
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    function asset($file, $options = [], $full = false)
    {
        static $manifest = null;
        static $prefix = null;

        if (is_null($manifest)) {
            $manifestPath = config('assets.manifest');
            $manifest = json_decode(file_get_contents($manifestPath), true);
            $prefix = trim(config('assets.prefix'), '/').'/';
        }

        if (is_bool($options)) {
            list($options, $full) = [[], $options];
        }

        if (isset($manifest[$file])) {
            $options['path'] = $prefix.$manifest[$file];
            return url($options, $full);
        }

        throw new InvalidArgumentException("File {$file} not defined in asset manifest.");
    }
}
