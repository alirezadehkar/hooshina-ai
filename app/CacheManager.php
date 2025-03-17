<?php
namespace HooshinaAi\App;

class CacheManager {
    const CACHE_EXPIRE_TIME = 86400;
    
    private static $prefix = 'hooshina_cache_';

    public static function set($key, $data, $expiration = self::CACHE_EXPIRE_TIME) {
        return set_transient(self::$prefix . $key, $data, $expiration);
    }

    public static function get($key) {
        return get_transient(self::$prefix . $key);
    }

    public static function delete($key) {
        return delete_transient(self::$prefix . $key);
    }

    public static function wrapper($cache_key, callable $data, $expiration = self::CACHE_EXPIRE_TIME)
    {
        $cached_data = self::get($cache_key);

        if (!empty($cached_data)) {
            return $cached_data;
        }

        $new_data = $data();

        self::set($cache_key, $new_data, $expiration);

        return $new_data;
    }
}