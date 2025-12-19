<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'date_of_birth' => 'required|date|before:today',
            'email' => 'required|email|unique:users,email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'mobile' => 'required|string|unique:users,mobile|max:20',
            'role' => 'required|in:tenant,renter',
            'id_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'profile_image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'First name is required',
            'last_name.required' => 'Last name is required',
            'date_of_birth.required' => 'Date of birth is required',
            'date_of_birth.date' => 'Date of birth must be a valid date',
            'date_of_birth.before' => 'Date of birth must be before today',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters',
            'password.confirmed' => 'Passwords do not match',
            'mobile.required' => 'Mobile number is required',
            'mobile.unique' => 'Mobile number already exists',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be either tenant or renter',
            'id_image.required' => 'ID image is required',
            'id_image.image' => 'ID image must be an image',
            'id_image.mimes' => 'ID image must be a file of type: jpeg, png, jpg, gif',
            'id_image.max' => 'ID image may not be greater than 2MB',
            'profile_image.required' => 'Profile image is required',
            'profile_image.image' => 'Profile image must be an image',
            'profile_image.mimes' => 'Profile image must be a file of type: jpeg, png, jpg, gif',
            'profile_image.max' => 'Profile image may not be greater than 2MB'
        ];
    }
}