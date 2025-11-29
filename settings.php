<?php

// Validation: prevent saving empty required fields
function wp_tg_required_field($value, $option) {
    $old = get_option($option);
    if (empty($value)) {
        return $old;
    }
    return $value;
}

// Create settings page
add_action('admin_menu', function () {
    add_options_page(
        'Telegram Notifier',
        'Telegram Notifier',
        'manage_options',
        'wp-tg-notifier',
        'wp_tg_notifier_settings_page'
    );
});

// Render settings page
function wp_tg_notifier_settings_page() {
    ?>
    <div class="wrap">
        <h2>Telegram Notifier</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('wp_tg_notifier_options');
            do_settings_sections('wp_tg_notifier');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings fields
add_action('admin_init', function () {

    register_setting('wp_tg_notifier_options', 'wp_tg_token', [
        'sanitize_callback' => function($v){ return wp_tg_required_field($v, 'wp_tg_token'); }
    ]);
    register_setting('wp_tg_notifier_options', 'wp_tg_chat_id', [
        'sanitize_callback' => function($v){ return wp_tg_required_field($v, 'wp_tg_chat_id'); }
    ]);

    add_settings_section(
        'wp_tg_main_section',
        'Telegram Settings',
        null,
        'wp_tg_notifier'
    );

    add_settings_field(
        'wp_tg_token',
        'Bot Token',
        function () {
            echo '<input type="text" name="wp_tg_token" value="' . esc_attr(get_option('wp_tg_token')) . '" style="width:300px;">';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    add_settings_field(
        'wp_tg_chat_id',
        'Chat ID',
        function () {
            echo '<input type="text" name="wp_tg_chat_id" value="' . esc_attr(get_option('wp_tg_chat_id')) . '" style="width:300px;">';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    // Project / Site name prefix
    register_setting('wp_tg_notifier_options', 'wp_tg_project_name', [
        'sanitize_callback' => function($v){ return wp_tg_required_field($v, 'wp_tg_project_name'); }
    ]);
    add_settings_field(
        'wp_tg_project_name',
        'Project name prefix',
        function () {
            echo '<input type="text" name="wp_tg_project_name" value="' . esc_attr(get_option('wp_tg_project_name')) . '" style="width:300px;" placeholder="My Website">';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    // Enable comment notifications
    register_setting('wp_tg_notifier_options', 'wp_tg_enable_comments');
    add_settings_field(
        'wp_tg_enable_comments',
        'Comments notifications',
        function () {
            $val = get_option('wp_tg_enable_comments');
            echo '<label><input type="checkbox" name="wp_tg_enable_comments" value="1" ' . checked(1, $val, false) . '> Enable</label>';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    // Enable user notifications
    register_setting('wp_tg_notifier_options', 'wp_tg_enable_users');
    add_settings_field(
        'wp_tg_enable_users',
        'User notifications',
        function () {
            $val = get_option('wp_tg_enable_users');
            echo '<label><input type="checkbox" name="wp_tg_enable_users" value="1" ' . checked(1, $val, false) . '> Enable</label>';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    // Enable system notifications
    register_setting('wp_tg_notifier_options', 'wp_tg_enable_system');
    add_settings_field(
        'wp_tg_enable_system',
        'System updates notifications',
        function () {
            $val = get_option('wp_tg_enable_system');
            echo '<label><input type="checkbox" name="wp_tg_enable_system" value="1" ' . checked(1, $val, false) . '> Enable</label>';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    // Enable PHP error notifications
    register_setting('wp_tg_notifier_options', 'wp_tg_notify_php_errors');
    add_settings_field(
        'wp_tg_notify_php_errors',
        'PHP fatal errors',
        function () {
            $val = get_option('wp_tg_notify_php_errors');
            echo '<label><input type="checkbox" name="wp_tg_notify_php_errors" value="1" ' . checked(1, $val, false) . '> Enable</label>';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );

    // Enable forms notifications (CF7 / WPForms)
    register_setting('wp_tg_notifier_options', 'wp_tg_enable_forms');
    add_settings_field(
        'wp_tg_enable_forms',
        'Forms notifications (CF7, WPForms)',
        function () {
            $val = get_option('wp_tg_enable_forms');
            echo '<label><input type="checkbox" name="wp_tg_enable_forms" value="1" ' . checked(1, $val, false) . '> Enable</label>';
        },
        'wp_tg_notifier',
        'wp_tg_main_section'
    );
});