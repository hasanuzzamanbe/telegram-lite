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
        $this->addSettings();
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

    public function addSettings()
    {
        add_action('admin_init', function() {
            add_settings_section(
                'my_settings_section',
                'Post On Telegram',
                array($this, 'renderInstructionSection'), // No callback
                'general'
            );

            add_settings_field(
                'wpm_telegram_token',
                'Telegram Bot Token',
                array($this, 'renderSettingsInput'),
                'general',
                'my_settings_section',
                array(
                    'wpm_telegram_token'
                )
            );

            add_settings_field(
                'wpm_telegram_chat_id',
                'Telegram Chat Id',
                array($this, 'renderSettingsInput'),
                'general',
                'my_settings_section',
                array(
                    'wpm_telegram_chat_id'
                )
            );
            register_setting('general','wpm_telegram_token', 'esc_attr');
            register_setting('general','wpm_telegram_chat_id', 'esc_attr');
        });

    }

    public function renderSettingsInput($args)
    {
        $option = get_option($args[0]);
        echo '<input type="text" id="'. $args[0] .'" name="'. $args[0] .'" value="' . $option . '" />';
    }

    public function renderInstructionSection()
    {

        ob_start();
        ?>
            <div>
                <h3>Instructions:</h3>
                <p>1. Create a Telegram Bot using <a href="https://telegram.me/BotFather" target="_blank">BotFather</a> And collect API.</p>
                <p>2. Create your Telegram channel</p>
                <p>3. Add the BOT on that channel as admin</p>
                <p>4. Send a test message on your channel and forward that <br/> message to <a href="https://t.me/jsondumpbot" target="_blank">JsonDumpBot</a></p>
                <p>5. It will response with a json then collect the "forward_from_chat" Id,</b> Example Token <code>-1001xxxxxxx45</code> </p>
            </div>
        <?php

        echo ob_get_clean();
    }
}

(new WPM_Telegram())->boot();