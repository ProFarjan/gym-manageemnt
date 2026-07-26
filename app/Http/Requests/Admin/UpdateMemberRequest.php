<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('members.update');
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $member = $this->route('member');

        return [
            'full_name' => ['required', 'string', 'max:255'],
            'mobile_number' => ['required', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('members', 'email')->ignore($member->id)],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string'],
            'nid_number' => ['nullable', 'string', 'max:50', Rule::unique('members', 'nid_number')->ignore($member->id)],
            'nid_image' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png', 'max:2048'],
            'emergency_contact' => ['nullable', 'string', 'max:255'],

            'height' => ['nullable', 'numeric', 'between:50,250'],
            'weight' => ['nullable', 'numeric', 'between:20,300'],
            'blood_group' => ['nullable', 'string', 'in:A+,A-,B+,B-,O+,O-,AB+,AB-'],
            'fitness_goal' => ['nullable', 'string', 'max:255'],
            'medical_conditions' => ['nullable', 'string'],

            'membership_plan_id' => ['required', 'exists:membership_plans,id'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'discount_reason' => ['required_with:discount_amount', 'nullable', 'string', 'max:255'],
        ];
    }
}
