<?php
/**
 * Application Configuration
 * PUB CampusMate
 */

return [
    'name' => $_ENV['APP_NAME'] ?? 'PUB CampusMate',
    'env' => $_ENV['APP_ENV'] ?? 'production',
    'debug' => filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN),
    'url' => $_ENV['APP_URL'] ?? 'http://localhost/pub-campusmate/public',
    'timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Dhaka',

    // Academic Policy Defaults (Configurable, avoiding hardcoded rules)
    'academic' => [
        'min_credits_per_semester' => (float)($_ENV['CREDIT_MIN_PER_SEMESTER'] ?? 9.0),
        'max_credits_per_semester' => (float)($_ENV['CREDIT_MAX_PER_SEMESTER'] ?? 18.0),
        'probation_max_credits' => (float)($_ENV['CREDIT_PROBATION_MAX'] ?? 12.0),
        'default_grading_scale' => '4.00',
        'passing_grade_point' => 2.00, // C grade
    ],

    // AI Helpdesk RAG Configuration
    'helpdesk' => [
        'driver' => $_ENV['AI_HELPDESK_DRIVER'] ?? 'mysql_fulltext',
        'model' => $_ENV['AI_HELPDESK_MODEL'] ?? 'gemini-1.5-flash',
        'chunk_size_tokens' => 500,
        'chunk_overlap_tokens' => 50,
        'top_k_results' => 5,
    ],

    // Storage Paths
    'paths' => [
        'base' => dirname(__DIR__),
        'app' => dirname(__DIR__) . '/app',
        'views' => dirname(__DIR__) . '/resources/views',
        'storage' => dirname(__DIR__) . '/storage',
        'logs' => dirname(__DIR__) . '/storage/logs',
        'documents' => dirname(__DIR__) . '/storage/documents',
        'cache' => dirname(__DIR__) . '/storage/cache',
    ]
];
