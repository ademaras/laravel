<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;

class CountryController extends Controller
{
    public function index()
    {
        $countries = Country::get();

        return $this->mobile(true, 'Ülkeler', $countries);
    }
}
