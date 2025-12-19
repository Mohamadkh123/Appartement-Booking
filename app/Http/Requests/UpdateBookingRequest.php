<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Booking;
use Carbon\Carbon;

class UpdateBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only the booking owner can update their booking
        $booking = $this->route('booking');
        return $booking && $this->user()->id === $booking->user_id;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'start_date' => 'sometimes|date|after:today',
            'end_date' => 'sometimes|date|after:start_date',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Check if dates overlap with existing bookings
            if ($this->isOverlapping()) {
                $validator->errors()->add('start_date', 'The selected dates overlap with an existing booking.');
            }
        });
    }

    /**
     * Check if the requested dates overlap with existing bookings
     */
    private function isOverlapping(): bool
    {
        $booking = $this->route('booking');
        
        if (!$this->start_date || !$this->end_date || !$booking) {
            return false;
        }

        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        // Check for overlapping bookings (include pending bookings to prevent conflicts)
        // Exclude the current booking from the check
        $overlappingBookings = Booking::where('apartment_id', $booking->apartment_id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('id', '!=', $booking->id)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($query) use ($startDate, $endDate) {
                        $query->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();

        return $overlappingBookings;
    }
}
