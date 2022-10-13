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
    protected $botToken = '5771707400:AAGTMTzzoBeG3IxFuJcZiqVRDrA-oxWAgro';
    protected $chatId = '-1001897340045';

    public function notify($postId, $post)
    {
        $permalink = get_permalink($postId);
        $title = $post->post_title;
        $author = get_the_author_meta('display_name', $post->post_author);
        $message = "New post published:\n <strong>$title<strong> \n by- $author \n";
        $message .= "<a href='$permalink'>$permalink</a>";
        $message .= "\n Post created at: $post->post_date \n\n";

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