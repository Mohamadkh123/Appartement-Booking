<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Review;
use App\Models\Apartment;
use Illuminate\Http\Request;

class ReviewController extends BaseController
{
    
     //Display a listing of the resource.
     
    public function index(Request $request)
    {
        $query = Review::with(['user', 'apartment']);

        // Filter by apartment ID
        if ($request->has('apartment_id')) {
            $query->where('apartment_id', $request->apartment_id);
        }

        // Filter by user ID
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        $reviews = $query->paginate(10);

        return $this->sendPaginatedResponse($reviews, 'Reviews retrieved', 200);
    }

    
      //Store a newly created resource in storage.
     
    public function store(StoreReviewRequest $request)
    {
        try {
            $user = $request->user();
            $apartment = Apartment::findOrFail($request->apartment_id);

            // Create or update review
            $review = $user->reviews()->updateOrCreate(
                ['apartment_id' => $request->apartment_id],
                [
                    'rating' => $request->rating,
                    'comment' => $request->comment
                ]
            );

            // Load relationships
            $review->load(['user', 'apartment']);

            return $this->sendResponse(new ReviewResource($review), 'Review saved successfully', 201);
        } catch (\Exception $e) {
            return $this->sendError('Review save failed', ['error' => $e->getMessage()],500);
        }
    }

    
      //Get reviews for a specific apartment
     
    public function apartmentReviews(Apartment $apartment)
    {
        $reviews = $apartment->reviews()->with('user')->paginate(10);
        return $this->sendPaginatedResponse($reviews, 'Apartment reviews retrieved', 200);
    }

}