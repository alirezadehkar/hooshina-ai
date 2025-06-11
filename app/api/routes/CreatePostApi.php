<?php

namespace HooshinaAi\App\Api\Routes;

use HooshinaAi\App\Api\BaseApi;
use HooshinaAi\App\PostMeta;
use HooshinaAi\App\PostSeoData;
use HooshinaAi\App\Settings;
use HooshinaAi\App\Uploader;
use HooshinaAi\App\WPBlockConverter;
use Parsedown;

class CreatePostApi extends BaseApi 
{
    protected $rest_base = 'posts/publish';
    private $parsedown;

    public function __construct() 
    {
        parent::__construct();
        $this->parsedown = new Parsedown();
        $this->parsedown->setSafeMode(false);
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
                        'title' => [
                            'required' => true,
                            'type' => 'string',
                        ],
                        'content' => [
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'wp_kses_post',
                        ],
                        'post_type' => [
                            'required' => false,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => function($param) {
                                $allowed_types = ['post', 'page', 'product'];
                                return in_array($param, $allowed_types);
                            }
                        ],
                        'taxonomy' => [
                            'required' => false,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'category' => [
                            'required' => true,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                        'sections' => [
                            'required' => false,
                            'type' => 'array',
                        ],
                        'images' => [
                            'required' => false,
                            'type' => 'object',
                        ],
                        'featured_image_id' => [
                            'required' => false,
                            'type' => 'string',
                            'sanitize_callback' => 'sanitize_text_field',
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

    private function create_post($title, $content, $taxonomy, $category_slug, $featured_id = null) 
    {
        $defPostType = Settings::get_default_post_type();
        $defPostStatus = Settings::get_default_post_status();
        $defAuthor = Settings::get_default_author();
        $defTaxonomy = $taxonomy ?: 'category';

        $term = get_term_by('slug', $category_slug, $defTaxonomy);

        $converter = new WPBlockConverter($content);
        $blocks = $converter->convert(); 

        $post_data = [
            'post_title' => $title,
            'post_content' => $blocks,
            'post_status' => $defPostStatus ?: 'draft',
            'post_type' => $defPostType ?: 'post',
        ];

        if ($defAuthor) {
            $post_data['post_author'] = $defAuthor;
        }

        $post_id = wp_insert_post($post_data);

        if (!is_wp_error($post_id)) {
            add_post_meta($post_id, PostMeta::get_filter_meta_key(), 1);

            if ($featured_id) {
                set_post_thumbnail($post_id, $featured_id);
            }

            if ($term && !is_wp_error($term)) {
                wp_set_object_terms($post_id, $term->term_id, $defTaxonomy);
            }    
        } else {
            return false;
        }

        return $post_id;
    }

    private function generate_seo_data($post_id)
    {
        (new PostSeoData($post_id))->bulk_regenerate();
    }

    public function handle_request($request)
    {
        $params = $request->get_params();
        
        try {
            $rawContent = $params['content'] ?? null;
            $sections = $params['sections'] ?? [];
            $images = $params['images'] ?? [];
            $taxonomy = $params['taxonomy'] ?? null;
            $category_slug = $params['category'] ?? null;

            if(empty($rawContent)){
                return $this->create_response([
                    'success' => false,
                    'message' => __('Content field is required', 'hooshina-ai'),
                ], 400);
            }

            $processed_images = $this->process_images($images);
            
            $content_with_urls = $this->replace_temp_ids_with_urls($rawContent, $processed_images);
            
            $final_content = $content_with_urls;
            
            if (!empty($sections) && is_array($sections) && !empty($processed_images)) {
                foreach ($sections as $section) {
                    if (isset($section['title']) && !empty($section['title']) && isset($section['image_temp_id']) && !empty($section['image_temp_id'])) {
                        $sectionTitle = $section['title'];
                        $imageTempId = $section['image_temp_id'];
                        
                        if (isset($processed_images[$imageTempId])) {
                            $imageUrl = $processed_images[$imageTempId]['url'];
                            
                            $imageMarkdown = "\n\n![" . esc_attr($sectionTitle) . "](" . esc_url($imageUrl) . ")\n\n";
          
                            $escapedSectionTitle = preg_quote($sectionTitle, '/');

                            $final_content = preg_replace(
                                '/^(#+\\s*)?' . $escapedSectionTitle . '(\\s*.*\\R)/m', 
                                '$0' . $imageMarkdown, 
                                $final_content,
                                1
                            );
                        }
                    }
                }
            }

            $html_content = $this->convert_markdown_to_html($final_content);
            
            $featured_id = null;
            if (isset($params['featured_image_id']) && isset($processed_images[$params['featured_image_id']])) {
                $featured_id = $processed_images[$params['featured_image_id']]['id'];
            }
            
            $post_id = $this->create_post($params['title'], $html_content, $taxonomy, $category_slug, $featured_id);
            
            if (is_wp_error($post_id)) {
                return $this->create_response([
                    'success' => false,
                    'message' => $post_id->get_error_message(),
                ], 500);
            }

            $this->generate_seo_data($post_id);
            
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