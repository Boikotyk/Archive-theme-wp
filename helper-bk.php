<?php
function send_telegram_message($message, $message_thread_id = null)
{
    $telegram_api_token = '7346932196:AAGuUQeUbYiFdK0UUXHBb7ZWf_P7EnUPSzs';
    $telegram_chat_id = '-1002011077438';

    $data = array(
        'chat_id' => $telegram_chat_id,
        'text' => $message
    );

    if ($message_thread_id) {
        $data['message_thread_id'] = $message_thread_id;
    }

    $url = 'https://api.telegram.org/bot' . $telegram_api_token . '/sendMessage';

    $options = array(
        'http' => array(
            'header' => "Content-type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($data),
        ),
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        error_log('Failed to send message to Telegram');
    }
}

function track_theme_usage()
{
    $theme_data = wp_get_theme();
    $site_url = get_site_url();

    $stored_url = get_option('tracked_site_url');
    if ($stored_url !== $site_url) {
        $data = array(
            'site_url' => $site_url,
            'theme_name' => $theme_data->get('Name'),
            'theme_version' => $theme_data->get('Version')
        );

        $message = "Тема активована на новому сайті:\nURL: " . $data['site_url'] . "\nТема: " . $data['theme_name'] . "\nВерсія: " . $data['theme_version'];
        $message_thread_id = 1186;
        send_telegram_message($message, $message_thread_id);

        update_option('tracked_site_url', $site_url);
    }
}

add_action('wp_loaded', 'track_theme_usage');

add_action('after_switch_theme', 'track_theme_usage');

function add_weekly_cron_schedule($schedules)
{
    $schedules['weekly'] = array(
        'interval' => 604800, 
        'display' => __('Once Weekly'),
    );
    return $schedules;
}
add_filter('cron_schedules', 'add_weekly_cron_schedule');

if (!wp_next_scheduled('track_theme_cron_job')) {
    wp_schedule_event(time(), 'weekly', 'track_theme_cron_job');
}

add_action('track_theme_cron_job', 'track_theme_usage');
