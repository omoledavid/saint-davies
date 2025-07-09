<?php

namespace App\Http\Controllers;

use App\Http\Requests\TenantRegistrationRequest;
use App\Http\Requests\TenantUpdateRequest;
use App\Http\Resources\TenancyResource;
use App\Models\Tenancy;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TenantController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $tenants = Tenancy::where('manager_id', $request->user()->id)->get();
        return $this->success('Tenants fetched successfully', TenancyResource::collection($tenants));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TenantRegistrationRequest $request)
    {
        $data = $request->validated();
        $data['manager_id'] = $request->user()->id;
        // Handle file uploads
        foreach (['id_front_image', 'id_back_image', 'user_image'] as $fileField) {
            if ($request->hasFile($fileField)) {
                $file = $request->file($fileField);
                $path = $file->storeAs('property/tenants', time() . '_' . $file->getClientOriginalName(), 'public');
                $data[$fileField] = $path;
            }
        }
        $tenant = Tenancy::create($data);
        return $this->success('Tenant created successfully', new TenancyResource($tenant));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TenantUpdateRequest $request, string $id)
    {
        $data = $request->validated();
        $tenant = Tenancy::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found', 404);
        }
        if ($tenant->manager_id !== auth()->user()->id) {
            return $this->error('Unauthorized access to tenant', 403);
        }
        $tenant->update($data);
        return $this->success('Tenant updated successfully', new TenancyResource($tenant));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $tenant = Tenancy::find($id);
        if (!$tenant) {
            return $this->error('Tenant not found', 404);
        }
        if ($tenant->manager_id !== auth()->user()->id) {
            return $this->error('Unauthorized access to tenant', 403);
        }
        $tenant->delete();
        return $this->success('Tenant deleted successfully');
    }
}
