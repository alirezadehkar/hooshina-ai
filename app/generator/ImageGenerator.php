<?php
namespace Hooshina\App\Generator;

class ImageGenerator extends GeneratorAbstract implements GeneratorInterface
{
    public function generate()
    {
        $client  = GeneratorClient::client('generate/image', [
            'subject' => $this->get_param('subject'),
            'size' => $this->get_param('size', '1024x1024'),
            'quality' => $this->get_param('quality', 'hd'),
            'tone' => $this->get_param('tone', 'creative'),
            'locale' => $this->get_param('locale', 'en'),
        ]);

        return $client ? $this->find_last($client, 'data') : null;
    }
}