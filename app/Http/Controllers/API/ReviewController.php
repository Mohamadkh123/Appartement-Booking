<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    
     
    public function index(Request $request)
    {
        $query = Review::with(['user', 'apartment']);

        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->paginate(10);

        return $this->sendPaginatedResponse($reviews, 'Reviews retrieved', 200);
    }

    
     
    public function store(StoreReviewRequest $request)
    {
        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            $review = $user->reviews()->updateOrCreate(
                ['apartment_id' => $request->apartment_id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]
            );

            $review->load(['user', 'apartment']);

            return $this->sendResponse(new ReviewResource($review), 'Review saved successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Review save failed', ['error' => $e->getMessage()],500);
        }
    }

    
     
    public function apartmentReviews(Apartment $apartment)
    {
        $reviews = $apartment->reviews()->with('user')->paginate(10);
        return $this->sendPaginatedResponse($reviews, 'Apartment reviews retrieved', 200);
    }

}