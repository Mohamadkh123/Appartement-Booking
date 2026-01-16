<?php

namespace App\Http\Controllers\API;


use Illuminate\Http\Request;
use App\Models\FcmToken;


class FcmTokenController extends BaseController
{

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user->update([
            'fcm_token' => $request->fcm_token
        ]);

        return $this->sendResponse([], 'FCM Token saved successfully in users table',200);
    }
    public function getToken($userId)
    {
        $token = FcmToken::where('user_id', $userId)->latest()->first();

        if ($token) {
           return $this->sendResponse(['fcm_token' => $token->fcm_token], 'Token retrieved successfully',200);
        }

       return $this->sendError('Token not found', [],404);
    }
}