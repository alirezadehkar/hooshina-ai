<?php

namespace HooshinaAi\App\Api\Middleware;

use HooshinaAi\App\Connection;

class ApiKeyMiddleware {
    public function handle_validate_api_key($request) {
        $api_key = $request->get_header('X-Api-Key');
        
        if (empty($api_key)) {
            return new \WP_Error(
                'rest_forbidden',
                __('API key is required.', 'hooshina-ai'),
                ['status' => 401]
            );
        }

        if ($api_key !== Connection::getConnectionSiteKey()) {
            return new \WP_Error(
                'rest_forbidden',
                __('Invalid API key.', 'hooshina-ai'),
                ['status' => 401]
            );
        }

        return true;
    }
} 