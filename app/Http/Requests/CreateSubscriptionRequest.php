<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateSubscriptionRequest extends FormRequest
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
            'plan' => 'required|in:free,pro,pro_plus',
            'email' => 'required|email',
            'given_name' => 'required|string|max:255',
            'family_name' => 'required|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'city' => 'required|string|max:255',
            'region' => 'nullable|string|max:255',
            'postal_code' => 'required|string|max:10',
            'country_code' => 'required|string|size:2',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'plan.required' => 'Please select a subscription plan.',
            'plan.in' => 'Please select a valid subscription plan.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'given_name.required' => 'First name is required.',
            'family_name.required' => 'Last name is required.',
            'address_line1.required' => 'Address line 1 is required.',
            'city.required' => 'City is required.',
            'postal_code.required' => 'Postal code is required.',
            'country_code.required' => 'Country is required.',
            'country_code.size' => 'Country code must be 2 characters.',
        ];
    }

    /**
     * Get customer data from the request
     */
    public function getCustomerData(): array
    {
        return [
            'email' => (string) $this->input('email'),
            'given_name' => (string) $this->input('given_name'),
            'family_name' => (string) $this->input('family_name'),
            'company_name' => $this->input('company_name') ? (string) $this->input('company_name') : null,
            'address_line1' => (string) $this->input('address_line1'),
            'address_line2' => $this->input('address_line2') ? (string) $this->input('address_line2') : null,
            'city' => (string) $this->input('city'),
            'region' => $this->input('region') ? (string) $this->input('region') : null,
            'postal_code' => (string) $this->input('postal_code'),
            'country_code' => (string) $this->input('country_code'),
        ];
    }
}
