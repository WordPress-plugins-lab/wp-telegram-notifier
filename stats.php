<?php

// Increment numeric counters for statistics
function wp_tg_stats_inc($key) {

    $counters = get_option('wp_tg_stats_counters');

    if (!is_array($counters)) {
        $counters = [];
    }

    if (!isset($counters[$key])) {
        $counters[$key] = 0;
    }

    $counters[$key]++;

    update_option('wp_tg_stats_counters', $counters);
}

// Log events to WP option (simple)
function wp_tg_log_event($message) {

    $stats = get_option('wp_tg_stats');

    if (!is_array($stats)) {
        $stats = [];
    }

    $stats[] = [
        'time' => current_time('mysql'),
        'msg'  => $message
    ];

    update_option('wp_tg_stats', $stats);
}

// Admin page to show stats
add_action('admin_menu', function () {
    add_submenu_page(
        'options-general.php',
        'Notifier Stats',
        'Stats',
        'manage_options',
        'wp-tg-notifier-stats',
        'wp_tg_show_stats_page'
    );
});

function wp_tg_show_stats_page() {

    $stats = get_option('wp_tg_stats');

    ?>
    <div class="wrap">
        <h2>Telegram Notifier — Stats</h2>

        <?php
        // Build counters summary from real counters
        $counters_map = [
            'Approved comments'     => 'comments_approved',
            'Pending comments'      => 'comments_pending',
            'New users'             => 'users_registered',
            'Failed logins'         => 'login_failed',
            'System updates'        => 'updates_done',
            'PHP fatal errors'      => 'php_errors',
            'CF7 submissions'       => 'forms_cf7',
            'WPForms submissions'   => 'forms_wpforms'
        ];

        $counters = get_option('wp_tg_stats_counters');
        if (!is_array($counters)) {
            $counters = [];
        }
        ?>

        <div style="margin-top:15px; padding:10px; background:#fff; border-left:4px solid #0073aa; max-width:900px;">
            <h3>Summary</h3>
            <ul>
                <?php foreach ($counters_map as $label => $key): ?>
                    <li>
                        <strong><?php echo $label; ?>:</strong>
                        <?php echo isset($counters[$key]) ? (int)$counters[$key] : 0; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <table class="widefat fixed striped" style="max-width:900px; margin-top:20px;">
            <thead>
                <tr>
                    <th style="width:180px;">Time</th>
                    <th>Message</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                if (is_array($stats) && count($stats) > 0):
                    foreach ($stats as $row): ?>
                        <tr>
                            <td><?php echo esc_html($row['time']); ?></td>
                            <td>
                                <pre style="white-space:pre-wrap; margin:0;">
<?php echo esc_html($row['msg']); ?>
                                </pre>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="2">No events logged yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}