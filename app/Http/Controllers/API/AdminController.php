<?php

namespace App\Http\Controllers\API;


use App\Http\Resources\UserResource;
use App\Models\Apartment;
use App\Models\Booking;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminController extends BaseController
{
    
      //Get pending users for admin approval
     
    public function pendingUsers(Request $request)
{
    $users = User::where('status', 'pending')->paginate(20);

    return $this->sendPaginatedResponse($users, 'pending users retrieved');
}

    
     // Approve a user
     
    public function approveUser(Request $request, User $user)
    {
        // Check if user is already approved
       if ($user->status === 'active') {
            // Return error 400 (Bad Request) because client is trying an invalid action
            return $this->sendError('user already approved', []);
        }

        try {
            // Update user status to "active"
            $user->update(['status' => 'active']);

            // Return success 200 (OK) with updated user data and success message
            return $this->sendResponse(new UserResource($user), 'user approved');

        } catch (Exception $e) {
            // Return error 500 (Internal Server Error) if update process fails
            return $this->sendError('user approval failed', ['error' => $e->getMessage()]);
        }
    }

    

    
      
     // Reject a user
     
    public function rejectUser(Request $request, User $user)
{
    // Check if action is invalid (if user is already rejected or active)
    if ($user->status === 'rejected' || $user->status === 'active') {
        // Return error 400 (Bad Request) because client is trying an invalid action
        return $this->sendError('invalid action', []);
    }

    try {
        // Delete user images from storage
        if ($user->id_image) {
            Storage::disk('public')->delete($user->id_image);
        }
        
        if ($user->profile_image) {
            Storage::disk('public')->delete($user->profile_image);
        }

        // Delete user
        $user->delete();

        // Return success 200 (OK) with confirmation message
        return $this->sendResponse([], 'user rejected');

    } catch (Exception $e) {
        // Return error 500 (Internal Server Error) if deletion process fails
        return $this->sendError('user rejection failed', ['error' => $e->getMessage()]);
    }
}
    
      // Get statistics for admin dashboard
     
    public function statistics(Request $request)
    {
        // Get statistics for admin dashboard
        try {
            $stats = [
                'total_users' => User::count(),
                'pending_users' => User::where('status', 'pending')->count(),
                'active_users' => User::where('status', 'active')->count(),
                'total_apartments' => Apartment::count(),
                'available_apartments' => Apartment::where('status', 'available')->count(),
                'total_bookings' => Booking::count(),
                'pending_bookings' => Booking::where('status', 'pending')->count(),
                'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
                'total_revenue' => Booking::where('status', 'confirmed')->sum('total_price')
            ];

            // Return success 200 (OK) with statistics data and success message
            return $this->sendResponse($stats, 'statistics retrieved');

        } catch (Exception $e) {
            // Return error 500 (Internal Server Error) if statistics retrieval fails
            return $this->sendError('statistics retrieval failed', ['error' => $e->getMessage()]);
        }
    }
}