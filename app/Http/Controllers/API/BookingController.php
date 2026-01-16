<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Models\Apartment;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class BookingController extends BaseController
{
    
     
    public function index(Request $request)
    {
        $query = Booking::with(['user', 'apartment']);

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $bookings = $query->paginate(10);

        return $this->sendPaginatedResponse($bookings, 'Bookings retrieved', 200);
      }

    
     
    public function store(StoreBookingRequest $request)
    {
        if (!$request->user()->isTenant()) {
            return $this->sendError('Only tenants can book apartments', [],403);
        }

        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);
            $totalPrice = (float)($days * $apartment->price);

            $tenantWallet = $user->wallet;
            if (!$tenantWallet) {
                return $this->sendError('You do not have money in your wallet. ', [],400);
            }

            $currentBalance = (float)($tenantWallet->balance ?? 0);
            if ($currentBalance < $totalPrice) {
                return $this->sendError("Insufficient balance in your wallet. Your balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalPrice, 2), [],400);
            }

            $booking = $user->bookings()->create([
                'apartment_id' => $request->apartment_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
                'total_price' => $totalPrice
            ]);

          

            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking created. Pending owner approval.',200);
        } catch (Exception $e) {
            return $this->sendError('Booking creation failed', ['error' => $e->getMessage()],500);
        }
    }

    
     
     
    public function show(Booking $booking)
    {
        $booking->load(['user', 'apartment']);
        return $this->sendResponse(new BookingResource($booking), 'Booking retrieved',200);
          }

    
     
    public function update(Request $request, Booking $booking)
    {
        $user = $request->user();

        if (!$user->isAdmin() && $user->id !== $booking->apartment->owner_id) {
            return $this->sendError('Unauthorized',[], 403);
        }
        
        if ($booking->status === 'cancelled') {
            return $this->sendError('Cannot update a booking that has been cancelled by the tenant', [],400);
        }

        $request->validate([
            'status' => 'required|in:confirmed,rejected'
        ]);

        try {
            if ($request->status === 'confirmed' && $booking->status === 'pending') {
                DB::beginTransaction();
                
                try {
                    $tenant = $booking->user;
                    $tenantWallet = $tenant->wallet;
                    
                    if (!$tenantWallet) {
                        DB::rollBack();
                        return $this->sendError('You do not have money in your wallet', [], 400);
                    }

                    if ((float)($tenantWallet->balance ?? 0) < (float)($booking->total_price ?? 0)) {
                        DB::rollBack();
                        return $this->sendError('Insufficient balance in tenant wallet. Balance: $' . number_format($tenantWallet->balance ?? 0, 2) . ', Required: $' . number_format($booking->total_price ?? 0, 2),[], 400);
                    }

                    $renter = $booking->apartment->owner;
                    $renterWallet = $renter->wallet;
                    
                    if (!$renterWallet) {
                        $renterWallet = new Wallet(['user_id' => $renter->id, 'balance' => 0]);
                        $renter->wallet()->save($renterWallet);
                    }

                    $tenantWallet->balance = (float)(($tenantWallet->balance ?? 0) - $booking->total_price);
                    $tenantWallet->save();

                    $renterWallet->balance = (float)(($renterWallet->balance ?? 0) + $booking->total_price);
                    $renterWallet->save();

                    $booking->update(['status' => $request->status]);
                                
                                
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    return $this->sendError('Payment processing failed', ['error' => $e->getMessage()],500);
                }
            } else {
                $booking->update(['status' => $request->status]);
                
            }

            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking status updated',200);
        } catch (Exception $e) {
            return $this->sendError('Booking update failed', ['error' => $e->getMessage()],500);
        }
    }

    
     
    public function cancel(Request $request, Booking $booking)
    {
        if ($request->user()->id !== $booking->user_id) {
            return $this->sendError('Unauthorized', [],401);
        }

        if ($booking->status !== 'pending' && $booking->status !== 'confirmed') {
            return $this->sendError('Invalid action', [],400);
        }

        try {
            $apartment = $booking->apartment;
            $tenant = $booking->user;
            $bookingStatus = $booking->status;
            $bookingTotalPrice = $booking->total_price;
            
            $booking->update(['status' => 'cancelled']);
            
            if ($bookingStatus === 'confirmed') {
                $refundAmount = (float)($bookingTotalPrice / 2);
                
                $tenantWallet = $tenant->wallet;
                if ($tenantWallet) {
                    $tenantWallet->balance = (float)($tenantWallet->balance + $refundAmount);
                    $tenantWallet->save();
                }
                
                $renterWallet = $apartment->owner->wallet;
                if ($renterWallet) {
                    $renterWallet->balance = (float)($renterWallet->balance - $refundAmount);
                    $renterWallet->save();
                }
            }
            
            
            $booking->load(['user', 'apartment']);
            
            return $this->sendResponse(new BookingResource($booking), 'Booking cancelled',200);
        } catch (Exception $e) {
            return $this->sendError('Booking cancellation failed', ['error' => $e->getMessage()],500);
        }
    }

        public function updateDetails(UpdateBookingRequest $request, Booking $booking)
    {
        if ($booking->status === 'cancelled') {
            return $this->sendError('Cannot update details of a cancelled booking', [],400);
        }

        try {
            if ($request->user()->id !== $booking->user_id) {
                return $this->sendError('Unauthorized', [],401);
            }

            if ($booking->status !== 'pending') {
                return $this->sendError('Cannot modify a confirmed booking. You can only cancel it.', [],400);
            }

            $totalPrice = $booking->total_price;
            if ($request->has('start_date') || $request->has('end_date')) {
                $startDate = $request->has('start_date') ? Carbon::parse($request->start_date) : $booking->start_date;
                $endDate = $request->has('end_date') ? Carbon::parse($request->end_date) : $booking->end_date;
                $days = $startDate->diffInDays($endDate);
                $totalPrice = (float)($days * $booking->apartment->price);
            }

            $booking->update(array_merge(
                $request->only(['start_date', 'end_date']),
                ['total_price' => $totalPrice, 'status' => $booking->status]
            ));

            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking updated',200);
        } catch (Exception $e) {
            return $this->sendError('Booking update failed', ['error' => $e->getMessage()],500);
        }
    }

    
     
    public function myBookings(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['apartment.owner', 'apartment.images'])->paginate(10);
        return $this->sendPaginatedResponse($bookings, 'My bookings:',200);
    }
}