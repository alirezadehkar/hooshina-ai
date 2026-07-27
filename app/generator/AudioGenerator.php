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

        $client = new HaiClient();
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

        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/audio/status', ['content_id' => $content_id]);

        // HaiClient returns the Throwable itself on failure, and an object is
        // never empty(), so a plain empty() check would let an error through to
        // find(), which walks the exception's stack trace and can turn an
        // unrelated 'status' argument into a fake result.
        if(!is_array($response)){
            return ['content' => null, 'id' => null, 'status' => 'failed'];
        }

        $url = $this->find($response, 'media_url');
        $status = $this->find($response, 'status');

        if(empty($status)){
            return ['content' => null, 'id' => null, 'status' => 'failed'];
        }

        if($status == 'done' && filter_var($url, FILTER_VALIDATE_URL)){
            $uploadData = $this->uploadFile($url);
        }

        return [
            'content' => ($uploadData['url'] ?? $url),
            'id' => ($uploadData['id'] ?? null),
            'status' => $status,
        ];
    }

    public function get_speech_voices()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/audio/speech-voices');

        return $response ? $this->find($response, 'data') : null;
    }
}