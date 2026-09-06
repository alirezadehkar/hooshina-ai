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
                // Generation POSTs may legitimately wait several minutes,
                // while metadata/status GETs should fail quickly and retry.
                'timeout' => max(1, (int) ($this->options['timeout'] ?? ($isGet ? 30 : 300))),
            ];

            if(!$isGet){
                $args['body'] = wp_json_encode($body);
            }

            // Only idempotent reads are retried. Retrying a timed-out
            // generation POST can submit and bill the same request twice.
            $retries = max(0, (int) ($this->options['retries'] ?? ($isGet ? 2 : 0)));

            for ($attempt = 0; $attempt <= $retries; $attempt++) {
                $response = $handler($url, $args);

                if (is_wp_error($response)) {
                    $message = method_exists($response, 'get_error_message')
                        ? $response->get_error_message()
                        : 'WordPress HTTP error';

                    if ($attempt < $retries) {
                        usleep(250000 * ($attempt + 1));
                        continue;
                    }

                    throw new \Exception('Invalid hooshina response: ' . $message);
                }

                $resBody = wp_remote_retrieve_body($response);
                $statusCode = (int) wp_remote_retrieve_response_code($response);

                if (($statusCode === 429 || $statusCode >= 500) && $attempt < $retries) {
                    usleep(250000 * ($attempt + 1));
                    continue;
                }

                if ($statusCode < 200 || $statusCode >= 300) {
                    throw new \Exception("Invalid hooshina status code: ({$statusCode})\n [RESPONSE] " . $resBody);
                }

                $decoded = json_decode($resBody, true);

                if (!is_array($decoded)) {
                    throw new \Exception('Invalid JSON returned by Hooshina.');
                }

                return $decoded;
            }
        } catch(\Throwable $th){
            Logger::error($th);
            return false;
        }
    }
}
