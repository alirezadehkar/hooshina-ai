<?php
namespace HooshinaAi\App\Generator;

use HooshinaAi\App\CacheManager;
use HooshinaAi\App\Helper;
use HooshinaAi\App\Logger;

class GeneratorHelper
{
    public static function get_prompt_id($str)
    {
        $str = strtolower($str);

        if(Helper::str_contains($str, 'title')){
            return Generator::TITLE_PROMPT_ID;
        } elseif(Helper::str_contains($str, 'seo-description')){
            return Generator::META_DESCRIPTION_PROMPT_ID;
        } elseif(Helper::str_contains($str, 'product-description')){
            return Generator::PRODUCT_DESCRIPTION_PROMPT_ID;
        } elseif(Helper::str_contains($str, 'keyword')){
            return Generator::KEYWORD_PROMPT_ID;
        } elseif(Helper::str_contains($str, 'review-reply')){
            return Generator::PRODUCT_REVIEW_PROMPT_ID;
        } elseif(Helper::str_contains($str, 'comment')){
            return Generator::COMMENT_PROMPT_ID;
        }

        return null;
    }

    public static function delete_cache()
    {
        CacheManager::delete('supported_lanuages');
        CacheManager::delete('content_tones');
        CacheManager::delete('image_styles');
        CacheManager::delete('image_styles_' . Generator::TextToImage);
        CacheManager::delete('image_styles_' . Generator::ProductImage);
        CacheManager::delete('image_sizes');
    }

    public static function get_supported_lanuages()
    {
        return CacheManager::wrapper('supported_lanuages', function(){
            $provider = new Generator;
            $data = $provider->content()->get_supported_languages();
    
            return $data;
        });
    }

    public static function get_content_tones()
    {
        return CacheManager::wrapper('content_tones', function(){
            $provider = new Generator;
            $data = $provider->content()->get_content_tones();
        
            return $data;
        });
    }

    public static function get_image_styles($model = null)
    {
        return CacheManager::wrapper('image_styles' . ($model ? '_' . $model : ''), function() use ($model){
            $styles = [];

            $provider = new Generator;
            $data = $provider->image()->get_image_styles($model);
    
            if(!empty($data)){
                foreach($data as $styleKey => $styleData){
                    $styles[$styleKey] = $styleData['title'];
                }
            }
        
            return $styles;
        });
    }

    public static function get_image_sizes()
    {
        return CacheManager::wrapper('image_sizes', function(){
            $sizes = [];

            $provider = new Generator;
            $data = $provider->image()->get_image_sizes();

            if(!empty($data)){
                foreach($data as $sizeKey => $sizeData){
                    $sizes[$sizeKey] = $sizeData['title'];
                }
            }

            return $sizes;
        });
    }

    public static function get_generator_types()
    {
        return [
            'text' => __('Text', 'hooshina-ai'),
            'image' => __('Image', 'hooshina-ai'),
        ];
    }
}