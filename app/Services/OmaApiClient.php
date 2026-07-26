<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

/**
 * Thin client for the OMA API (https://api.omabd.com/doc).
 * GET-only, Bearer auth, one-way sync (OMA -> GCSM).
 */
class OmaApiClient
{
    protected function request(): PendingRequest
    {
        return Http::baseUrl(rtrim(config('oma.base_url'), '/'))
            ->withToken(config('oma.token'))
            ->acceptJson()
            ->timeout(config('oma.timeout', 30))
            ->retry(config('oma.retries', 3), config('oma.retry_delay_ms', 1000));
    }

    /** Students admitted on a date (dd-mm-yyyy) -> array of student objects. */
    public function newStudents(string $admissionDate): array
    {
        $res = $this->request()->get(config('oma.endpoints.new_students'), [
            'admissionDate' => $admissionDate,
        ]);
        $res->throw();
        return $this->normalise($res->json());
    }

    /** Students changed on a date (dd-mm-yyyy). */
    public function updatedStudents(string $date): array
    {
        $res = $this->request()->get(config('oma.endpoints.update_students'), [
            'date' => $date,
        ]);
        $res->throw();
        return $this->normalise($res->json());
    }

    /** Single student full record by studentID. */
    public function studentData(string $studentId): ?array
    {
        $res = $this->request()->get(config('oma.endpoints.student_data'), [
            'studentID' => $studentId,
        ]);
        $res->throw();
        $data = $this->normalise($res->json());
        return $data[0] ?? null;
    }

    /** Paged full list. */
    public function students(int $page = 1): array
    {
        $res = $this->request()->get(config('oma.endpoints.students'), ['page' => $page]);
        $res->throw();
        return $this->normalise($res->json());
    }

    /** Accept either a single student object or a list; always return a list. */
    protected function normalise(mixed $json): array
    {
        if (! is_array($json)) return [];
        if (array_key_exists('studentID', $json)) return [$json]; // single object
        return array_values(array_filter($json, 'is_array'));
    }
}
