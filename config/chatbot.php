<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Read-only DB connection for chatbot SQL queries
    |--------------------------------------------------------------------------
    | Create a read-only MySQL user and point this to it.
    | Falls back to the default connection if the read-only one fails.
    */
    'read_connection' => env('DB_CHATBOT_CONNECTION', 'mysql_chatbot'),

    /*
    |--------------------------------------------------------------------------
    | Enable / disable the Text→SQL RAG pipeline
    |--------------------------------------------------------------------------
    */
    'enable_text_to_sql' => env('CHATBOT_ENABLE_TEXT_TO_SQL', true),

    /*
    |--------------------------------------------------------------------------
    | Allow ALL tables (dangerous — leave false in production)
    |--------------------------------------------------------------------------
    */
    'allow_all_tables' => env('CHATBOT_ALLOW_ALL_TABLES', false),

    /*
    |--------------------------------------------------------------------------
    | Whitelisted tables the chatbot may query (lowercase)
    |--------------------------------------------------------------------------
    | Sensitive tables (push_subscriptions, mailings) are intentionally omitted.
    | The 'users' table is included so the chatbot can look up basic profile
    | info, but sensitive columns (password, email, phone, etc.) are stripped
    | from the schema description sent to the LLM.
    */
    'allowed_tables' => array_map('strtolower', [
        'users',
        'medicines',
        'medicine_schedules',
        'medicine_reminders',
        'medicine_logs',
        'health_metrics',
        'environments',
        'environment_metrics',
        'symptoms',
        'user_symptoms',
        'diseases',
        'disease_symptoms',
        'user_diseases',
        'uploads',
        'posts',
        'comments',
        'notifications',
    ]),
];
