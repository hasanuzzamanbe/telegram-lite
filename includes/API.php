<?php

namespace WPM_TelegramLite\includes;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Ajax Handler Class
 * @since 1.0.0
 */
class API
{
    private $token = '';
    private $chatId = '';
    private $parseMode = 'none';

    private $apiBase = 'https://api.telegram.org/bot';

    public function __construct($token = '', $chatId = '')
    {
        $this->token = $token;
        $this->chatId = $chatId;
    }

    public function setChatId($chatId)
    {
        $this->chatId = $chatId;
        return $this;
    }

    public function setToken($token)
    {
        $this->token = $token;
        return $this;
    }

    public function sendMessage($message, $parseMode = '')
    {
        if (!$message) {
            return new \WP_Error(300, 'Message is required', []);
        }

        if(!$this->token) {
            return new \WP_Error(300, 'Token is required', []);
        }

        if (!$parseMode) {
            $parseMode = $this->parseMode;
        }

        if($parseMode == 'none') {
            $message = $this->clearText($message);
        }

        return $this->sendRequest('sendMessage', [
            'chat_id'    => $this->chatId,
            'parse_mode' => $parseMode,
            'text'       => urlencode($message)
        ]);
    }

    public function getMe()
    {
        return $this->sendRequest('getMe');
    }

    private function getBaseUrl()
    {
        return $this->apiBase . $this->token . '/';
    }

    private function clearText($html)
    {
        return preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($html))) );
    }

    public function sendRequest($endPont, $args = [])
    {
        if(!$this->token) {
            return new \WP_Error(300, 'Token is required', []);
        }
        $url = add_query_arg($args, $this->getBaseUrl() . $endPont);

        $ch = curl_init();
        $optArray = array(
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true
        );
        curl_setopt_array($ch, $optArray);
        $result = curl_exec($ch);

        curl_close($ch);

        $result = \json_decode($result, true);

        if (isset($result['ok'])) {
            if(!empty($result['ok'])) {
                return $result;
            }
            return new \WP_Error($result['error_code'], $result['description'], $result);
        }

        return new \WP_Error(300, 'Unknown API error from Telegram', $result);
    }

}