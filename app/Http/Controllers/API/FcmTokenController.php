<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Models\FcmToken;


class FcmTokenController extends BaseController
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'fcm_token' => 'required|string',
        ]);

        // تخزين التوكن في قاعدة البيانات
        FcmToken::updateOrCreate(
            ['user_id' => $validated['user_id']],
            ['fcm_token' => $validated['fcm_token']]
        );

         return $this->sendResponse([], 'FCM Token stored successfully',200);
    }

    public function getToken($userId)
    {
        // استرجاع التوكن من قاعدة البيانات
        $token = FcmToken::where('user_id', $userId)->latest()->first();

        if ($token) {
           return $this->sendResponse(['fcm_token' => $token->fcm_token], 'Token retrieved successfully',200);
        }

       return $this->sendError('Token not found', [],404);
    }
}
