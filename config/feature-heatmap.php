<?php

return [

    // Enable or disable the entire package (can be turned off in production)
    'enabled' => env('FEATURE_HEATMAP_ENABLED', true),

    // Whether to dispatch tracking via a queue or write directly to the DB
    // Set to false for immediate DB writes without needing 'queue:work' running
    'use_queue' => env('FEATURE_HEATMAP_USE_QUEUE', false),
    'queue_name' => env('FEATURE_HEATMAP_QUEUE', 'default'),

    // When true, the TrackFeatureUsage middleware is automatically pushed onto the specified
    // middleware groups ('web', 'api', etc.) by the ServiceProvider — no manual route setup required.
    // Set to false if you want to apply the 'track.feature' middleware manually.
    'auto_track' => env('FEATURE_HEATMAP_AUTO_TRACK', true),

    // Middleware groups to automatically attach tracking to
    'auto_track_groups' => ['web', 'api'],

    // How many days to retain raw logs before they are pruned by the aggregate command
    'raw_log_retention_days' => 14,

    // Route/URI patterns to exclude from tracking (regex)
    'excluded_patterns' => [
        '^_debugbar',
        '^horizon',
        '^telescope',
        '^feature-heatmap', // Exclude the package's own dashboard from tracking
    ],

    // Route prefix and middleware for the dashboard
    'route_prefix' => 'feature-heatmap',
    'route_middleware' => ['web'],

    // Gate that controls who can view the dashboard
    // Define it in AuthServiceProvider: Gate::define('viewFeatureHeatmap', ...)
    'authorization_gate' => 'viewFeatureHeatmap',

    // Package internal authentication settings (login modal via env credentials)
    'auth_enabled' => env('FEATURE_HEATMAP_AUTH_ENABLED', env('FEATURE_HEATMAP_USERNAME') !== null),
    'username'     => env('FEATURE_HEATMAP_USERNAME', 'admin'),
    'password'     => env('FEATURE_HEATMAP_PASSWORD', 'secret'),

    // Auth user model columns used to display user identity in the Users tab
    'user_name_column'  => env('FEATURE_HEATMAP_USER_NAME_COL', 'name'),
    'user_email_column' => env('FEATURE_HEATMAP_USER_EMAIL_COL', 'email'),
];
