<?php
namespace HooshinaAi\App\Notice;

use HooshinaAi\App\Helper;

abstract class NoticeAbstract {
    protected string $message;
    protected $content;
    protected $dismissable = false;

    protected string $type = 'success';

    /**
     * Initialize class.
     *
     * @param string $message Message to be displayed
     */
    public function __construct( string $message, $content = null ) 
    {
        $this->message = $message;
        $this->content = $content;
    }

    /**
     * Displays warning on the admin screen.
     *
     * @return void
     */
    public function render() 
    {
        $icon = '';
        switch ($this->type) {
            case 'success':
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /> <path d="M9 12l2 2l4 -4" /> </svg>';
                break;
            case 'warning':
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M12.802 2.165l5.575 2.389c.48 .206 .863 .589 1.07 1.07l2.388 5.574c.22 .512 .22 1.092 0 1.604l-2.389 5.575c-.206 .48 -.589 .863 -1.07 1.07l-5.574 2.388c-.512 .22 -1.092 .22 -1.604 0l-5.575 -2.389a2.036 2.036 0 0 1 -1.07 -1.07l-2.388 -5.574a2.036 2.036 0 0 1 0 -1.604l2.389 -5.575c.206 -.48 .589 -.863 1.07 -1.07l5.574 -2.388a2.036 2.036 0 0 1 1.604 0z" /> <path d="M12 8v4" /> <path d="M12 16h.01" /> </svg>';
                break;
            case 'error':
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M12 9v4" /> <path d="M10.363 3.591l-8.106 13.534a1.914 1.914 0 0 0 1.636 2.871h16.214a1.914 1.914 0 0 0 1.636 -2.87l-8.106 -13.536a1.914 1.914 0 0 0 -3.274 0z" /> <path d="M12 16h.01" /> </svg>';
                break;
            case 'info':
            default:
                $icon = '<svg xmlns="http://www.w3.org/2000/svg" width="23" height="23" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M12 9h.01" /> <path d="M11 12h1v4h1" /> <path d="M12 3c7.2 0 9 1.8 9 9s-1.8 9 -9 9s-9 -1.8 -9 -9s1.8 -9 9 -9z" /> </svg>';
                break;
        }

        $html = sprintf(
            '<div class="hai-notice-wrap hai-notice--%s"><div class="hai-notice">',
            esc_attr($this->type)
        );
        
        $html .= '<div class="hai-notice--icon-wrap">';
        $html .= sprintf(
            '<div class="hai-notice--icon">%s</div>',
            Helper::allow_svg_tags($icon)
        );
        $html .= sprintf('<h3 class="hai-notice--title">%s</h3>', esc_html($this->message));
        $html .= '</div>';

        $content = $this->content;
        if (!empty($content)) {
            $html .= '<div class="hai-notice--content">';
            if (is_array($content)) {
                foreach ($content as $item) {
                    if (is_array($item)) {
                        $html .= '<ol class="hai-notice--list">';
                        foreach ($item as $list_item) {
                            if (is_string($list_item)) {
                                $html .= sprintf('<li class="hai-notice--list-item">%s</li>', esc_html($list_item));
                            }
                        }
                        $html .= '</ol>';
                    } elseif (is_string($item)) {
                        $html .= sprintf('<p class="hai-notice--message">%s</p>', esc_html($item));
                    }
                }
            } else {
                $html .= sprintf('<div class="hai-notice--message">%s</div>', esc_html($content));
            }
            $html .= '</div>';
        }

        if($this->dismissable){
            $html .= '<span class="hai-dismiss-notice"><svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" > <path d="M18 6l-12 12" /> <path d="M6 6l12 12" /> </svg></span>';
        }

        $html .= '</div></div>';
        echo $html;
    }

    public function dismissable()
    {
        $this->dismissable = true;
        return $this;
    }

    public function adminNotice()
    {
        add_action('admin_notices', function(){
            echo '<div class="wrap hai-admin-notice-wrap">';
            $this->render();
             echo '</div>';
        }, 1);
    }
}