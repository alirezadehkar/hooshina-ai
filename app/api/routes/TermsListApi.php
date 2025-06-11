<?php

namespace HooshinaAi\App\Api\Routes;

use HooshinaAi\App\Api\BaseApi;

class TermsListApi extends BaseApi 
{
    protected $rest_base = 'terms/list';

    public function __construct() 
    {
        parent::__construct();
    }

    public function register_routes() 
    {
        register_rest_route(
            $this->get_namespace(),
            '/' . $this->get_rest_base(),
            [
                [
                    'methods' => 'GET',
                    'callback' => [$this, 'handle_request'],
                    'permission_callback' => [$this->middleware, 'handle_validate_api_key'],
                    'args' => [
                        'taxonomy' => [
                            'required' => false,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                ],
            ]
        );
    }

    public function handle_request($request)
    {
        $params = $request->get_params();
        
        try {
            $taxonomy = $params['taxonomy'] ?? 'category';
            
            $terms = get_terms([
                'taxonomy' => $taxonomy,
                'hide_empty' => false,
                'orderby' => 'name',
                'order' => 'ASC'
            ]);

            if (is_wp_error($terms)) {
                throw new \Exception($terms->get_error_message());
            }

            $formatted_terms = array_map(function($term) {
                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'parent' => $term->parent,
                    'count' => $term->count
                ];
            }, $terms);
            
            return $this->create_response([
                'success' => true,
                'terms' => $formatted_terms
            ], 200);
            
        } catch (\Exception $e) {
            return $this->create_response([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
} 