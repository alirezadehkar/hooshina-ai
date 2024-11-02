<?php
namespace Hooshina\App;

defined('ABSPATH') or die('No script kiddies please!');

class Helper {
    /**
     *
     * Json validation and return
     *
     * @param $str
     * @param bool $assoc
     * @return false|mixed
     */
    public static function is_json($str, bool $assoc = false)
    {
        $str = json_decode($str, $assoc);
        return $str && json_last_error() === JSON_ERROR_NONE ? $str : false;
    }

    /**
     *
     * Generate security nonce
     *
     * @return mixed
     */
    public static function generate_nonce()
    {
        return wp_create_nonce('hooshina_nonce');
    }

    public static function get_ip()
    {
        if(isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = self::unslash_sanitize($_SERVER['HTTP_CLIENT_IP']);
        elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = self::unslash_sanitize($_SERVER['HTTP_X_FORWARDED_FOR']);
        elseif(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = self::unslash_sanitize($_SERVER['HTTP_X_FORWARDED']);
        elseif(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = self::unslash_sanitize($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']);
        elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = self::unslash_sanitize($_SERVER['HTTP_FORWARDED_FOR']);
        elseif(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = self::unslash_sanitize($_SERVER['HTTP_FORWARDED']);
        elseif(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = self::unslash_sanitize($_SERVER['REMOTE_ADDR']);
        else
            $ipaddress = false;

        return $ipaddress;
    }

    public static function dd($data)
    {
        echo '<pre>';
        var_dump($data);
        echo '</pre>';
    }

    public static function unslash_sanitize($str)
    {
        return wp_unslash(sanitize_text_field($str));
    }

    public static function get_image_tones()
    {
        return [
            'photorealism' => __('Photorealism', 'hooshina-ai'),
            'realistic' =>  __('Realistic Art', 'hooshina-ai'),
            '3d' => __('3D Art', 'hooshina-ai'),
            'creative' => __('Creative Art', 'hooshina-ai'),
            'art-oils' => __('Art Oils', 'hooshina-ai'),
            'digital-art' => __('Digital Art', 'hooshina-ai'),
            'anime' => __('Anime', 'hooshina-ai'),
            'portrait' => __('Portrait', 'hooshina-ai'),
            'landscape' => __('Landscape', 'hooshina-ai'),
            'abstract' => __('Abstract Art', 'hooshina-ai'),
            'surreal' => __('Surrealism', 'hooshina-ai'),
            'cartoon' => __('Cartoon Art', 'hooshina-ai'),
            'sci-fi' => __('Sci-Fi Art', 'hooshina-ai'),
            'fantasy' => __('Fantasy Art', 'hooshina-ai'),
            'minimalist' => __('Minimalist Art', 'hooshina-ai'),
            'vintage' => __('Vintage Art', 'hooshina-ai'),
            'modern-art' => __('Modern Art', 'hooshina-ai'),
            'pop-art' => __('Pop Art', 'hooshina-ai'),
            'impressionism' => __('Impressionism', 'hooshina-ai'),
            'expressionism' => __('Expressionism', 'hooshina-ai'),
            'cubism' => __('Cubism', 'hooshina-ai'),
            'fauvism' => __('Fauvism', 'hooshina-ai'),
            'graffiti-art' => __('Graffiti Art', 'hooshina-ai'),
            'street-art' => __('Street Art', 'hooshina-ai'),
            'classical-art' =>  __('Classical Art', 'hooshina-ai'),
        ];
    }

    public static function get_supported_lanuages()
    {
        return [
            'english' => __('English', 'hooshina-ai'),
            'persian' => __('Persian', 'hooshina-ai'),
            'french' => __('French', 'hooshina-ai'),
            'spanish' => __('Spanish', 'hooshina-ai'),
            'german' => __('German', 'hooshina-ai'),
            'italian' => __('Italian', 'hooshina-ai'),
            'chinese' => __('Chinese', 'hooshina-ai'),
            'japanese' => __('Japanese', 'hooshina-ai'),
            'korean' => __('Korean', 'hooshina-ai'),
            'arabic' => __('Arabic', 'hooshina-ai'),
            'russian' => __('Russian', 'hooshina-ai'),
            'portuguese' => __('Portuguese', 'hooshina-ai'),
            'hindi' => __('Hindi', 'hooshina-ai'),
            'turkish' => __('Turkish', 'hooshina-ai'),
            'urdu' => __('Urdu', 'hooshina-ai'),
            'dutch' => __('Dutch', 'hooshina-ai'),
            'swedish' => __('Swedish', 'hooshina-ai'),
            'norwegian' => __('Norwegian', 'hooshina-ai'),
            'danish' => __('Danish', 'hooshina-ai'),
            'finnish' => __('Finnish', 'hooshina-ai'),
            'polish' => __('Polish', 'hooshina-ai'),
            'czech' => __('Czech', 'hooshina-ai'),
            'hungarian' => __('Hungarian', 'hooshina-ai'),
            'greek' => __('Greek', 'hooshina-ai'),
            'hebrew' => __('Hebrew', 'hooshina-ai'),
            'thai' => __('Thai', 'hooshina-ai'),
            'vietnamese' => __('Vietnamese', 'hooshina-ai'),
            'indonesian' => __('Indonesian', 'hooshina-ai'),
            'malay' => __('Malay', 'hooshina-ai'),
            'filipino' => __('Filipino', 'hooshina-ai'),
            'swahili' => __('Swahili', 'hooshina-ai'),
            'bulgarian' => __('Bulgarian', 'hooshina-ai'),
            'romanian' => __('Romanian', 'hooshina-ai'),
            'serbian' => __('Serbian', 'hooshina-ai'),
            'croatian' => __('Croatian', 'hooshina-ai'),
            'slovak' => __('Slovak', 'hooshina-ai'),
            'estonian' => __('Estonian', 'hooshina-ai'),
            'latvian' => __('Latvian', 'hooshina-ai'),
            'lithuanian' => __('Lithuanian', 'hooshina-ai'),
            'ukrainian' => __('Ukrainian', 'hooshina-ai'),
            'belarusian' => __('Belarusian', 'hooshina-ai'),
            'georgian' => __('Georgian', 'hooshina-ai'),
            'armenian' => __('Armenian', 'hooshina-ai'),
            'azeri' => __('Azeri', 'hooshina-ai'),
            'kurdish' => __('Kurdish', 'hooshina-ai'),
            'sinhala' => __('Sinhala', 'hooshina-ai'),
            'nepali' => __('Nepali', 'hooshina-ai'),
            'malagasy' => __('Malagasy', 'hooshina-ai'),
        ];
    }

    public static function get_image_sizes()
    {
        return [
            '1024x1024' => '1024x1024',
            '1792x1024' => '1792x1024',
            '1024x1792' => '1024x1792',
        ];
    }

    public static function get_content_tones()
    {
        return [
            "friendly" =>  __('Friendly', 'hooshina-ai'),
            "motivational" =>  __('Motivational', 'hooshina-ai'),
            "neutral" =>  __('Neutral', 'hooshina-ai'),
            "formal" =>  __('Formal', 'hooshina-ai'),
            "informal" =>  __('Informal', 'hooshina-ai'),
            "professional" =>  __('Professional', 'hooshina-ai'),
            "informative" =>  __('Informative', 'hooshina-ai'),
            "humorous" =>  __('Humorous', 'hooshina-ai'),
            "serious" =>  __('Serious', 'hooshina-ai'),
            "lovely" =>  __('Lovely', 'hooshina-ai'),
        ];
    }

    public static function get_generator_types()
    {
        return [
            'text' => __('Text', 'hooshina-ai'),
            'image' => __('Image', 'hooshina-ai'),
        ];
    }
}