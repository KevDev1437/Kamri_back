<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class PostalCodeForCountry implements ValidationRule
{
    private string $country;

    public function __construct(?string $country)
    {
        $this->country = strtoupper((string) $country);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $val = (string) $value;

        $patterns = [
            'BE' => '/^\d{4}$/',
            'FR' => '/^\d{5}$/',
            'DE' => '/^\d{5}$/',
            'NL' => '/^\d{4}\s?[A-Z]{0,2}$/',
            'LU' => '/^\d{4}$/',
        ];

        if (isset($patterns[$this->country])) {
            if (!preg_match($patterns[$this->country], $val)) {
                $fail('Code postal invalide pour le pays sélectionné.');
            }
        } else {
            if (strlen($val) < 2 || strlen($val) > 12) {
                $fail('Code postal invalide.');
            }
        }
    }
}
