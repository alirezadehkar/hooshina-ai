<?php
use HooshinaAi\App\Fields;
use HooshinaAi\App\Notice\Notice;
?>
<div class="tab-content">
    <?php 
    if(!$apiDeactivated){
        Notice::success(
            __('Hooshina API Successfully Activated', 'hooshina-ai'),
            [
                __('Currently, this connection is only used for the following two purposes:', 'hooshina-ai'),
                [
                    __('Automatically publishing content from Hooshina to your website', 'hooshina-ai'),
                    __('Retrieving your WordPress categories to align content structure', 'hooshina-ai')
                ],
                __('This communication is performed via a dedicated, secure API and does not grant any additional access to your site.', 'hooshina-ai'),
                __('You can rest assured that no actions beyond the two listed above will be performed.', 'hooshina-ai'),
                __('If any changes occur in access levels or API functionality, you will be notified through official plugin updates.', 'hooshina-ai')
            ],
        )->render(); 
    } else {
        Notice::error(
            __('Hooshina API Deactivated', 'hooshina-ai'),
            __('Automatic content publishing will not occur and Hooshina access to your site is currently blocked.', 'hooshina-ai'),
        )->render();
    }

    echo Fields::render_field(
        Fields::CHECKBOX,
        'api_deactivated',
        'api_deactivated',
        __('Deactivation Hooshina API', 'hooshina-ai'),
        $apiDeactivated,
        [],
        ['classes' => 'has-disabled']
    );

    $post_statuses = get_post_statuses();
    echo Fields::render_field(
        Fields::SELECT,
        'default_post_status',
        'default_post_status',
        __('Post Status', 'hooshina-ai'),
        $defPostStatus,
        $post_statuses,
        [
            'description' => __('It is recommended to set the status to draft and publish posts after thorough review and making the desired changes.', 'hooshina-ai')
        ]
    );

    echo Fields::render_field(
        Fields::SELECT,
        'default_author',
        'hai-default-author',
        __('Post Author', 'hooshina-ai'),
        $defAuthor,
        [],
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
    ?>

    <footer class="hai-options-foot">
        <button type="submit" class="hai-submit"><?php esc_html_e('Save Changes', 'hooshina-ai') ?></button>
    </footer>
</div>