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

      

    public function index(Request $request)
    {


        if (!$request->user()->isTenant()) {
            return $this->sendError('Only tenants can view apartment listings', [], 403);
        }

        $query = Apartment::with(['owner', 'images']);

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

        if (!$request->has('status')) {
            $query->where('status', 'available');
        } elseif ($request->status != 'all') {
            $query->where('status', $request->status);
        }

         $perPage = $request->query('per_page', 10);
         $apartments = $query->paginate($perPage);

        return $this->sendPaginatedResponse($apartments, 'Apartments retrieved',200);
    }



    public function store(StoreApartmentRequest $request)
    {
        try {
            $user = $request->user();

            $apartment = $user->apartments()->create([
                'title' => $request->title,
                'description' => $request->description,
                'price' => $request->price,
                'province' => $request->province,
                'city' => $request->city,
                'features' => $request->features ?? [],
                'status' => 'available'
            ]);

            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    $imagePath = $image->store('apartment_images', 'public');
                    ApartmentImage::create([
                        'apartment_id' => $apartment->id,
                        'image_path' => $imagePath
                    ]);
                }
            }

            $apartment->load(['owner', 'images']);

            return $this->sendResponse(new ApartmentResource($apartment), 'Apartment created',201);

} catch (Exception $e) {

    return $this->sendError('Apartment creation failed', ['error' => $e->getMessage()],500);
}
    }


    public function show(Apartment $apartment)
    {
        $apartment->load(['owner', 'images']);
        return $this->sendResponse(new ApartmentResource($apartment), 'Apartment retrieved',200);
    }



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
            $apartment->update($validated);

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


    public function destroy(Request $request, Apartment $apartment)
    {
        if ($request->user()->id !== $apartment->owner_id && !$request->user()->isAdmin()) {
            return $this->sendError('Unauthorized',[],401);
        }

        try {
            foreach ($apartment->images as $image) {
                Storage::disk('public')->delete($image->image_path);
            }

            $apartment->delete();

            return $this->sendResponse([], 'Apartment deleted',204);
                 } catch (Exception $e) {
                   return $this->sendError('Apartment deletion failed', ['error' => $e->getMessage()],500);
                 }
                }



    public function addToFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can add apartments to favorites', [], 403);
        }

        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();

        if ($favorite) {
            return $this->sendError('Apartment already added to favorites', [], 400);
        } else {
            $user->favorites()->create(['apartment_id' => $apartment->id]);
            return $this->sendResponse([], 'Apartment added to favorites',200);
        }
    }


    public function removeFromFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can remove apartments from favorites', [], 403);
        }

        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();

        if (!$favorite) {
            return $this->sendError('Apartment already removed from favorites', [], 400);
        } else {
            $favorite->delete();
            return $this->sendResponse([], 'Apartment removed from favorites',204);
        }
    }



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


    
    public function bookFromFavorites(Request $request, Apartment $apartment)
    {
        $user = $request->user();

        if (!$user->isTenant()) {
            return $this->sendError('Only tenants can book apartments from favorites', [], 403);
        }

        $favorite = $user->favorites()->where('apartment_id', $apartment->id)->first();

        if (!$favorite) {
            return $this->sendError('This apartment is not in your favorites list', [], 404);
        }

        $request->validate([
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after:start_date',
        ]);

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
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $days = $startDate->diffInDays($endDate);
            $totalPrice = (float)($days * $apartment->price);

            $tenantWallet = $user->wallet;
            if (!$tenantWallet) {
                return $this->sendError('You do not have money in your wallet.', [], 400);
            }

            $currentBalance = (float)($tenantWallet->balance ?? 0);
            if ($currentBalance < $totalPrice) {
                return $this->sendError("Insufficient balance in your wallet. Your balance: $" . number_format($currentBalance, 2) . ", Required: $" . number_format($totalPrice, 2), [], 400);
            }

            $booking = $user->bookings()->create([
                'apartment_id' => $apartment->id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'status' => 'pending',
                'total_price' => $totalPrice
            ]);



            $booking->load(['user', 'apartment']);

            return $this->sendResponse(new BookingResource($booking), 'Booking created from favorites. Pending owner approval.',200);
        } catch (Exception $e) {
            return $this->sendError('Booking creation from favorites failed', ['error' => $e->getMessage()],500);
        }
    }
}
