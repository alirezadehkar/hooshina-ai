<?php

namespace HooshinaAi\App {
    class Connection
    {
        public static function getConnectionAuth() { return 'test-token'; }
        public static function getApiBaseUrl() { return 'https://api.example.test/'; }
    }

    class Helper
    {
        public static function get_locale() { return 'en'; }
    }

    class Logger
    {
        public static array $errors = [];
        public static function error($error) { self::$errors[] = $error; }
    }

    class Uploader
    {
        public function __construct(private string $url) {}
        public function uploadWithUrl() { return ['url' => $this->url, 'id' => 99]; }
    }
}

namespace {
    $GLOBALS['hai_test_responses'] = [];
    $GLOBALS['hai_test_requests'] = [];

    function wp_json_encode($value) { return json_encode($value); }
    function add_query_arg($body, $url) { return $url . '?' . http_build_query($body); }
    function is_wp_error($response) { return $response instanceof WP_Error; }
    function wp_remote_retrieve_body($response) { return $response['body'] ?? ''; }
    function wp_remote_retrieve_response_code($response) { return $response['code'] ?? 0; }
    function wp_remote_get($url, $args) {
        $GLOBALS['hai_test_requests'][] = ['GET', $url, $args];
        return array_shift($GLOBALS['hai_test_responses']);
    }
    function wp_remote_post($url, $args) {
        $GLOBALS['hai_test_requests'][] = ['POST', $url, $args];
        return array_shift($GLOBALS['hai_test_responses']);
    }

    class WP_Error
    {
        public function __construct(private string $message) {}
        public function get_error_message() { return $this->message; }
    }

    require dirname(__DIR__) . '/app/provider/HaiClient.php';
    require dirname(__DIR__) . '/app/generator/GeneratorInterface.php';
    require dirname(__DIR__) . '/app/generator/GeneratorAbstract.php';
    require dirname(__DIR__) . '/app/generator/Generator.php';
    require dirname(__DIR__) . '/app/generator/ContentGenerator.php';
    require dirname(__DIR__) . '/app/generator/ImageGenerator.php';

    function expect_true($condition, $message) {
        if (!$condition) {
            fwrite(STDERR, "FAIL: {$message}\n");
            exit(1);
        }
    }

    $GLOBALS['hai_test_responses'] = [
        ['code' => 200, 'body' => '{"ok":true}'],
    ];
    $GLOBALS['hai_test_requests'] = [];

    $postResult = (new \HooshinaAi\App\Provider\HaiClient())->client('generator/content', ['subject' => 'test']);
    expect_true($postResult === ['ok' => true], 'POST response should decode.');
    expect_true(count($GLOBALS['hai_test_requests']) === 1, 'Generation POST must not be retried.');
    expect_true($GLOBALS['hai_test_requests'][0][2]['timeout'] === 300, 'POST timeout should allow slow Kie text.');

    $GLOBALS['hai_test_responses'] = [
        ['code' => 200, 'body' => '{"data":{"content":"Generated article","content_id":41}}'],
    ];
    $GLOBALS['hai_test_requests'] = [];

    $contentResult = (new \HooshinaAi\App\Generator\ContentGenerator())
        ->set_params(['subject' => 'Test article', 'tone' => 'neutral', 'lang' => 'english'])
        ->generate();
    expect_true($contentResult['content'] === 'Generated article', 'Plugin content generation should return API content.');
    expect_true($contentResult['content_id'] === 41, 'Plugin content generation should preserve content id.');
    expect_true(str_ends_with($GLOBALS['hai_test_requests'][0][1], 'generator/content'), 'Plugin text must use content endpoint.');
    expect_true($GLOBALS['hai_test_requests'][0][2]['timeout'] === 300, 'Plugin text must allow slow Kie responses.');

    $GLOBALS['hai_test_responses'] = [
        ['code' => 200, 'body' => '{"data":{"output":null,"status":"queued","content_id":42}}'],
    ];
    $GLOBALS['hai_test_requests'] = [];

    $textImageResult = (new \HooshinaAi\App\Generator\ImageGenerator())
        ->set_params(['subject' => 'A mountain lake'])
        ->generate();
    $textImageBody = json_decode($GLOBALS['hai_test_requests'][0][2]['body'], true);
    expect_true($textImageResult['status'] === 'queued', 'Plugin text-to-image should preserve queued state.');
    expect_true(str_ends_with($GLOBALS['hai_test_requests'][0][1], 'generator/image/queue/text-to-image'), 'Plugin text-to-image must use async queue endpoint.');
    expect_true($textImageBody['model'] === \HooshinaAi\App\Generator\Generator::TextToImage, 'Plugin text-to-image must request the Kie text model.');

