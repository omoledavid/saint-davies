<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    use ApiResponses;
    public function index()
    {
        return $this->ok('Plans fetched successfully', Plan::with('features')->get());
    }


    
}

