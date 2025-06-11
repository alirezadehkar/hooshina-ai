<?php
namespace HooshinaAi\App\Provider;

use HooshinaAi\App\Connection;
use HooshinaAi\App\Helper;
use HooshinaAi\App\Logger;

class HaiClient
{
    protected array $options = [];

    public function __construct(array $options = [])
    {
        $this->options = $options;
    }

    public function get_method()
    {
        return isset($this->options['method']) && is_string($this->options['method']) ? strtoupper($this->options['method']) : 'POST';
    }

    public function client(string $endpoint, array $body = [])
    {
        try{
            $headers = [
                'Authorization' => 'Bearer ' . Connection::getConnectionAuth(),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ];
    
            $body['locale'] = Helper::get_locale();
    
            $isGet = $this->get_method() == 'GET';

            $handler = $isGet ? 'wp_remote_get' : 'wp_remote_post';

            $url = Connection::getApiBaseUrl() . $endpoint;

            $url = $isGet ? add_query_arg($body, $url) : $url;

            $args = [
                'headers' => $headers,
                'timeout' => 500,
            ];

            if(!$isGet){
                $args['body'] = wp_json_encode($body);
            }

            $response = $handler($url, $args);
    
            if (is_wp_error($response)) {
                throw new \Exception('Invalid hooshina response: ' . $response->get_error_message());
            }
    
            $status_code = wp_remote_retrieve_response_code($response);
    
            if ($status_code != 200) {
                throw new \Exception('Invalid hooshina status code: ' . $status_code);
            }
    
            return json_decode(wp_remote_retrieve_body($response), true);
        } catch(\Throwable $th){
            Logger::error($th);
            return false;
        }
    }
}