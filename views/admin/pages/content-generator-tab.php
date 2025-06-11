<?php
use HooshinaAi\App\Fields;
?>
<div class="tab-content">
    <?php 
    echo Fields::render_field(
        Fields::SELECT,
        'default_content_tone',
        'default_content_tone',
        __('Default Content Tone', 'hooshina-ai'),
        $defContentTone,
        $contentTones,
    );

    echo Fields::render_field(
        Fields::SELECT,
        'default_content_lang',
        'default_content_lang',
        __('Default Content Lang', 'hooshina-ai'),
        $defContentLang,
        $languages,
    );

    echo Fields::render_field(
        Fields::SELECT,
        'default_image_style',
        'default_image_style',
        __('Default Image Style', 'hooshina-ai'),
        $defImageStyle,
        $imageStyles,
    );

    echo Fields::render_field(
        Fields::SELECT,
        'default_product_image_style',
        'default_product_image_style',
        __('Default Product Image Style', 'hooshina-ai'),
        $defProductImageStyle,
        $productImageStyles,
    );

    echo Fields::render_field(
        Fields::SELECT,
        'default_image_size',
        'default_image_size',
        __('Default Image Size', 'hooshina-ai'),
        $defImageSize,
        $imageSizes,
    );
    ?>

    <footer class="hai-options-foot">
        <button type="submit" class="hai-submit"><?php esc_html_e('Save Changes', 'hooshina-ai') ?></button>
    </footer>
</div>