<?php

namespace App\Http\Controllers\Classroom;

use App\Http\Controllers\Controller;
use App\Models\LearningSession;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

class RoomAccessController extends Controller
{
    use AuthorizesRequests;

    public function navigation(string $sessionId): JsonResponse
    {
        $session = LearningSession::where('session_id', $sessionId)->firstOrFail();
        $this->authorize('viewNavigation', $session);

        $googleMapsUrl = sprintf(
            'https://www.google.com/maps/dir/?api=1&destination=%f,%f',
            $session->latitude,
            $session->longitude
        );

        return response()->json([
            'message' => 'Detail rute navigasi berhasil dimuat.',
            'data' => [
                'session_id' => $session->session_id,
                'address' => $session->onsite_address ?? $session->student_address,
                'landmark' => $session->address_landmark,
                'latitude' => $session->latitude,
                'longitude' => $session->longitude,
                'google_maps_url' => $googleMapsUrl,
            ]
        ], 200);
    }
}
