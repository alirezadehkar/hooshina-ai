<?php
namespace Hooshina\App;

defined('ABSPATH') or die('No script kiddies please!');

class Assets {
    public static function get_handle_name($handle){
        return str_replace(['.'], '-', $handle);
    }

    /**
     *
     * wp register style custom public static function
     *
     * @param $handle
     * @param $src
     * @param null $deps
     * @param null $ver
     * @param null $media
     * @param bool $enqueue
     */
    public static function register_style($handle, $src, $deps = null, $ver = null, $media = null, $enqueue = false)
    {
        $ver = empty($ver) ? get_hai_version() : $ver;
        if (!empty($handle)) {
            $handle = static::get_handle_name($handle);
            if ($enqueue) {
                wp_enqueue_style($handle, $src, $deps, $ver, $media);
            } else {
                wp_register_style($handle, $src, $deps, $ver, $media);
            }
        }
    }

    /**
     * @param $handle
     * @param $src
     * @param $deps
     * @param $ver
     * @param $in_footer
     * @param $enqueue
     */
    public static function register_script($handle, $src, $deps = null, $ver = null, $in_footer = null, $enqueue = false)
    {
        $ver = empty($ver) ? get_hai_version() : $ver;
        if (!empty($handle)) {
            $handle = static::get_handle_name($handle);
            if ($enqueue) {
                wp_enqueue_script($handle, $src, $deps, $ver, $in_footer);
            } else {
                wp_register_script($handle, $src, $deps, $ver, $in_footer);
            }
        }
    }

    /**
     * @param $handle
     * @param $src
     * @param $deps
     * @param $ver
     * @param $media
     * @return void
     */
    public static function enqueue_style($handle, $src = null, $deps = null, $ver = null, $media = null)
    {
        if (!empty($handle)) {
            self::register_style($handle, $src, $deps, $ver, $media, true);
        }
    }

    /**
     * @param $handle
     * @param $src
     * @param $deps
     * @param $ver
     * @param $in_footer
     * @return void
     */
    public static function enqueue_script($handle, $src = null, $deps = null, $ver = null, $in_footer = null)
    {
        if (!empty($handle)) {
            self::register_script($handle, $src, $deps, $ver, $in_footer, true);
        }
    }

    public static function localize_script($handle, $object_name, $data){
        if(is_array($handle)){
            if (empty($handle)) return false;

            foreach ($handle as $handle_name){
                self::localize_script($handle_name, $object_name, $data);
            }
        } else {
            wp_localize_script($handle, $object_name, $data);
        }
    }

    /**
     *
     * Localize js translate
     *
     * @param null $key
     *
     * @return array|mixed
     */
    public static function get_localize_data($key = null)
    {
        $params = array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => Helper::generate_nonce(),

            'pluginOptionsUrl' => AdminMenu::get_options_url(),

            'generator' => [
                'types' => Helper::get_generator_types(),
                'contentTones' => Helper::get_content_tones(),
                'imagesTones' => Helper::get_image_tones(),
                'imageSizes' => Helper::get_image_sizes(),
                'languages' => Helper::get_supported_lanuages(),
            ],

            'texts' => [

            ]
        );

        $params = apply_filters('hooshina_script_localize_data', $params);

        return (!empty($key)) ? $params[$key] : $params;
    }

    public static function get_css($name){
        return HAI_CSS_URL . $name . '.css';
    }

    public static function get_js($name){
        return HAI_JS_URL . $name . '.js';
    }

    public static function get_img($name, $extension = 'png'){
        return HAI_IMG_URL . $name . '.' . $extension;
    }
}