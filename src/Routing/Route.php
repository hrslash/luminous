<?php

namespace Luminous\Routing;

use Closure;
use Illuminate\Support\Arr;
use Luminous\Bridge\UrlResource;

class Route
{
    /**
     * The HTTP verbs.
     *
     * @var array
     */
    protected $methods = [];

    /**
     * The URI.
     *
     * @var string
     */
    protected $uri;

    /**
     * The middleware.
     *
     * @var array
     */
    protected $middleware = [];

    /**
     * The controller.
     *
     * @var string|\Luminous\Routing\Controller
     */
    protected $controller;

    /**
     * The controller method.
     *
     * @var string
     */
    protected $controllerMethod;

    /**
     * The parameters.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Create a new route instance.
     *
     * @param array|string $methods
     * @param string $uri
     * @param array|string|\Closure $options
     * @return void
     */
    public function __construct($methods, $uri, $options)
    {
        $this->methods = array_map('strtoupper', (array) $methods);
        $this->uri = $uri;

        if (is_string($options)) {
            $options = ['uses' => $options];
        } elseif (! is_array($options)) {
            $options = [$options];
        }

        foreach ($options as $value) {
            if ($value instanceof Closure) {
                $this->controller = new ClosureController($value);
                $this->controllerMethod = 'render';
                break;
            }
        }

        if (! $this->controller && $uses = Arr::get($options, 'uses')) {
            list($this->controller, $this->controllerMethod) = explode('@', $uses);
        }

        if ($middleware = Arr::get($options, 'middleware')) {
            $this->middleware = is_string($middleware) ? explode('|', $middleware) : (array) $middleware;
        }

        if ($this->parameters = Arr::get($options, 'parameters', [])) {
            foreach ($this->parameters as $key => $value) {
                if (is_string($value) && strpos($value, '{') !== false) {
                    $replacement = preg_replace('/\{([a-z][a-z\d_]*)/i', "{{$key}__$1", $value);
                    $this->uri = preg_replace("/\{{$key}\}/", $replacement, $this->uri);
                }
            }
        }
    }

    /**
     * Get the methods.
     *
     * @return array
     */
    public function getMethods()
    {
        return $this->methods;
    }

    /**
     * Get the URI.
     *
     * @param array $parameters
     * @return string
     */
    public function getUri(array $parameters = [])
    {
        $uri = $this->uri;

        if (! $parameters) {
            return $uri;
        }

        $pattern = '~'.\FastRoute\RouteParser\Std::VARIABLE_REGEX.'~x';
        $result = '';

        foreach (explode('[', rtrim($uri, ']')) as $path) {
            $replaced = preg_replace_callback($pattern, function ($m) use ($parameters) {
                if ($value = Arr::get($parameters, $key = $m[1])) {
                    return $value;
                } elseif (preg_match('/^([^_]+)__([^_].*)$/', $key, $s) &&
                    ($object = Arr::get($parameters, $s[1])) && ($object instanceof UrlResource)
                ) {
                    return $object->urlPath($s[2]);
                } else {
                    return '__FAIL__';
                }
            }, $path);

            if (strpos($replaced, '__FAIL__') !== false) {
                break;
            }

            $result .= $replaced;
        }

        return $result;
    }

    /**
     * Get the middleware.
     *
     * @return array
     */
    public function getMiddleware()
    {
        return $this->middleware;
    }

    /**
     * Get the controller.
     *
     * @return string|\Luminous\Routing\Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Get the controller method.
     *
     * @return string
     */
    public function getControllerMethod()
    {
        return $this->controllerMethod;
    }

    /**
     * Set the incoming parameters.
     *
     * @param array $parameters
     * @return void
     */
    public function setParameters(array $parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);
    }

    /**
     * Get the parameter.
     *
     * @param string $key
     * @return mixed
     */
    public function parameter($key)
    {
        return Arr::get($this->parameters, $key);
    }
}
