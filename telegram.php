<?php

/**
 * Plugin Name: Telegram - Simply Notify on post publish
 * Plugin URI: http://wpminers.com/
 * Description: A Telegram Integration, automatically send new published post to telegram channel.
 * Author: Hasanuzzaman
 * Author URI: http://hasanuzzaman.com/
 * Version: 1.0.0
 * Text Domain: wpm-telegram-post
 * Domain Path: /language
 */

define('WPM_TELEGRAM_URL', plugin_dir_url(__FILE__));
define('WPM_TELEGRAM_DIR', plugin_dir_path(__FILE__));
define('WPM_TELEGRAM_VERSION', '1.0.5');

if (!defined('ABSPATH')) {
    exit;
}

class WPM_Telegram
{
    public function boot()
    {
        $this->loadClasses();
        $this->actions();
    }

    public function redirectSettings($plugin)
    {
        if ($plugin == plugin_basename( __FILE__ )) {
            exit(wp_redirect(admin_url('options-general.php?page=wpm_telegram_settings')));
        }
    }

    public function actions()
    {
        add_action('post_updated', [$this, 'publishPost'], 10, 3);
        add_action('admin_menu', [$this, 'addMenu']);
        add_action('admin_init', [$this, 'adminInit']);
        add_action('init', array($this, 'translate'));
        add_action('activated_plugin', [$this, 'redirectSettings']);
    }

    public function translate()
    {
        load_plugin_textdomain('wpm-telegram-post', false, dirname(plugin_basename(__FILE__)) . '/language');
    }

    public function publishPost($postId, $postAfter, $postBefore)
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

    public function addMenu()
    {
        add_options_page(
            __('Telegram - Post publish', 'wpm-telegram-post'),
            __('Telegram settings', 'wpm-telegram-post'),
            'manage_options',
            'wpm_telegram_settings',
            array($this, 'settings_page_render')
        );
    }

    public function settings_page_render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        ?>
        <div class="wrap">
            <h2><?php esc_html_e('Telegram - Simply Notify on post publish', 'wpm-telegram-post'); ?></h2>
            <form method="post" action="options.php">
                <?php settings_fields('wpm_telegram_settings'); ?>
                <?php do_settings_sections('wpm_telegram_settings'); ?>
                <p class="submit">
                    <input name="submit" type="submit" id="submit" class="button-primary" value="<?php esc_html_e('Save Changes', 'wpm-telegram-post'); ?>" />
                </p>
            </form>
        </div>
        <?php
    }



    public function adminInit()
    {
        add_settings_section(
            'renderInstructionSection',
            '',
            array($this, 'renderInstructionSection'),
            'wpm_telegram_settings'
        );

        add_settings_field(
            'wpm_telegram_bot_token',
            __('Telegram Bot Token', 'wpm-telegram-post'),
            array($this, 'renderSettingsInput'),
            'wpm_telegram_settings',
            'renderInstructionSection',
            array(
                'wpm_telegram_bot_token'
            )
        );

        add_settings_field(
            'wpm_telegram_chat_id',
            __('Telegram Chat ID', 'wpm-telegram-post'),
            array($this, 'renderSettingsInput'),
            'wpm_telegram_settings',
            'renderInstructionSection',
            array(
                'wpm_telegram_chat_id'
            )
        );

        register_setting('wpm_telegram_settings', 'wpm_telegram_bot_token', 'sanitize_text_field');
        register_setting('wpm_telegram_settings', 'wpm_telegram_chat_id', 'sanitize_text_field');
    }

    public function renderSettingsInput($args)
    {
        $option = get_option($args[0]);
        echo '<input style="width: 80%" type="text" id="' . $args[0] . '" name="' . $args[0] . '" value="' . $option . '" />';
    }

    public function renderInstructionSection()
    {
        ob_start();
        ?>
            <div>
                <h3><?php echo __('Instructions:', 'wpm-telegram-post'); ?></h3>
                <hr style="width:80%; margin: 0;">
                <p>
                    <?php echo __('1. Create a Telegram Bot sending <code>/newbot</code>  to', 'wpm-telegram-post'); ?>
                    <a href="https://telegram.me/BotFather" target="_blank">BotFather</a>
                    <?php echo __('And collect API Token.' , 'wpm-telegram-post'); ?>
                </p>
                <p><?php echo __('2. Create your Telegram channel', 'wpm-telegram-post'); ?></p>
                <p><?php echo __('3. Add the BOT on that channel as admin', 'wpm-telegram-post'); ?></p>
                <p><?php echo __('4. Send a test message on your channel and forward that', 'wpm-telegram-post'); ?>
                    <br/><?php echo __('message to', 'wpm-telegram-post'); ?> <a href="https://t.me/jsondumpbot" target="_blank">JsonDumpBot</a>
                </p>
                <p><?php echo __('5. It will response with a json just collect the "forward_from_chat" Id', 'wpm-telegram-post'); ?></p>
                </b><?php echo __('Example Token', 'wpm-telegram-post'); ?> <code>-1001xxxxxxx45</code> </p>
            </div>
        <?php
        echo ob_get_clean();
    }
}

(new WPM_Telegram())->boot();
