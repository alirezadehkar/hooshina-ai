<?php

namespace HooshinaAi\App\Api;

use HooshinaAi\App\Api\Middleware\ApiKeyMiddleware;

class BaseApi 
{
    protected $namespace = 'api/v1/hooshina';
    protected $rest_base = '';
    protected $middleware;
    protected $rewrite_rules_flushed = false;

    public function __construct() 
    {
        add_action('rest_api_init', [$this, 'register_routes']);

        add_action('rest_api_init', [$this, 'handle_check_and_flush_routes']);
        add_action('init', [$this, 'handle_add_custom_rewrite_rules']);

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

    public function handle_add_custom_rewrite_rules() 
    {
        $namespace = $this->get_namespace();
        $endpoint = $this->get_rest_base();

        add_rewrite_rule(
            "^{$namespace}/{$endpoint}/?$",
            "index.php?rest_route=/{$namespace}/{$endpoint}",
            'top'
        );

        $rules = get_option('rewrite_rules');
        $rule_pattern = "^{$namespace}/{$endpoint}/?$";
        
        if (!isset($rules[$rule_pattern]) && !$this->rewrite_rules_flushed) {
            $this->flush_rewrite_rules();
        }
    }

    public function handle_check_and_flush_routes() 
    {
        $routes = rest_get_server()->get_routes();
        $our_route = '/' . $this->get_namespace() . '/' . $this->get_rest_base();
        
        if (!isset($routes[$our_route])) {
            $this->flush_rewrite_rules();
        }
    }

    private function flush_rewrite_rules() 
    {
        if (!$this->rewrite_rules_flushed) {
            flush_rewrite_rules();
            $this->rewrite_rules_flushed = true;
        }
    }

    protected function create_response($data, $status = 200) 
    {
        $response = rest_ensure_response($data);
        $response->set_status($status);
        return $response;
    }
} 