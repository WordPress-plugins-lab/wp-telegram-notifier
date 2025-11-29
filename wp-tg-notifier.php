<?php
/*
Plugin Name: WP Telegram Notifier
Description: Simple Telegram notifications for WordPress.
Version: 1.1
Author: R145j7
*/

// Block direct access
if (!defined('ABSPATH')) exit;

// Load plugin modules
require_once plugin_dir_path(__FILE__) . 'settings.php';
require_once plugin_dir_path(__FILE__) . 'sender.php';
require_once plugin_dir_path(__FILE__) . 'events.php';
require_once plugin_dir_path(__FILE__) . 'stats.php';

// Activate hook
register_activation_hook(__FILE__, function () {
    add_option('wp_tg_token', '');
    add_option('wp_tg_chat_id', '');
    add_option('wp_tg_project_name', '');
    add_option('wp_tg_enable_comments', 1);
    add_option('wp_tg_enable_users', 1);
    add_option('wp_tg_enable_system', 1);
    add_option('wp_tg_notify_php_errors', 0);
    add_option('wp_tg_enable_forms', 1);
    add_option('wp_tg_stats', []);
});