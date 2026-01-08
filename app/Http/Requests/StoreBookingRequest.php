<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Booking;
use Carbon\Carbon;

class StoreBookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'apartment_id' => 'required|exists:apartments,id',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date'
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
            
            // Check if apartment is available for booking
            if ($this->apartment_id && !$this->isApartmentAvailable()) {
                $validator->errors()->add('apartment_id', 'The selected apartment is not available for booking.');
            }
        });
    }

    /**
     * Check if the requested dates overlap with existing bookings
     */
    private function isOverlapping(): bool
    {
        if (!$this->start_date || !$this->end_date || !$this->apartment_id) {
            return false;
        }

        $startDate = Carbon::parse($this->start_date);
        $endDate = Carbon::parse($this->end_date);

        // Check for overlapping bookings (include pending bookings to prevent conflicts)
        $overlappingBookings = Booking::where('apartment_id', $this->apartment_id)
            ->whereIn('status', ['pending', 'confirmed'])
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

    /**
     * Check if the apartment is available for booking
     */
    private function isApartmentAvailable(): bool
    {
        if (!$this->apartment_id) {
            return false;
        }

        $apartment = \App\Models\Apartment::find($this->apartment_id);
        
        // Apartment must exist and be available
        return $apartment && $apartment->status === 'available';
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'apartment_id.required' => 'Apartment is required',
            'apartment_id.exists' => 'Selected apartment does not exist',
            'start_date.required' => 'Start date is required',
            'start_date.date' => 'Start date must be a valid date',
            'start_date.after' => 'Start date must be in the future',
            'end_date.required' => 'End date is required',
            'end_date.date' => 'End date must be a valid date',
            'end_date.after' => 'End date must be after start date'
        ];
    }

    /**
     * Get the error message for authorization failure.
     *
     * @return string
     */
    public function forbiddenResponse()
    {
        return response()->json([
            'success' => false,
            'message' => 'Only tenants can book apartments'
        ], 403);
    }
}