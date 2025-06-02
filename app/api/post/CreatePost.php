<?php

namespace HooshinaAi\App\Api\Post;

use HooshinaAi\App\Api\BaseApi;
use HooshinaAi\App\Settings;
use HooshinaAi\App\Uploader;
use Parsedown;

class CreatePost extends BaseApi 
{
    protected $rest_base = 'posts/publish';
    private $parsedown;
    private $rewrite_rules_flushed = false;

    public function __construct() 
    {
        parent::__construct();
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(false);
        
        add_action('rest_api_init', [$this, 'handle_check_and_flush_routes']);
        add_action('init', [$this, 'handle_add_custom_rewrite_rules']);
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

    private function flush_rewrite_rules() 
    {
        if (!$this->rewrite_rules_flushed) {
            flush_rewrite_rules();
            $this->rewrite_rules_flushed = true;
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

    private function create_response($data, $status = 200) 
    {
        $response = rest_ensure_response($data);
        $response->set_status($status);
        return $response;
    }

    public function register_routes() 
    {
        register_rest_route(
            $this->get_namespace(),
            '/' . $this->get_rest_base(),
            [
                [
                    'methods' => 'POST',
                    'callback' => [$this, 'handle_request'],
                    'permission_callback' => [$this->middleware, 'handle_validate_api_key'],
                    'args' => [
                        'content' => [
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'wp_kses_post',
                        ],
                        'sections' => [
                            'required' => false,
                            'type' => 'array',
                        ],
                        'images' => [
                            'required' => false,
                            'type' => 'object',
                        ],
                        'featured_image_temp_id' => [
                            'required' => false,
                            'type' => 'string',
                        ],
                    ],
                ],
            ]
        );
    }

    private function process_images($images) 
    {
        $processed_images = [];
        
        if (isset($images['sections']) && is_array($images['sections'])) {
            foreach ($images['sections'] as $image) {
                if (isset($image['original_url']) && isset($image['temp_id'])) {
                    $uploader = new Uploader($image['original_url']);
                    $result = $uploader->upload();
                    
                    if (!empty($result)) {
                        $processed_images[$image['temp_id']] = ['url' => $result['url'], 'id' => $result['id']];
                    }
                }
            }
        }
        
        if (isset($images['featured']) && isset($images['featured']['original_url'])) {
            $uploader = new Uploader($images['featured']['original_url']);
            $result = $uploader->upload();
            
            if (!empty($result)) {
                $processed_images[$images['featured']['temp_id']] = ['url' => $result['url'], 'id' => $result['id']];
            }
        }
        
        return $processed_images;
    }

    private function replace_temp_ids_with_urls($content, $processed_images) 
    {
        if(!empty($processed_images)){
            foreach ($processed_images as $temp_id => $data) {
                $content = str_replace($temp_id, $data['url'], $content);
            }
        }
        return $content;
    }

    private function convert_markdown_to_html($content) 
    {
        return $this->parsedown->text($content);
    }

    private function create_post($content, $featured_id = null) 
    {
        $defPostType = Settings::get_default_post_type();
        $defPostStatus = Settings::get_default_post_status();
        $defAuthor = Settings::get_default_author();
        $defTaxonomy = Settings::get_default_taxonomy();
        $defCategory = Settings::get_default_category();

        $post_data = [
            'post_content' => $content,
            'post_status' => $defPostStatus ?: 'draft',
            'post_type' => $defPostType ?: 'post',
        ];

        if ($defAuthor) {
            $post_data['post_author'] = $defAuthor;
        }

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            if ($featured_id) {
                set_post_thumbnail($post_id, $featured_id);
            }

            if ($defTaxonomy && $defCategory) {
                wp_set_object_terms($post_id, $defCategory, $defTaxonomy);
            }
        }

        return $post_id;
    }

    public function handle_request($request) 
    {
        $params = $request->get_params();
        
        try {
            $rawContent = $params['content'] ?? null;

            if(empty($rawContent)){
                return $this->create_response([
                    'success' => false,
                    'message' => __('Content field is required', 'hooshina-ai'),
                ], 400);
            }

            $processed_images = $this->process_images($params['images'] ?? []);
            
            $content = $this->replace_temp_ids_with_urls($rawContent, $processed_images);
            
            $html_content = $this->convert_markdown_to_html($content);
            
            $featured_id = null;
            if (isset($params['featured_image_temp_id']) && isset($processed_images[$params['featured_image_temp_id']])) {
                $featured_id = $processed_images[$params['featured_image_temp_id']]['id'];
            }
            
            $post_id = $this->create_post($html_content, $featured_id);
            
            if (is_wp_error($post_id)) {
                return $this->create_response([
                    'success' => false,
                    'message' => $post_id->get_error_message(),
                ], 500);
            }
            
            return $this->create_response([
                'success' => true,
                'message' => __('Content created successfully', 'hooshina-ai'),
                'post_id' => $post_id
            ], 201);
            
        } catch (\Exception $e) {
            return $this->create_response([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }
} 