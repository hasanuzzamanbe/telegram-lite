<?php

/**
 * Plugin Name: Post On Telegram
 * Plugin URI: http://wpminers.com/
 * Description: A Telegram Integration when new post published.
 * Author: Hasanuzzaman Shamim
 * Author URI: http://hasanuzzaman.com/
 * Version: 1.0.0
 */

use WPM_Telegram\Classes\Bootstrap;

define('WPM_TELEGRAM_URL', plugin_dir_url(__FILE__));
define('WPM_TELEGRAM_DIR', plugin_dir_path(__FILE__));

define('WPM_TELEGRAM_VERSION', '1.0.5');

class WPM_Telegram {
    public function boot()
    {
        $this->loadClasses();
        $this->actions();
    }

    public function actions()
    {
        add_action('post_updated', [$this, 'publishPost'], 10, 3);
    }

    public function publishPost( $postId, $postAfter, $postBefore)
    {
        if ($postAfter->post_type != 'post') {
            return;
        }
        if ($postAfter->post_status == 'publish' && $postBefore->post_status != 'publish') {
            (new \WPM_Telegram\includes\Bootstrap())->notify($postId, $postAfter);
        }
    }

    public function loadClasses()
    {
        require WPM_TELEGRAM_DIR . 'includes/API.php';
        require WPM_TELEGRAM_DIR . 'includes/Bootstrap.php';
    }
}

(new WPM_Telegram())->boot();