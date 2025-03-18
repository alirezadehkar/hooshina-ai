<?php
namespace HooshinaAi\App;

use HooshinaAi\App\Generator\Generator;
use HooshinaAi\App\Generator\GeneratorHelper;
use HooshinaAi\App\Provider\Account;

class Ajax
{
    public static function handle_check_is_connected()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        if (!AiService::use()->isConnected())
            wp_send_json_error();

        wp_send_json_success();
    }

    public static function handle_connect_to_api()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $connectUrl = AiService::use()->getConnectUrl();

        if (is_wp_error($connectUrl) || empty($connectUrl))
            wp_send_json_error();

        wp_send_json_success(['redirect' => $connectUrl]);
    }

    public static function handle_disconnect_to_api()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $auth = AiService::use()->getConnectionAuth();

        if (empty($auth))
            wp_send_json_error();

        $revoke = AiService::use()->revokeConnection($auth);
        if (is_wp_error($revoke) || empty($revoke))
            wp_send_json_error();

        wp_send_json_success();
    }

    public static function handle_generate_content()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');
        
        $account = new Account;

        if(!$account->balance_sufficient()){
            wp_send_json_error([
                'msg' => __('Balance in Hooshina account is insufficient.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        $subject = isset($_POST['subject']) ? sanitize_textarea_field(wp_unslash($_POST['subject'])) : null;
        $type = isset($_POST['type']) ? sanitize_text_field(wp_unslash($_POST['type'])) : null;

        $size = isset($_POST['size']) ? sanitize_text_field(wp_unslash($_POST['size'])) : null;
        $style = isset($_POST['style']) ? sanitize_text_field(wp_unslash($_POST['style'])) : null;
        $image_url = isset($_POST['original_image']) ? sanitize_text_field(wp_unslash($_POST['original_image'])) : null;

        $lang = isset($_POST['lang']) ? sanitize_text_field(wp_unslash($_POST['lang'])) : null;
        $tone = isset($_POST['tone']) ? sanitize_text_field(wp_unslash($_POST['tone'])) : null;

        $buttonAction = isset($_POST['buttonAction']) ? sanitize_text_field(wp_unslash($_POST['buttonAction'])) : null;

        $generator = new Generator();

        if($type == 'image'){
            $generate = $generator->image()->set_params([
                'subject' => $subject,
                'size' => $size,
                'style' => $style,
                'original_image' => $image_url
            ])->generate();
        } else {
            $generate = $generator->content()->set_params([
                'subject' => $subject,
                'lang' => $lang,
                'tone' => $tone,
                'prompt_id' => GeneratorHelper::get_prompt_id($buttonAction)
            ])->generate();
        }

        $content = isset($generate['content']) ? $generate['content'] : null;

        if (empty($content)){
            wp_send_json_error([
                'msg' => __('Invalid response.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        if($type != 'image'){
            $content = Helper::convertMarkdown($content);
        }

        wp_send_json_success([
            'content' => $content, 
            'id' => (isset($generate['id']) ? $generate['id'] : null), 
            'status' => (isset($generate['status']) ? $generate['status'] : null),
            'content_id' => (isset($generate['content_id']) ? $generate['content_id'] : null),
            'status' => 'done'
        ]);
    }

    public static function handle_check_image_status()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $contentId = isset($_POST['content_id']) ? sanitize_text_field(wp_unslash($_POST['content_id'])) : null;

        $generator = new Generator();

        $imageStatus = $generator->image()->get_image_status($contentId);

        if (empty($imageStatus))
            wp_send_json_error();

        wp_send_json_success([
            'content' => $imageStatus['content'], 
            'id' => (isset($imageStatus['id']) ? $imageStatus['id'] : null), 
            'status' => (isset($imageStatus['status']) ? $imageStatus['status'] : null),
        ]);
    }

    public static function handle_check_account_balance()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $account = new Account;
        $sufficient = $account->balance_sufficient();

        if(!$sufficient){
            wp_send_json_error(['msg' => __('There is insufficient account balance.', 'hooshina-ai')]);
        }

        wp_send_json_success(['msg' => __('The account balance is sufficient.', 'hooshina-ai')]);
    }

    public static function handle_show_account_balance()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $account = new Account;
        $balance = $account->get_balance();

        if(!is_array($balance)){
            wp_send_json_error(['msg' => __('Account balance is unavailable.', 'hooshina-ai')]);
        }

        if(!$account->balance_sufficient()){
            wp_send_json_error(['htmlValue' => __('There is insufficient account balance.', 'hooshina-ai')]);
        }

        $isLtr = Helper::get_locale() == 'en';
        $balanceValue = $isLtr ? $balance['USD'] : $balance['IRT'];

        // translators: %s is the balance amount.
        $htmlValue = sprintf(__('Balance: %s', 'hooshina-ai'), sprintf("%s %s", number_format($balanceValue, ($isLtr ? 3 : 0)), ($isLtr ? __('Usd', 'hooshina-ai') : __('Toman', 'hooshina-ai'))));

        wp_send_json_success(['htmlValue' => $htmlValue]);
    }

    public static function handle_generate_comment_text()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $account = new Account;

        if(!$account->balance_sufficient()){
            wp_send_json_error([
                'msg' => __('Balance in Hooshina account is insufficient.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        if(empty($_POST['objectId'])){
            wp_send_json_error([
                'msg' => __('Invalid request.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        $objectId = sanitize_text_field(wp_unslash($_POST['objectId']));

        $isReviewsSummary = isset($_POST['isReviewsSummary']) && $_POST['isReviewsSummary'] == 'true';

        $text = '';

        if($isReviewsSummary){
            $productId = $objectId;

            $product = wc_get_product($productId);

            if(!$product){
                wp_send_json_error([
                    'msg' => __('Product not found.', 'hooshina-ai'),
                    'status' => 'error'
                ]);
            }

            $reviews = get_comments(array(
                'post_id' => $productId,
                'status' => 'approve',
                'number' => 70,
                'meta_query' => array(
                    array(
                        'key' => 'rating',
                        'value' => array(3, 4, 5),
                        'compare' => 'IN',
                        'type' => 'NUMERIC'
                    )
                ),
                'orderby' => 'meta_value_num',
                'meta_key' => 'rating',
                'order' => 'DESC'
            ));
            
            if(empty($reviews)){
                wp_send_json_error([
                    'msg' => __('No comments with a suitable rating were found to generate a summary.', 'hooshina-ai'),
                    'status' => 'error'
                ]);
            }
            
            $reviewsText = '';

            $productTitle = $product->get_title();
            $reviewsText .= "Product Title: {$productTitle}\n\n";

            foreach ($reviews as $review) {
                $reviewsText .= "Review: {$review->comment_content}\n\n";
            }
            
            $text = $reviewsText;
            $promptId = Generator::PRODUCT_REVIEWS_SUMMARY_PROMPT_ID;
        } else {
            $comment = get_comment($objectId);
            if (!$comment) {
                wp_send_json_error([
                    'msg' => __('Comment not found.', 'hooshina-ai'),
                    'status' => 'error'
                ]);
            }
    
            $postTitle = get_the_title($comment->comment_post_ID);

            $text = '';
            if($postTitle){
                $text = "Post Title: {$postTitle}\n\n";
            }

            $text .= 'Comment Text: ' . $comment->comment_content;
            $promptId = Generator::COMMENT_PROMPT_ID;
        }

        $generator = new Generator();

        $generate = $generator->content()->set_params([
            'subject' => $text,
            'prompt_id' => $promptId
        ])->generate();

        if (empty($generate)){
            wp_send_json_error([
                'msg' => __('An error occurred, please try again.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        wp_send_json_success([
            'content' => $generate['content'],
            'status' => 'success'
        ]);
    }

    public static function handle_submit_product_reviews_summary()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $objectId = !empty($_POST['objectId']) ? sanitize_text_field(wp_unslash($_POST['objectId'])) : null;
        $summary = !empty($_POST['answer']) ? sanitize_textarea_field(wp_unslash($_POST['answer'])) : null;

        if (empty($objectId) || empty($summary)) {
            wp_send_json_error([
                'msg' => __('Invalid input data.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }


        $product = wc_get_product($objectId);

        if(!$product){
            wp_send_json_error([
                'msg' => __('Product not found.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        if(!PostMeta::update_product_reviews_summary($objectId, Helper::convertMarkdown($summary))){
            wp_send_json_error([
                'msg' => __('An error occurred, please try again.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        wp_send_json_success([
            'msg' => __('Summary of product reviews recorded.', 'hooshina-ai'),
            'status' => 'success'
        ]);
    }

    public static function handle_submit_comment_answer()
    {
        check_ajax_referer('hooshina_ai_nonce', 'nonce');

        $objectId = !empty($_POST['objectId']) ? sanitize_text_field(wp_unslash($_POST['objectId'])) : null;
        $commentAnswer = !empty($_POST['answer']) ? sanitize_textarea_field(wp_unslash($_POST['answer'])) : null;

        if (empty($objectId) || empty($commentAnswer)) {
            wp_send_json_error([
                'msg' => __('Invalid input data.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }
    
        $parentComment = get_comment($objectId);
        if (!$parentComment) {
            wp_send_json_error([
                'msg' => __('Parent comment not found.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }
    
        $commentData = array(
            'comment_post_ID' => $parentComment->comment_post_ID,
            'comment_content' => Helper::convertMarkdown($commentAnswer),
            'comment_parent' => $objectId,
            'comment_approved' => 1,
            'user_id' => get_current_user_id(), 
        );
    
        $newCommentId = wp_insert_comment($commentData);
    
        if (!$newCommentId) {
            wp_send_json_error([
                'msg' => __('Failed to submit comment answer.', 'hooshina-ai'),
                'status' => 'error'
            ]);
        }

        update_comment_meta($newCommentId, 'hai_generated', true);
            
        wp_send_json_success([
            'msg' => __('Comment answer submitted successfully.', 'hooshina-ai'),
            'status' => 'success'
        ]);
    }
}