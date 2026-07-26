<?php

// Configuration for integration with the Ocean Maritime Academy (OMA) API.
// Docs: https://api.omabd.com/doc/  — GET-only, Bearer token, one-way sync (OMA -> GCSM).

return [
    // Base URL of the OMA API (no trailing slash).
    'base_url' => env('OMA_API_BASE_URL', 'https://api.omabd.com'),

    // Bearer token issued by OMA.
    'token' => env('OMA_API_TOKEN', ''),

    // HTTP timeout (seconds) and retry policy for transient failures.
    'timeout' => (int) env('OMA_API_TIMEOUT', 30),
    'retries' => (int) env('OMA_API_RETRIES', 3),
    'retry_delay_ms' => (int) env('OMA_API_RETRY_DELAY_MS', 1000),

    // Endpoints (relative to base_url).
    'endpoints' => [
        'students'        => '/students',          // ?page=
        'student_data'    => '/student-data',       // ?studentID=
        'new_students'    => '/new-students',        // ?admissionDate=dd-mm-yyyy
        'update_students' => '/update-students',     // ?date=dd-mm-yyyy
    ],
];
