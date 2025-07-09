<?php

namespace App\Http\Controllers;

use App\Http\Resources\PropertyUnitResource;
use App\Http\Resources\TenancyResource;
use App\Models\Tenancy;
use Illuminate\Http\Request;
use App\Traits\ApiResponses;

class TenantUserController extends Controller
{
    use ApiResponses;
    public function dashboard()
    {
        $user = auth()->user();
        $tenant = Tenancy::find($user->id);
        return $this->ok('Tenant user dashboard', [
            'user' => new TenancyResource($tenant),
            'property_apartment' => new PropertyUnitResource($tenant->unit->load('property', 'property.files')),
        ]);
    }
    public function requestMaintenance(Request $request)
    {
        $validatedData = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string|in:low,medium,high',
        ]);
        $tenant = Tenancy::find(auth()->user()->id);
        $tenant->maintenanceRequests()->create($validatedData);
        return $this->ok('Maintenance request created successfully');
    }
}
