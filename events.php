<?php

// Block direct access
if (!defined('ABSPATH')) exit;

// ===== COMMENTS =====

// New approved comment
// Check if comment notifications enabled
if (!get_option('wp_tg_enable_comments')) return;
add_action('comment_post', function ($comment_id, $comment_approved) {

    // Only approved comments
    if ((int) $comment_approved !== 1) {
        return;
    }

    if (!function_exists('wp_tg_send')) {
        return;
    }

    $comment = get_comment($comment_id);
    if (!$comment) {
        return;
    }

    $post       = get_post($comment->comment_post_ID);
    $post_title = $post ? $post->post_title : '';

    $msg  = "📝 New approved comment\n\n";
    $msg .= "Post: " . $post_title . "\n";
    $msg .= "Author: " . $comment->comment_author . "\n";
    $msg .= "Content:\n" . $comment->comment_content;

    if (function_exists('wp_tg_stats_inc')) {
        wp_tg_stats_inc('comments_approved');
    }

    wp_tg_send($msg);

}, 10, 2);

// New comment waiting for moderation
// Check if comment notifications enabled
if (!get_option('wp_tg_enable_comments')) return;
add_action('wp_insert_comment', function ($comment_id, $comment_object) {

    if (!function_exists('wp_tg_send')) {
        return;
    }

    // Only pending comments
    if ((int) $comment_object->comment_approved !== 0) {
        return;
    }

    $post       = get_post($comment_object->comment_post_ID);
    $post_title = $post ? $post->post_title : '';

    $msg  = "🕓 New comment awaiting moderation\n\n";
    $msg .= "Post: " . $post_title . "\n";
    $msg .= "Author: " . $comment_object->comment_author . "\n";
    $msg .= "Content:\n" . $comment_object->comment_content;

    if (function_exists('wp_tg_stats_inc')) {
        wp_tg_stats_inc('comments_pending');
    }

    wp_tg_send($msg);

}, 10, 2);


// ===== USERS =====

// New user registration
// Check if user notifications enabled
if (!get_option('wp_tg_enable_users')) return;
add_action('user_register', function ($user_id) {

    if (!function_exists('wp_tg_send')) {
        return;
    }

    $user = get_userdata($user_id);
    if (!$user) {
        return;
    }

    $msg  = "👤 New user registered\n\n";
    $msg .= "Username: " . $user->user_login . "\n";
    $msg .= "Email: " . $user->user_email;

    if (function_exists('wp_tg_stats_inc')) {
        wp_tg_stats_inc('users_registered');
    }

    wp_tg_send($msg);

}, 10, 1);

// Failed login (security alert)
// Check if user notifications enabled
if (!get_option('wp_tg_enable_users')) return;
add_action('wp_login_failed', function ($username) {

    if (!function_exists('wp_tg_send')) {
        return;
    }

    $msg  = "🚨 Failed login attempt\n\n";
    $msg .= "Username: " . $username . "\n";
    $msg .= "IP: " . (isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : 'unknown');

    if (function_exists('wp_tg_stats_inc')) {
        wp_tg_stats_inc('login_failed');
    }

    wp_tg_send($msg);

}, 10, 1);


// ===== SYSTEM =====

// Theme / plugin / core updates
// Check if system notifications enabled
if (!get_option('wp_tg_enable_system')) return;
add_action('upgrader_process_complete', function ($upgrader, $hook_extra) {

    if (!function_exists('wp_tg_send')) {
        return;
    }

    if (empty($hook_extra['type']) || empty($hook_extra['action'])) {
        return;
    }

    $type   = $hook_extra['type'];   // plugin, theme, core
    $action = $hook_extra['action']; // update, install

    $msg  = "⚙️ Update completed\n\n";
    $msg .= "Type: " . $type . "\n";
    $msg .= "Action: " . $action;

    if (function_exists('wp_tg_stats_inc')) {
        wp_tg_stats_inc('updates_done');
    }

    wp_tg_send($msg);

}, 10, 2);

// PHP fatal errors to Telegram (optional, controlled by option)
add_action('init', function () {

    if (!function_exists('wp_tg_send')) {
        return;
    }

    // Option will be added in settings later, default off
    $enabled = get_option('wp_tg_notify_php_errors');
    if (empty($enabled)) {
        return;
    }

    register_shutdown_function(function () {

        if (!function_exists('wp_tg_send')) {
            return;
        }

        $error = error_get_last();
        if (!$error) {
            return;
        }

        $fatal_types = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR];
        if (!in_array($error['type'], $fatal_types, true)) {
            return;
        }

        $msg  = "💥 PHP fatal error\n\n";
        $msg .= "Message: " . $error['message'] . "\n";
        $msg .= "File: " . $error['file'] . "\n";
        $msg .= "Line: " . $error['line'];

        if (function_exists('wp_tg_stats_inc')) {
            wp_tg_stats_inc('php_errors');
        }

        wp_tg_send($msg);
    });
});


// ===== FORMS =====

// Hook forms after all plugins are loaded
add_action('plugins_loaded', function () {

    // Contact Form 7
    if (defined('WPCF7_VERSION')) {
        add_action('wpcf7_before_send_mail', function ($contact_form) {
            if (!get_option('wp_tg_enable_forms')) return;

            if (!function_exists('wp_tg_send')) {
                return;
            }

            $title      = $contact_form->title();
            $submission = class_exists('WPCF7_Submission') ? WPCF7_Submission::get_instance() : null;
            $data       = $submission ? $submission->get_posted_data() : [];

            $msg  = "📩 New Contact Form 7 submission\n\n";
            $msg .= "Form: " . $title . "\n\n";

            if (!empty($data) && is_array($data)) {
                foreach ($data as $key => $value) {
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $msg .= $key . ": " . $value . "\n";
                }
            }

            if (function_exists('wp_tg_stats_inc')) {
                wp_tg_stats_inc('forms_cf7');
            }

            wp_tg_send($msg);

        }, 10, 1);
    }

    // WPForms
    if (defined('WPFORMS_VERSION')) {
        add_action('wpforms_process_complete', function ($fields, $entry, $form_data, $entry_id) {
            if (!get_option('wp_tg_enable_forms')) return;

            if (!function_exists('wp_tg_send')) {
                return;
            }

            $msg  = "📨 New WPForms submission\n\n";
            $msg .= "Form: " . (isset($form_data['settings']['form_title']) ? $form_data['settings']['form_title'] : 'Unknown') . "\n\n";

            if (!empty($fields) && is_array($fields)) {
                foreach ($fields as $field) {
                    $label = isset($field['name']) ? $field['name'] : (isset($field['label']) ? $field['label'] : 'Field');
                    $value = isset($field['value']) ? $field['value'] : '';
                    if (is_array($value)) {
                        $value = implode(', ', $value);
                    }
                    $msg .= $label . ": " . $value . "\n";
                }
            }

            if (function_exists('wp_tg_stats_inc')) {
                wp_tg_stats_inc('forms_wpforms');
            }

            wp_tg_send($msg);

        }, 10, 4);
    }

});