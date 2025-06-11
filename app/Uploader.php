<?php
namespace HooshinaAi\App;

use HooshinaAi\App\Logger;
use stdClass;

class Uploader
{
    private $fileUrl;
    private $postId;

    public function __construct($fileUrl, $postId = null)
    {
        $this->fileUrl = $fileUrl;
        $this->postId = $postId;
    }

    public function upload()
    {
        $data = new stdClass;
        $params = [];

        $file = $this->fileUrl;

        try{
            add_filter('upload_mimes', function ($existing_mimes){
                $existing_mimes['svg'] = 'image/svg+xml';
                $existing_mimes['webp'] = 'image/webp';
    
                return $existing_mimes;
            });
    
            if ( ! function_exists( 'media_handle_sideload' ) ) {
                require_once( ABSPATH . 'wp-admin/includes/media.php' );
                require_once( ABSPATH . 'wp-admin/includes/file.php' );
                require_once( ABSPATH . 'wp-admin/includes/image.php' );
            }
    
            if ( ! empty( $file )) {
                preg_match( '/[^\?]+\.(jpe?g|jpe|gif|png|svg|mp3|mp4|webm|webp)\b/i', $file, $matches );
                $file_array = array();
                $file_array['name'] = basename( $matches[0] );
    
                $file_array['tmp_name'] = download_url($file);
    
                if ( is_wp_error( $file_array['tmp_name'] ) ) {
                    return $file_array['tmp_name'];
                }
    
                $id = media_handle_sideload($file_array, $this->postId, null, $params);
    
                if ( is_wp_error( $id ) ) {
                    wp_delete_file( $file_array['tmp_name'] );
                    return $id;
                }
    
                $meta = wp_get_attachment_metadata($id);
                $data->attachment_id = $id;
                $data->url = wp_get_attachment_url($id);
                $data->thumbnail_url = wp_get_attachment_thumb_url($id);
                $data->height = $meta['height'] ?? null;
                $data->width = $meta['width'] ?? null;
                $data->id = $id;
            }
    
            return (array) $data;
        } catch(\Throwable $th){
            Logger::error($th);
            return [];
        }
    }
}