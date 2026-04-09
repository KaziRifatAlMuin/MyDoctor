<?php

return [
    // connection name to use for read-only SQL execution
    'read_connection' => env('DB_CHATBOT_CONNECTION', 'mysql_chatbot'),

    // whether to allow the Text->SQL pipeline. Toggle via env or config.
    'enable_text_to_sql' => env('CHATBOT_ENABLE_TEXT_TO_SQL', true),

    // If true, generated SQL may reference any table. Set to false in production.
    'allow_all_tables' => env('CHATBOT_ALLOW_ALL_TABLES', false),

    // Whitelisted tables that the chatbot is allowed to query (lowercase).
    // Adjust to your privacy needs. Only used when allow_all_tables is false.
    'allowed_tables' => array_map('strtolower', [
        'users',
        'posts',
        'comments',
        'diseases',
        'symptoms',
        'medicine_logs',
        'medicine_reminders',
        'health_metrics',
    ]),
];
