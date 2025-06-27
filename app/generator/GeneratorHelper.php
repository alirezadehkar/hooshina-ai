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
        CacheManager::delete('speech_voices');
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

    public static function get_speech_voices()
    {
        return CacheManager::wrapper('speech_voices', function(){
            $provider = new Generator;
            $data = $provider->audio()->get_speech_voices();
    
            return $data;
        });
    }

    public static function get_generator_types()
    {
        return [
            'text' =>[
                'title' =>  __('Text', 'hooshina-ai'),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M20 17v-12c0 -1.121 -.879 -2 -2 -2s-2 .879 -2 2v12l2 2l2 -2z" /> <path d="M16 7h4" /> <path d="M18 19h-13a2 2 0 1 1 0 -4h4a2 2 0 1 0 0 -4h-3" /> </svg>'
            ],
            'image' => [
                'title' => __('Image', 'hooshina-ai'),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M15 8h.01" /> <path d="M10 21h-4a3 3 0 0 1 -3 -3v-12a3 3 0 0 1 3 -3h12a3 3 0 0 1 3 3v5" /> <path d="M3 16l5 -5c.928 -.893 2.072 -.893 3 0l1 1" /> <path d="M14 21v-4a2 2 0 1 1 4 0v4" /> <path d="M14 19h4" /> <path d="M21 15v6" /> </svg>'
            ],
            'text-to-speech' => [
                'title' => __('Text to Speech', 'hooshina-ai'),
                'icon' => '<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M18.364 18.364a9 9 0 1 0 -12.728 0" /> <path d="M11.766 22h.468a2 2 0 0 0 1.985 -1.752l.5 -4a2 2 0 0 0 -1.985 -2.248h-1.468a2 2 0 0 0 -1.985 2.248l.5 4a2 2 0 0 0 1.985 1.752z" /> <path d="M12 9m-2 0a2 2 0 1 0 4 0a2 2 0 1 0 -4 0" /> </svg>'
            ]
        ];
    }
}