<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array {
        return [
            'email' => ['required','email'],
            'phone' => ['nullable','string','max:30'],
            'shippingAddress' => ['required','array'],
            'billingAddress' => ['required','array'],
            'deliveryMethod' => ['required','array'],
            'deliveryMethod.code' => ['required','string','exists:shipping_methods,code'],
            'coupon' => ['nullable','string'],
            'paymentIntentId' => ['nullable','string'],
        ];
    }
}
