<?php
namespace HooshinaAi\App;

use HooshinaAi\App\AdminMenu;
use HooshinaAi\App\Generator\GeneratorHelper;
use HooshinaAi\App\Options;
use HooshinaAi\App\Traits\Singleton;

class Connection
{
    use Singleton;

    private static $appName = 'Hooshina App';
    private static $baseUrl = 'https://app.hooshina.com/';

    private static $connectionOptionKey = 'hooshina_connection_token';
    
    const CHARGE_PAGE_URL = 'https://app.hooshina.com/panel/credits';

    public function __construct()
    {
        add_action('hai_event_hooshina_connection_checker', [__CLASS__, 'handle_event_check_connection_status']);
        add_action('admin_init', [__CLASS__, 'handle_register_cron']);
    }

    private static function getBaseUrl()
    {
        return self::$baseUrl;
    }

    public static function getApiBaseUrl()
    {
        return self::$baseUrl . 'api/v1/';
    }

    private static function getAppUuid()
    {
        return get_option('hooshina_app_user_uuid');
    }

    private static function generateApplicationPassword()
    {
        $host = wp_parse_url(site_url(), PHP_URL_HOST);
        return md5($host);
    }

    public static function getConnectUrl()
    {
        $appPass = self::generateApplicationPassword();
        if (!$appPass)
            return false;

        $data = [
            'auth_token' => $appPass,
            'from' => AdminMenu::get_options_url('account')
        ];

        return add_query_arg('data', base64_encode(wp_json_encode($data)), (self::getBaseUrl() . 'connect/app'));
    }

    private static function requestByAction($token, $action = 'verify')
    {
        $url = self::getApiBaseUrl() . 'connect/' . $action;

        $headers = [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 120,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        return wp_remote_retrieve_body($response);

        $status_code = wp_remote_retrieve_response_code($response);

        if ($action !== 'revoke' && $status_code !== 200) {
            return false;
        }

        return wp_remote_retrieve_body($response);
    }

    public static function verifyConnection($token)
    {
        return self::requestByAction($token);
    }

    public static function revokeConnection($token)
    {
        $revoke = self::requestByAction($token, 'revoke');

        if ($revoke){
            delete_option(self::$connectionOptionKey);
            GeneratorHelper::delete_cache();
        }

        return $revoke;
    }

    public static function isConnected()
    {
        $data = self::getCurrentConnectionData();
        return is_object($data) && isset($data->siteKey) ? $data->siteKey : false;
    }

    public static function getCurrentConnectionData()
    {
        $data = get_option(self::$connectionOptionKey);

        if (empty($data))
            return false;

        $tokenData = json_decode($data);
        if (!$tokenData || !is_object($tokenData))
            return false;

        return $tokenData;
    }

    private static function getCurrentPageUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
        $host = isset($_SERVER['HTTP_HOST']) ? sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])) : null;
        $uri = isset($_SERVER['REQUEST_URI']) ? sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'])) : null;
        return $protocol . $host . $uri;
    }

    public static function getCurrentWallet()
    {
        return Options::get_current_wallet();
    }

    public static function getConnectionAuth()
    {
        $tokenData = self::getCurrentConnectionData();
        return is_object($tokenData) && isset($tokenData->auth) ? $tokenData->auth : null;
    }

    public static function getConnectionSiteKey()
    {
        return self::isConnected();
    }

    public function getWalletBalance()
    {
        $auth = self::getConnectionAuth();
        if (!$auth)
            return false;

        $url = self::getApiBaseUrl() . 'user/total-credit';

        $headers = [
            'Authorization' => 'Bearer ' . $auth,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        $response = wp_remote_get($url, [
            'headers' => $headers,
            'timeout' => 90,
        ]);

        if (is_wp_error($response)) {
            return false;
        }

        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code !== 200) {
            return false;
        }

        return json_decode(wp_remote_retrieve_body($response));
    }

    public static function handle_return_from_ai()
    {
        $currentUrl = self::getCurrentPageUrl();

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        $mainToken = isset($_GET['token']) ? sanitize_text_field(wp_unslash($_GET['token'])) : null;
        
        if (empty($mainToken))
            return false;

        $tokenData = json_decode(base64_decode($mainToken));

        if (!$tokenData || !is_object($tokenData) || (!isset($tokenData->auth) || !isset($tokenData->siteKey))){
            return false;
        }

        $verify = self::verifyConnection($tokenData->auth);

        if (!$verify){
            delete_option(self::$connectionOptionKey);
            return false;
        }

        update_option(self::$connectionOptionKey, base64_decode($mainToken));

        wp_redirect(remove_query_arg('token', $currentUrl));
        exit();
    }

    public static function handle_event_check_connection_status()
    {
        $auth = self::getConnectionAuth();
        if (!$auth)
            return false;

        if (!isset($auth) || self::verifyConnection($auth)){
            return false;
        }

        self::revokeConnection($auth);
        delete_option(self::$connectionOptionKey);
    }

    public static function handle_register_cron()
    {
        if (!wp_next_scheduled('hai_event_hooshina_connection_checker')) {
            $hour = wp_rand(0, 23);
            $minutes = wp_rand(0, 59);
            $timestamp = strtotime("tomorrow {$hour}:{$minutes}:00");
            wp_schedule_event($timestamp, 'daily', 'hai_event_hooshina_connection_checker');
        }
    }

    public static function verifySiteKey($siteKey)
    {
        $apiUrl = self::getApiBaseUrl() . 'connect/verify-site-key';
        
        $ch = curl_init();
        
        curl_setopt($ch, CURLOPT_URL, $apiUrl . '?site_key=' . urlencode($siteKey));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Content-Type: application/json'
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        curl_close($ch);
        
        if ($httpCode == 200 && $response) {
            $result = json_decode($response);
            return $result && isset($result->verified) ? $result->verified : false;
        }
        
        return false;
    }
}