<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'postalCode' => $this->postal_code,
            'city' => $this->city,
            'country' => $this->country,
            'phone' => $this->phone,
            'isDefaultShipping' => (bool) $this->is_default_shipping,
            'isDefaultBilling' => (bool) $this->is_default_billing,
        ];
    }
}
