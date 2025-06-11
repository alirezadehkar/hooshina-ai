<?php
namespace HooshinaAi\App;

use HooshinaAi\App\Generator\Generator;

class PostSeoData
{
    private $post;
    private $meta_keys = [
        'rank_math' => [
            'title' => 'rank_math_title',
            'description' => 'rank_math_description',
            'focus_keyword' => 'rank_math_focus_keyword'
        ],
        'yoast' => [
            'title' => '_yoast_wpseo_title',
            'description' => '_yoast_wpseo_metadesc',
            'focus_keyword' => '_yoast_wpseo_focuskw'
        ]
    ];

    public function __construct($post_id)
    {
        $this->post = get_post($post_id);
    }

    public function bulk_regenerate()
    {
        if(Options::is_rank_math_active() || Options::is_yoast_seo_active()){
            try {
                $title = $this->post->post_title;
                $this->update_title($title);

                $description = $this->generate_meta(Generator::META_DESCRIPTION_PROMPT_ID);
                if($description){
                    $this->update_description($description);
                }

                $keyword = $this->generate_meta(Generator::KEYWORD_PROMPT_ID);
                if($keyword){
                    $this->update_focus_keyword($keyword);
                }
            } catch(\Throwable $th){
                Logger::error($th);
            }
        }   
    }

    private function generate_meta($prompt_id)
    {
        $generator = new Generator();

        $generate = $generator->content()->set_params([
            'subject' => $this->post->post_title,
            'lang' => Settings::get_default_content_lang(),
            'tone' => Settings::get_default_content_tone(),
            'prompt_id' => $prompt_id
        ])->generate();

        $content = isset($generate['content']) ? $generate['content'] : null;

        return $content;
    }

    private function get_meta_key($type)
    {
        if (Options::is_rank_math_active()) {
            return $this->meta_keys['rank_math'][$type] ?? null;
        }
        
        if (Options::is_yoast_seo_active()) {
            return $this->meta_keys['yoast'][$type] ?? null;
        }

        return null;
    }

    private function update_meta($type, $value)
    {
        $meta_key = $this->get_meta_key($type);
        if (empty($meta_key)) {
            return false;
        }

        if ($type === 'focus_keyword') {
            $current_keywords = get_post_meta($this->post->ID, $meta_key, true);
            if (!empty($current_keywords)) {
                $keywords_array = array_unique(array_merge(
                    array_map('trim', explode(',', $current_keywords)),
                    array_map('trim', explode(',', $value))
                ));
                $value = implode(',', $keywords_array);
            }
        }

        return update_post_meta($this->post->ID, $meta_key, $value);
    }

    public function update_title($value)
    {
        return $this->update_meta('title', $value);
    }

    public function update_description($value)
    {
        return $this->update_meta('description', $value);
    }

    public function update_focus_keyword($value)
    {
        return $this->update_meta('focus_keyword', $value);
    }
}