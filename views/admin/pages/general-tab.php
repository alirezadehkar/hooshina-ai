<?php
use HooshinaAi\App\Helper;
?>
<div class="hai-subtabs">
    <ul>
        <li class="<?php echo $subtab === 'content-generation' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(add_query_arg('subtab', 'content-generation')); ?>">
                <?php esc_html_e('Content Generation', 'hooshina-ai'); ?>
            </a>
        </li>
        <li class="<?php echo $subtab === 'auto-content-generation' ? 'active' : ''; ?>">
            <a href="<?php echo esc_url(add_query_arg('subtab', 'auto-content-generation')); ?>">
                <?php esc_html_e('Automatic Content Generation', 'hooshina-ai'); ?>
            </a>
        </li>
    </ul>
</div>

<div class="tab-content">
    <?php if ($subtab === 'content-generation'): ?>
        <?php 
        echo Helper::render_field(
            'select',
            'default_content_tone',
            'default_content_tone',
            __('Default Content Tone', 'hooshina-ai'),
            $contentTones,
            $defContentTone,
        );

        echo Helper::render_field(
            'select',
            'default_image_style',
            'default_image_style',
            __('Default Image Style', 'hooshina-ai'),
            $imageStyles,
            $defImageStyle,
        );

        echo Helper::render_field(
            'select',
            'default_product_image_style',
            'default_product_image_style',
            __('Default Product Style', 'hooshina-ai'),
            $productImageStyles,
            $defProductImageStyle,
        );

        echo Helper::render_field(
            'select',
            'default_image_size',
            'default_image_size',
            __('Default Image Size', 'hooshina-ai'),
            $imageSizes,
            $defImageSize
        );
        ?>
    <?php else: ?>
        <?php 
        $post_types = get_post_types(['public' => true], 'objects');
        $post_types_options = [];
        if ($post_types) {
            foreach ($post_types as $post_type) {
                $post_types_options[$post_type->name] = $post_type->label;
            }
        }

        echo Helper::render_field(
            'select',
            'default_post_type',
            'default_post_type',
            __('Default Post Type', 'hooshina-ai'),
            $post_types_options,
            $defPostType,
        );

        $post_statuses = get_post_statuses();
        echo Helper::render_field(
            'select',
            'default_post_status',
            'default_post_status',
            __('Default Post Status', 'hooshina-ai'),
            $post_statuses,
            $defPostStatus,
            [
                'description' => __('It is recommended to set the status to draft and publish posts after thorough review and making the desired changes.', 'hooshina-ai')
            ]
        );

        echo Helper::render_field(
            'select',
            'default_author',
            'hai-default-author',
            __('Default Author', 'hooshina-ai'),
            [],
            $defAuthor,
            [
                'data' => $defAuthor ? [
                    'selected' => json_encode([
                        'id' => $defAuthor,
                        'text' => get_the_author_meta('display_name', $defAuthor),
                        'role' => wp_roles()->get_names()[get_userdata($defAuthor)->roles[0]]
                    ])
                ] : []
            ]
        );

        $taxonomies = get_taxonomies(['public' => true], 'objects');
        $taxonomies_options = [];
        if ($taxonomies) {
            foreach ($taxonomies as $taxonomy) {
                $taxonomies_options[$taxonomy->name] = $taxonomy->label;
            }
        }

        echo Helper::render_field(
            'select',
            'default_taxonomy',
            'hai-default-taxonomy',
            __('Default Taxonomy', 'hooshina-ai'),
            $taxonomies_options,
            $defTaxonomy,
            [
                'description_if' => __('To select a category, you must first select the desired taxonomy.', 'hooshina-ai'),
                'description_if_condition' => empty($defTaxonomy),
                'data' => [
                    'target' => 'hai-default-category',
                    'action' => 'load_terms'
                ]
            ]
        );

        echo Helper::render_field(
            'select',
            'default_category',
            'hai-default-category',
            __('Default Category', 'hooshina-ai'),
            [],
            $defCategory,
        );
        ?>
    <?php endif; ?>

    <footer class="hai-options-foot">
        <button type="submit" class="hai-submit"><?php esc_html_e('Save Changes', 'hooshina-ai') ?></button>
    </footer>
</div>