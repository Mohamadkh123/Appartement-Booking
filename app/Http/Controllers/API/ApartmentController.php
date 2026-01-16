<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\API\BaseController;
use App\Http\Requests\StoreApartmentRequest;
use App\Http\Resources\ApartmentResource;
use App\Http\Resources\BookingResource;
use App\Models\Apartment;
use App\Models\ApartmentImage;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ApartmentController extends BaseController
{

      //Display a listing of the resource.

    public function index(Request $request)
    {


        // Check if user is tenant
        if (!$request->user()->isTenant()) {
            return $this->sendError('Only tenants can view apartment listings', [], 403);
        }

        $query = Apartment::with(['owner', 'images']);

        // Apply filters
        if ($request->has('province')) {
            $query->inProvince($request->province);
        }

        if ($request->has('city')) {
            $query->inCity($request->city);
        }

        if ($request->has('min_price') || $request->has('max_price')) {
            $min = $request->min_price ?? 0;
            $max = $request->max_price ?? 999999999;
            $query->priceBetween($min, $max);
        }

        if ($request->has('features')) {
            $features = explode(',', $request->features);
            $query->hasFeatures($features);
        }

        // Apply status filter (only show available apartments by default)
        if (!$request->has('status')) {
            $query->where('status', 'available');
        } elseif ($request->status != 'all') {
            $query->where('status', $request->status);
        }

         $perPage = $request->query('per_page', 10);
         $apartments = $query->paginate($perPage);

        // Return paginated response
        return $this->sendPaginatedResponse($apartments, 'Apartments retrieved',200);
    }


     //Store a newly created resource in storage.

    public function store(StoreApartmentRequest $request)
    {
        try {
            $user = $request->user();

            // Create apartment with owner relationship
            $apartment = $user->apartments()->create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'province' => $request->province,
                'city' => $request->city,
                'features' => $request->features ?? [],
                'status' => 'available'
            ]);

            // Handle image uploads
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('apartment_images', 'public');
                    ApartmentImage::create([
                        'apartment_id' => $apartment->id,
                        'image_path' => $imagePath
                    ]);
                }
            }

            // Load relationships
            $apartment->load(['owner', 'images']);

            return $this->sendResponse(new ApartmentResource($apartment), 'Apartment created',201);

} catch (Exception $e) {
    // Handle creation errors
    return $this->sendError('Apartment creation failed', ['error' => $e->getMessage()],500);
}
    }


    public function show(Apartment $apartment)
    {
        // Load relationships
        $apartment->load(['owner', 'images']);
        return $this->sendResponse(new ApartmentResource($apartment), 'Apartment retrieved',200);
    }


       //Update the specified resource in storage.

    public function update(Request $request, Apartment $apartment)
    {
        if ($request->user()->id !== $apartment->owner_id) {
            return $this->sendError('Unauthorized', [], 401);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'province' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:100',
            'features' => 'nullable|array',
            'status' => 'sometimes|in:available,booked,maintenance',
            'images' => 'nullable',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            // Update apartment fields
            $apartment->update($validated);

            // Update images if provided
            if ($request->hasFile('images')) {

                foreach ($apartment->images as $image) {
                    Storage::disk('public')->delete($image->image_path);
                }

                $apartment->images()->delete();

                foreach ($request->file('images') as $image) {
                    $path = $image->store('apartment_images', 'public');
                    $apartment->images()->create([
                        'image_path' => $path
                    ]);
                }
            }

            $apartment->load(['owner', 'images']);

            return $this->sendResponse(
                new ApartmentResource($apartment),
                'Apartment updated',
                200
            );
        } catch (\Throwable $e) {
            return $this->sendError(
                'Apartment update failed',
                ['error' => $e->getMessage()],
                500
            );
        }
    }

     //Remove the specified resource from storage.

    public function destroy(Request $request, Apartment $apartment)
    {
        // Check ownership or admin privileges
        if ($request->user()->id !== $apartment->owner_id && !$request->user()->isAdmin()) {
            // Return unauthorized error
            return $this->sendError('Unauthorized',[],401);
        }

        try {
            // Delete associated images
            foreach ($apartment->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            // Delete apartment
            $apartment->delete();

            return $this->sendResponse([], 'Apartment deleted',204);
                 } catch (Exception $e) {
                   // Handle deletion errors
                   return $this->sendError('Apartment deletion failed', ['error' => $e->getMessage()],500);
                 }
                }


      //Add apartment to favorites

    public function addToFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        // Check if user is tenant
        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can add apartments to favorites', [], 403);
        }

        // Check if already favorited
        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();

        if ($favorite) {
            return $this->sendError('Apartment already added to favorites', [], 400);
        } else {
            // Add to favorites
            $user->favorites()->create(['apartment_id' => $apartment->id]);
            return $this->sendResponse([], 'Apartment added to favorites',200);
        }
    }

      //Remove apartment from favorites

    public function removeFromFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        // Check if user is tenant
        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can remove apartments from favorites', [], 403);
        }

        // Check if already favorited
        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();

        if (!$favorite) {
            return $this->sendError('Apartment already removed from favorites', [], 400);
        } else {
            // Remove from favorites
            $favorite->delete();
            return $this->sendResponse([], 'Apartment removed from favorites',204);
        }
    }


      //Get user's favorite apartments

    public function favorites(Request $request)
    {
        $favorites = $request->user()
            ->favorites()
            ->with('apartment.owner', 'apartment.images')
            ->paginate(10);

        $favorites->getCollection()->transform(function ($favorite) {
            return $favorite->apartment;
        });

        return $this->sendPaginatedResponse($favorites, 'favorites retrieved',200);
    }


    /**
     * Book an apartment from the favorites list
     */
    public function bookFromFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        // Check if user is tenant
        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can book apartments from favorites', [], 403);
        }

        // Check if apartment is in user's favorites
        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();

        if (!$favorite) {
            return $this->sendError('This apartment is not in your favorites list', [], 404);
        }

        // Validate booking request data
        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Check for overlapping bookings
        $existingBooking = $apartment->bookings()
            ->where('status', '!=', 'cancelled')
            ->where(function($query) use ($request) {
                $query->where(function($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                          ->where('end_date', '>', $request->start_date);
                    })
                    ->orWhere(function($q) use ($request) {
                        $q->where('start_date', '<', $request->end_date)
                          ->where('end_date', '>=', $request->end_date);
                    })
                    ->orWhere(function($q) use ($request) {
                        $q->where('start_date', '>=', $request->start_date)
                          ->where('end_date', '<=', $request->end_date);
                    });
            })
            ->first();

        if ($existingBooking) {
            return $this->sendError('This apartment is already booked for the selected dates', [], 400);
        }

        try {
            // Calculate total price based on days
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);
            $totalPrice = (float)($days * $apartment->price);

            // Check if tenant has a wallet
            $tenantWallet = $user->wallet;
            if (!$tenantWallet) {
                return $this->sendError('You do not have money in your wallet.', [], 400);
            }

            // Check if tenant has sufficient balance
            $currentBalance = (float)($tenantWallet->balance ?? 0);
            if ($currentBalance < $totalPrice) {
                return $this->sendError("Insufficient balance in your wallet. Your balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalPrice, 2), [], 400);
            }

            // Create booking with pending status
            $booking = $user->bookings()->create([
                'apartment_id' => $apartment->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
                'total_price' => $totalPrice
            ]);



            // Load relationships
            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking created from favorites. Pending owner approval.',200);
        } catch (Exception $e) {
            // Handle creation errors
            return $this->sendError('Booking creation from favorites failed', ['error' => $e->getMessage()],500);
        }
    }
}
