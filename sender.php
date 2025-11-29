<?php

// Send message to Telegram
function wp_tg_send($text) {
    $prefix = trim(get_option('wp_tg_project_name'));
    if (!empty($prefix)) {
        $text = "[" . $prefix . "]\n" . $text;
    }

    $token = get_option('wp_tg_token');
    $chat  = get_option('wp_tg_chat_id');

    if (!$token || !$chat) return false;

    $url = "https://api.telegram.org/bot{$token}/sendMessage";

    $args = [
        'body' => [
            'chat_id' => $chat,
            'text'    => $text
        ],
        'timeout'  => 3,
        'blocking' => false
    ];

    $response = wp_remote_post($url, $args);

    // Log for stats
    wp_tg_log_event($text);

    return $response;
}