<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShippingMethod;
use Illuminate\Http\Request;

class ShippingController extends Controller
{
    public function __construct() { $this->middleware('auth:sanctum'); }

    public function index(Request $req) {
        $country = strtoupper($req->query('country','BE'));
        $methods = ShippingMethod::query()->where('active', true)->get()
            ->filter(fn($m) => $m->isAvailableForCountry($country))
            ->values()
            ->map(fn($m) => [
                'code' => $m->code, 'label' => $m->label, 'price' => (float)$m->price, 'eta' => $m->eta
            ]);
        return response()->json(['success' => true, 'methods' => $methods]);
    }
}
