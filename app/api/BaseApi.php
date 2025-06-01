<?php

namespace HooshinaAi\App\Api;

use HooshinaAi\App\Api\Middleware\ApiKeyMiddleware;

class BaseApi 
{
    protected $namespace = 'api/v1/hooshina';
    protected $rest_base = '';
    protected $middleware;

    public function __construct() 
    {
        add_action('rest_api_init', [$this, 'register_routes']);
        $this->middleware = new ApiKeyMiddleware();
    }

    public function register_routes() 
    {
    }

    protected function get_namespace() 
    {
        return $this->namespace;
    }

    protected function get_rest_base() 
    {
        return $this->rest_base;
    }
} 