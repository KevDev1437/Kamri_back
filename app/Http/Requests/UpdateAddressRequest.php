<?php

namespace App\Http\Requests;

use App\Rules\PostalCodeForCountry;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        $country = strtoupper((string) $this->input('country'));

        return [
            'label'       => ['sometimes','string','max:50'],
            'first_name'  => ['sometimes','string','max:60'],
            'last_name'   => ['sometimes','string','max:60'],
            'line1'       => ['sometimes','string','max:120'],
            'line2'       => ['nullable','string','max:120'],
            'postal_code' => ['sometimes','string','max:12', new PostalCodeForCountry($country)],
            'city'        => ['sometimes','string','max:80'],
            'country'     => ['sometimes','string','size:2', Rule::in(['BE','FR','DE','NL','LU'])],
            'phone'       => ['nullable','string','max:32','regex:/^[0-9+\s().-]{6,20}$/'],

            'is_default_shipping' => ['sometimes','boolean'],
            'is_default_billing'  => ['sometimes','boolean'],
        ];
    }
}
