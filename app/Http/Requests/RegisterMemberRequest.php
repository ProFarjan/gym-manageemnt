<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMemberRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255', 'unique:members,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],

            'height' => ['nullable', 'numeric', 'between:50,250'],
            'weight' => ['nullable', 'numeric', 'between:20,300'],
            'blood_group' => ['nullable', 'string', 'in:A+,A-,B+,B-,O+,O-,AB+,AB-'],
            'fitness_goal' => ['nullable', 'string', 'max:255'],

            'membership_plan_id' => ['required', 'exists:membership_plans,id'],
        ];
    }
}
