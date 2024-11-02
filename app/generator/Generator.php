<?php
namespace Hooshina\App\Generator;

class Generator
{
    public function content()
    {
        return new ContentGenerator();
    }

    public function image()
    {
        return new ImageGenerator();
    }
}