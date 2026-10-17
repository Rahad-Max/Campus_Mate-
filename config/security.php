<?php
/**
 * Security & Role Configuration
 * PUB CampusMate
 */

return [
    'session' => [
        'name' => $_ENV['SESSION_COOKIE_NAME'] ?? 'pub_campusmate_session',
        'lifetime' => (int)($_ENV['SESSION_LIFETIME'] ?? 120), // minutes
        'path' => '/',
        'domain' => '',
        'secure' => filter_var($_ENV['SESSION_SECURE_COOKIE'] ?? false, FILTER_VALIDATE_BOOLEAN),
        'httponly' => true,
        'samesite' => 'Lax'
    ],

    'roles' => [
        'student' => [
            'name' => 'Student',
            'dashboard_route' => '/dashboard/student',
            'permissions' => [
                'view_courses',
                'view_offered_courses',
                'request_course_registration',
                'view_own_registration',
                'view_own_timetable',
                'view_own_academic_history',
                'view_notices',
                'view_faculty_directory',
                'use_ai_helpdesk'
            ]
        ],
        'teacher' => [
            'name' => 'Teacher',
            'dashboard_route' => '/dashboard/teacher',
            'permissions' => [
                'view_courses',
                'view_assigned_sections',
                'view_class_schedule',
                'request_makeup_class',
                'view_student_roster',
                'view_notices',
                'update_faculty_profile',
                'use_ai_helpdesk'
            ]
        ],
        'academic_advisor' => [
            'name' => 'Academic Advisor',
            'dashboard_route' => '/dashboard/advisor',
            'permissions' => [
                'view_courses',
                'view_assigned_advisees',
                'view_advisee_history',
                'approve_registration_requests',
                'reject_registration_requests',
                'view_timetable',
                'view_notices',
                'use_ai_helpdesk'
            ]
        ],
        'department_coordinator' => [
            'name' => 'Department Coordinator',
            'dashboard_route' => '/dashboard/coordinator',
            'permissions' => [
                'manage_curriculum',
                'manage_courses',
                'manage_course_offerings',
                'manage_sections',
                'assign_teachers',
                'manage_class_schedules',
                'resolve_schedule_conflicts',
                'finalize_registration',
                'manage_exams',
                'post_notices',
                'upload_academic_documents',
                'view_audit_logs'
            ]
        ]
    ],

    'csrf' => [
        'token_key' => '_csrf_token',
        'header_name' => 'X-CSRF-TOKEN',
        'expire_after_seconds' => 7200
    ],

    'uploads' => [
        'allowed_document_types' => ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'],
        'allowed_image_types' => ['image/jpeg', 'image/png', 'image/webp'],
        'max_file_size_mb' => 15,
        'storage_path' => dirname(__DIR__) . '/storage/documents'
    ]
];
