<?php

return [
    'defaults' => [
        'open_before_minutes' => (int) env('LIVE_ROOM_OPEN_BEFORE_MINUTES', 15),
        'reminder_minutes' => (int) env('LIVE_ROOM_REMINDER_MINUTES', 10),
        'student_join_requires_moderator_started' => (bool) env('LIVE_ROOM_REQUIRE_MODERATOR_START', true),
    ],

    'platforms' => [
        'internal' => [
            'label' => 'Live noi bo',
            'supports_embed' => false,
            'web_sdk_enabled' => false,
            'default_launch_mode' => 'internal',
        ],
        'zoom' => [
            'label' => 'Zoom',
            'supports_embed' => (bool) env('LIVE_ROOM_ZOOM_SUPPORTS_EMBED', false),
            'web_sdk_enabled' => (bool) env('LIVE_ROOM_ZOOM_WEB_SDK_ENABLED', false),
            'client_id' => env('ZOOM_CLIENT_ID'),
            'account_id' => env('ZOOM_ACCOUNT_ID'),
            'default_launch_mode' => env('LIVE_ROOM_ZOOM_LAUNCH_MODE', 'redirect'),
        ],
        'google_meet' => [
            'label' => 'Google Meet',
            'supports_embed' => false,
            'web_sdk_enabled' => false,
            'default_launch_mode' => env('LIVE_ROOM_GOOGLE_MEET_LAUNCH_MODE', 'redirect'),
        ],
    ],
];
