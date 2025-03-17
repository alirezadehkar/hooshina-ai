<?php
defined('ABSPATH') or die('No script kiddies please!');

function hooshina_ai_get_version()
{
    if(!function_exists('get_plugin_data')){
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    }
    $plugin_data = get_plugin_data(HOOSHINA_AI_PLUGIN_FILE_PATH);
    return $plugin_data['Version'];
}

function hooshina_ai_view($name, $data = [])
{
    if(is_array($data) && !empty($data)){
        extract($data);
    }

    $path = HOOSHINA_AI_VIEW_PATH . str_replace('.', '/', $name) . '.php';

    if (!file_exists($path)) return false;

    include $path;
}

function hooshina_ai_get_asset_data($key = null)
{
    $path = HOOSHINA_AI_PATH . 'build/index.asset.php';
    if (!file_exists($path))
        return false;

    $data = include $path;
    $data = !empty($data) && is_array($data) ? (object)$data : null;

    return is_object($data) ? (isset($key) && isset($data->{$key}) ? $data->{$key} : $data) : null;
}