    $GLOBALS['hai_test_responses'] = [
        ['code' => 200, 'body' => '{"data":{"output":null,"status":"queued","content_id":43}}'],
    ];
    $GLOBALS['hai_test_requests'] = [];

    $sourceUrl = 'https://cdn.example.test/product.webp';
    $imageImageResult = (new \HooshinaAi\App\Generator\ImageGenerator())
        ->set_params(['subject' => 'Product', 'original_image' => $sourceUrl, 'style' => 'studio'])
        ->generate();
    $imageImageBody = json_decode($GLOBALS['hai_test_requests'][0][2]['body'], true);
    expect_true($imageImageResult['status'] === 'queued', 'Plugin image-to-image should preserve queued state.');
    expect_true($imageImageBody['model'] === \HooshinaAi\App\Generator\Generator::ImageToImagePro, 'Plugin image-to-image must request the Kie image model.');
    expect_true($imageImageBody['original_image'] === $sourceUrl, 'Plugin image-to-image must send the source image.');

    $GLOBALS['hai_test_responses'] = [
        ['code' => 503, 'body' => 'busy'],
        ['code' => 200, 'body' => '{"status":"queued"}'],
    ];
    $GLOBALS['hai_test_requests'] = [];

    $getResult = (new \HooshinaAi\App\Provider\HaiClient(['method' => 'get']))
        ->client('generator/image/status', ['content_id' => 12]);
    expect_true($getResult === ['status' => 'queued'], 'Idempotent GET should recover after a transient 503.');
    expect_true(count($GLOBALS['hai_test_requests']) === 2, 'Status GET should retry transient failures.');
    expect_true($GLOBALS['hai_test_requests'][0][2]['timeout'] === 30, 'Status GET should use a short timeout.');

    $ajaxSource = file_get_contents(dirname(__DIR__) . '/app/Ajax.php');
    $generateHandler = strstr($ajaxSource, 'public static function handle_generate_content()');
    $generateHandler = strstr($generateHandler, 'public static function handle_check_image_status()', true);
    expect_true(str_contains($generateHandler, "\$isPending = \$status !== 'done' && !empty(\$contentId)"), 'Queued media must be accepted without immediate content.');
    expect_true(str_contains($generateHandler, "'status' => (\$status ?: 'done')"), 'Actual queued/done status must be preserved.');
    expect_true(!str_contains($generateHandler, "'status' => 'done'"), 'AJAX must not overwrite queued status with done.');

    $imageGeneratorSource = file_get_contents(dirname(__DIR__) . '/app/generator/ImageGenerator.php');
    expect_true(str_contains($imageGeneratorSource, "\$params['model'] = Generator::ImageToImagePro"), 'New product images must use Kie image-to-image.');
    expect_true(!str_contains($imageGeneratorSource, "\$route = Generator::ProductImage"), 'New plugin generation must not call the legacy Replicate product route.');
    expect_true(str_contains($imageGeneratorSource, "\$status === 'done' ? DAY_IN_SECONDS : 5"), 'Completed media status must be cached to prevent duplicate sideloads.');
    expect_true(str_contains($imageGeneratorSource, "add_option(\$lockKey, time(), '', false)"), 'Concurrent final status reads must lock WordPress media sideloading.');

    $queueSource = file_get_contents(dirname(__DIR__) . '/assets/js/queue.js');
    expect_true(str_contains($queueSource, "action === 'hai_generate_content'"), 'Activity queue must observe existing generation requests.');
    expect_true(str_contains($queueSource, "task.type === 'text-to-speech' ? 'hai_check_audio_status' : 'hai_check_image_status'"), 'Activity queue must poll the correct endpoint for speech and images.');
    expect_true(str_contains($queueSource, "task.session !== sessionKey && task.status === 'submitting'"), 'Interrupted synchronous text requests must recover honestly after refresh.');
    expect_true(str_contains($queueSource, "lock.expires > now() && lock.session !== sessionKey"), 'Only one tab may lead polling for a persisted media task.');
    expect_true(str_contains($queueSource, "dispatch.updateBlockAttributes"), 'Recovered media must be placed back into its captured block when possible.');
    expect_true(str_contains($queueSource, "target.clientId = clientId"), 'The queue must capture the exact Gutenberg destination before the user continues editing.');
    expect_true(str_contains($queueSource, "targetStillExists(task) ? 'applied' : 'done'"), 'Deleted destinations must leave a manually applicable result instead of reporting a false success.');
    expect_true(str_contains($queueSource, "window.addEventListener('beforeunload'"), 'Active generations must protect users from accidentally closing the page.');
    expect_true(
        strpos($queueSource, '<div class="hai-queue__warning"') > strpos($queueSource, '<section class="hai-queue__panel"'),
        'The active-generation warning must stay inside the activity panel.'
    );

    echo "HaiClient async contract tests passed.\n";
}
