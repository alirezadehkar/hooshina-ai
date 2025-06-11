<?php
namespace HooshinaAi\App;

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
        return wp_create_nonce('hooshina_ai_nonce');
    }

    public static function get_ip()
    {
        if(isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['HTTP_CLIENT_IP']));
        elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']));
        elseif(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED']));
        elseif(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']));
        elseif(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['HTTP_FORWARDED_FOR']));
        elseif(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['HTTP_FORWARDED']));
        elseif(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
        else
            $ipaddress = false;

        return $ipaddress;
    }

    public static function unslash_sanitize($str)
    {
        return sanitize_text_field(wp_unslash($str));
    }

    public static function get_locale()
    {
        $locale = strtolower(get_locale());
        return is_rtl() && self::str_contains($locale, 'fa') ? 'fa' : 'en';
    }

    public static function str_contains($haystack, $needle){
        if (function_exists('str_contains')){
            return str_contains($haystack, $needle);
        }

        return strpos($haystack, $needle) !== false;
    }    

    public static function get_first_key(array $data)
    {
        if(empty($data)){
            return null;
        }

        $data = array_keys($data);
        return $data[array_key_first($data)] ?? null;
    }

    public static function get_current_lang()
    {
        if (!function_exists('wp_get_available_translations')) {
            require_once(ABSPATH . 'wp-admin/includes/translation-install.php');
        }

        $locale = get_locale();
        $translations = wp_get_available_translations();
        $translation = is_array($translations) && isset($translations[$locale]) ? $translations[$locale] : null;

        if (is_array($translation) && isset($translation['english_name'])) {
            $englishName = $translation['english_name'];
            
            if(self::str_contains($englishName, ' (')){
                $englishName = explode(' (', $englishName)[0]; 
            }

            return strtolower($englishName);
        }

        return self::get_locale() == 'en' ? 'english' : 'persian';
    }

    public static function convertMarkdown($markdown)
    {
        $parser = new \Netcarver\Textile\Parser();
        $markdown = str_replace("\n", "  \n", $markdown);
        return $parser->parse($markdown);
    }

    public static function allow_svg_tags($svg_content) {
        $allowed_html = array(
            'svg' => array(
                'xmlns' => array(),
                'width' => array(),
                'height' => array(),
                'viewbox' => array(),
                'fill' => array(),
                'stroke' => array(),
                'stroke-width' => array(),
                'stroke-linecap' => array(),
                'stroke-linejoin' => array(),
            ),
            'path' => array(
                'd' => array(),
            ),
        );
        
        return wp_kses($svg_content, $allowed_html);
    }


    public static function is_blocked_urls() {
        return (new \WP_Http)->block_request('https://app.hooshina.com');
    }
}