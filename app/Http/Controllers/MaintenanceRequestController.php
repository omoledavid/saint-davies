<?php

namespace App\Http\Controllers;

use App\Http\Resources\MaintenanceRequestResource;
use App\Models\MaintenanceRequest;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class MaintenanceRequestController extends Controller
{
    use ApiResponses;

    public function index()
    {
        $manager = auth()->user();
        $maintenanceRequests = MaintenanceRequest::with(['tenancy.unit.property'])
            ->whereHas('tenancy.unit.property', function ($query) use ($manager) {
                $query->where('manager_id', $manager->id);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return $this->ok('Maintenance requests retrieved successfully', [
            'maintenance_requests' => MaintenanceRequestResource::collection($maintenanceRequests)
        ]);
    }

    public function changeStatus(Request $request)
    {
        $validatedData = $request->validate([
            'maintenance_request_id' => 'required|exists:maintenance_requests,id',
            'status' => 'required|in:pending,in_progress,resolved,cancelled'
        ]);

        $manager = auth()->user();
        $maintenanceRequest = MaintenanceRequest::whereHas('tenancy.unit.property', function ($query) use ($manager) {
            $query->where('manager_id', $manager->id);
        })->findOrFail($validatedData['maintenance_request_id']);

        $maintenanceRequest->update(['status' => $validatedData['status']]);

        return $this->ok('Maintenance request status updated successfully', [
            'maintenance_request' => $maintenanceRequest->load('tenancy.unit.property')
        ]);
    }
}
