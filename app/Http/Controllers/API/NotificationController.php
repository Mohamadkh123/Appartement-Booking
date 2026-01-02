<?php

namespace App\Http\Controllers\API;

use App\Http\Resources\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        $notifications = $user->notifications()->orderBy('created_at', 'desc')->paginate(20);
        
        return $this->sendPaginatedResponse($notifications, 'Notifications retrieved',200);
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, Notification $notification)
    {
        $user = $request->user();
        
        // Check if notification belongs to user
        if ($notification->user_id !== $user->id) {
            return $this->sendError('Unauthorized', [],403);
        }
        
        $notification->markAsRead();
        
        return $this->sendResponse(new NotificationResource($notification), 'Notification marked as read',200);
    }

    
}