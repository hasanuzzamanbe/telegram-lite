<?php

namespace WPM_Telegram\includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax Handler Class
 * @since 1.0.0
 */
class Bootstrap
{
    protected $botToken;
    protected $chatId;

    public function __construct()
    {
        $this->botToken = sanitize_text_field(get_option('wpm_telegram_token'));
        $this->chatId = sanitize_text_field(get_option('wpm_telegram_chat_id'));
    }

    public function notify($postId, $post)
    {
        if (!$post->post_date) {
            return;
        }

        $permalink = get_permalink($postId);
        $title = $post->post_title;
        $author = get_the_author_meta('display_name', $post->post_author);
        $message = "-- New Post Published: --\n <strong>$title<strong> \n ✍️ by- $author \n";
        $message .= "<a href='$permalink'>$permalink</a>";
        $message .= "\n At: $post->post_date \n\n";

        $api = $this->getApiClient($this->botToken, $this->chatId);
        $response = $api->sendMessage($message);

        if (is_wp_error($response)) {
            do_action('wpm_exception_log', $response->get_error_message());
            return;
        }
        do_action('wpm_telegram_notify', $response);
    }

    protected function getApiClient($token, $chatId = '')
    {
        return new API(
            $token,
            $chatId
        );
    }

}