<?php
namespace Hooshina\App\Generator;

use Hooshina\App\Connection;

class GeneratorClient
{
    public static function client(string $endpoint, array $body)
    {
        $headers = [
            'Authorization' => 'Bearer ' . Connection::getConnectionAuth(),
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'Referer' => site_url()
        ];

        $body['locale'] = 'fa';

        $response = wp_remote_post(Connection::getApiBaseUrl() . $endpoint, [
            'headers' => $headers,
            'timeout' => 250,
            'body' => wp_json_encode($body)
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
}