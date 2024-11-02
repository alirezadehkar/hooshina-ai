<?php
namespace Hooshina\App\Generator;

class ContentGenerator extends GeneratorAbstract implements GeneratorInterface
{
    public function generate()
    {
        $client  = GeneratorClient::client('generate/content', [
            'subject' => $this->get_param('subject'),
            'tone' => $this->get_param('tone', 'neutral'),
            'lang' => $this->get_param('lang', 'english'),
            'locale' => $this->get_param('locale', 'en'),
        ]);

        return $client ? $this->find($client, 'content') : null;
    }
}