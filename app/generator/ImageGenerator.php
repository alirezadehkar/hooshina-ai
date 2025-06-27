<?php
namespace HooshinaAi\App\Generator;

use HooshinaAi\App\Helper;
use HooshinaAi\App\Provider\HaiClient;
use HooshinaAi\App\Uploader;

class ImageGenerator extends GeneratorAbstract implements GeneratorInterface
{
    public function generate()
    {
        $client = new HaiClient();

        $params = [
            'subject' => $this->get_param('subject'),
            'size' => $this->get_param('size', '1024x1024'),
            'style' => $this->get_param('style', 'classical-realism'),
            'locale' => Helper::get_locale(),
        ];

        $imageUrl = $this->get_param('original_image');

        $route = Generator::TextToImage;

        if(!empty($imageUrl) && filter_var($imageUrl, FILTER_VALIDATE_URL)){
            $params['original_image'] = $imageUrl;
            $route = Generator::ProductImage;
            unset($params['subject']);
        }

        $params['model'] = $route;

        $response = $client->client('generator/image/queue/' . $route, $params);

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

    public function get_image_styles($model = null)
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/image/styles', ['model' => $model]);

        return $response ? $this->find($response, 'data') : null;
    }

    public function get_image_sizes()
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/image/sizes');

        return $response ? $this->find($response, 'data') : null;
    }

    public function get_image_status($content_id = null)
    {
        $client = new HaiClient(['method' => 'get']);
        $response = $client->client('generator/image/status', ['content_id' => $content_id]);

        $url = $response ? $this->find($response, 'media_url') : null;
        $status = $response ? $this->find($response, 'status') : null;

        if(filter_var($url, FILTER_VALIDATE_URL) && $status == 'done'){
            $uploadData = $this->uploadFile($url);
        }

        return ['content' => $url, 'id' => $uploadData['id'] ?? null, 'status' => $status];
    }
}