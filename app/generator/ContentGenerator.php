<?php
namespace HooshinaAi\App\Generator;

use HooshinaAi\App\Helper;
use HooshinaAi\App\Provider\HaiClient;

class ContentGenerator extends GeneratorAbstract implements GeneratorInterface
{
    public function generate()
    {
        $body = [
            'subject' => $this->get_param('subject'),
            'tone' => $this->get_param('tone', 'neutral'),
            'lang' => $this->get_param('lang', 'english'),
            'locale' => Helper::get_locale(),
        ];

        if(!empty($this->get_param('prompt_id'))){
            $body['with_prompt'] = $this->get_param('prompt_id');
        }

        $client = new HaiClient();
        $response = $client->client('generator/content', $body);

        $content = $this->find($response, 'content');
        $contentId = $response ? $this->find($response, 'content_id') : null;

        $content = trim($content, '"');

        return $response ? ['content' => $content, 'status' => 'done', 'content_id' => $contentId] : null;
    }

    public function get_supported_languages()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/content/languages');

        return $response ? $this->find($response, 'data') : null;
    }

    public function get_content_tones()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/content/tones');

        return $response ? $this->find($response, 'data') : null;
    }
}