<?php
namespace HooshinaAi\App\Generator;

class Generator
{
    const TextToImage = 'text-to-image';
    const ProductImage = 'product-image';

    const COMMENT_PROMPT_ID = 19;
    const PRODUCT_REVIEW_PROMPT_ID = 5;
    const TITLE_PROMPT_ID = 11;
    const PRODUCT_DESCRIPTION_PROMPT_ID = 4;
    const META_DESCRIPTION_PROMPT_ID = 10;
    const KEYWORD_PROMPT_ID = 18;
    const PRODUCT_REVIEWS_SUMMARY_PROMPT_ID = 20;

    public function content()
    {
        return new ContentGenerator();
    }

    public function image()
    {
        return new ImageGenerator();
    }
}