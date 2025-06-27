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
            'voice' => $this->get_param('voice', 'alloy'),
            'locale' => Helper::get_locale(),
        ];

        $client = new HaiClient();
        $response = $client->client('generator/audio/queue/text-to-speech', $body);

        $url = $response ? $this->find($response, 'output') : null;
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

    public function get_speech_voices()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/audio/speech-voices');

        return $response ? $this->find($response, 'data') : null;
    }
}