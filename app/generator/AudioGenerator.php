<?php
namespace HooshinaAi\App\Generator;

use HooshinaAi\App\Helper;
use HooshinaAi\App\Provider\HaiClient;

class AudioGenerator extends GeneratorAbstract implements GeneratorInterface
{
    public function generate()
    {
        $body = [
            'content' => $this->get_param('content'),
            'voice' => $this->get_param('voice'),
            'locale' => Helper::get_locale(),
        ];

        $client = new HaiClient(['timeout' => 60, 'retries' => 0]);
        $response = $client->client('generator/audio/queue/text-to-speech', $body);

        $url = $response ? $this->find($response, 'output') : null;
        // Speech is generated asynchronously, so a fresh request normally comes
        // back queued with no audio yet and is finished by get_audio_status().
        $status = $response ? $this->find($response, 'status') : 'done';
        $contentId = $response ? $this->find($response, 'content_id') : null;

        if($status == 'done' && filter_var($url, FILTER_VALIDATE_URL)){
            $uploadData = $this->uploadFile($url);
        }

        return [
            'content' => ($uploadData['url'] ?? $url),
            'id' => ($uploadData['id'] ?? null),
            'status' => $status,
            'content_id' => $contentId
        ];
    }

    /**
     * Polls one queued speech job.
     *
     * A missing content id means the job failed and the server dropped the
     * placeholder, so it is reported as failed rather than left pending; the
     * caller would otherwise poll forever.
     */
    public function get_audio_status($content_id = null)
    {
        if(empty($content_id)){
            return ['content' => null, 'id' => null, 'status' => 'failed'];
        }

        $cacheKey = 'hai_audio_generation_status_' . md5((string) $content_id);
        $cached = function_exists('get_transient') ? get_transient($cacheKey) : false;

        if (is_array($cached)) {
            return $cached;
        }

        $client = new HaiClient(['method' => 'get', 'timeout' => 30, 'retries' => 2]);
        $response = $client->client('generator/audio/status', ['content_id' => $content_id]);

        if(!is_array($response)){
            return ['content' => null, 'id' => null, 'status' => 'processing'];
        }

        $url = $this->find($response, 'media_url');
        $status = $this->find($response, 'status');

        if(empty($status)){
            return ['content' => null, 'id' => null, 'status' => 'processing'];
        }

        if($status == 'done' && filter_var($url, FILTER_VALIDATE_URL)){
            $lockKey = 'hai_audio_generation_lock_' . md5((string) $content_id);
            $lockAcquired = !function_exists('add_option') || add_option($lockKey, time(), '', false);

            if (!$lockAcquired && function_exists('get_option')) {
                $lockCreatedAt = (int) get_option($lockKey, 0);
                if ($lockCreatedAt > 0 && time() - $lockCreatedAt > 120 && function_exists('delete_option')) {
                    delete_option($lockKey);
                    $lockAcquired = add_option($lockKey, time(), '', false);
                }
            }

            if (!$lockAcquired) {
                return ['content' => null, 'id' => null, 'status' => 'processing'];
            }

            try {
                $uploadData = $this->uploadFile($url);
            } finally {
                if (function_exists('delete_option')) {
                    delete_option($lockKey);
                }
            }
        }

        $result = [
            'content' => ($uploadData['url'] ?? $url),
            'id' => ($uploadData['id'] ?? null),
            'status' => $status,
        ];

        if (function_exists('set_transient')) {
            $ttl = $status === 'done' ? DAY_IN_SECONDS : 5;
            set_transient($cacheKey, $result, $ttl);
        }

        return $result;
    }

    public function get_speech_voices()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/audio/speech-voices');

        return $response ? $this->find($response, 'data') : null;
    }
}
