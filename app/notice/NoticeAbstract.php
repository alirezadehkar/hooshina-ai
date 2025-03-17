<?php
namespace HooshinaAi\App\Notice;

abstract class NoticeAbstract {
    /**
     * Message to be displayed
     *
     * @var string
     */
    protected string $message;

    /**
     *
     * Message type to be displayed
     *
     * @var string
     */
    protected string $type = 'success';

    /**
     * Initialize class.
     *
     * @param string $message Message to be displayed
     */
    public function __construct( string $message ) {
        $this->message = $message;

        add_action('admin_notices', array($this, 'render'));
    }

    /**
     * Displays warning on the admin screen.
     *
     * @return void
     */
    public function render() {
        printf(
            '<div class="notice notice-%s is-dismissible"><p>%s</p></div>',
            esc_attr($this->type), 
            esc_html($this->message)
        );
    }
}