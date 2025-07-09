<?php

namespace App\Http\Controllers;

use App\Enums\PropertyStatus;
use App\Http\Filters\PropertyFilter;
use App\Http\Requests\PropertyRequest;
use App\Http\Resources\PropertyResource;
use App\Http\Resources\ReviewResource;
use App\Models\FileUpload;
use App\Models\Property;
use App\Services\FileUploadService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index(PropertyFilter $filter)
    {
        $user = auth()->user();
        $properties = Property::where('manager_id', $user->id)->filter($filter)->latest()->get();
        return $this->ok('Properties fetched successfully', PropertyResource::collection($properties));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyRequest $request)
    {
        $user = auth()->user();
        $property = Property::create([
            ...$request->validated(),
            'manager_id' => $user->id,
            'status' => PropertyStatus::PENDING,
        ]);
        return $this->ok('Property created successfully', PropertyResource::make($property));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyRequest $request, string $id)
    {
        $user = auth()->user();
        $property = Property::findOrFail($id);
        $property->update($request->validated());
        return $this->ok('Property updated successfully', PropertyResource::make($property));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth()->user();
        $property = Property::findOrFail($id);
        if ($property->manager_id !== $user->id) {
            return $this->error('Unauthorized access to property', 403);
        }
        if ($property->units()->whereHas('tenancy')->exists()) {
            return $this->error('Cannot delete property with active tenants', 422);
        }
        $property->delete();
        return $this->ok('Property deleted successfully');
    }


    public function uploadImages(Request $request, string $id)
    {
        $user = auth()->user();
        $property = Property::findOrFail($id);

        if ($property->manager_id !== $user->id) {
            return $this->error('Unauthorized access to property', 403);
        }

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $fileService = new FileUploadService();
            $response = $fileService->uploadFiles($property, $request, 'images');

            if (!$response['success']) {
                return $this->error('Some images failed to upload', 400, $response['errors']);
            }

            return $this->ok('Property images uploaded successfully', [
                'uploaded_files' => array_map(function ($path) {
                    return asset('storage/' . $path);
                }, $response['uploaded_paths'])
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to upload images', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified image.
     */
    public function updateImage(Request $request, string $fileId)
    {
        $user = auth()->user();
        $file = FileUpload::findOrFail($fileId);
        $property = $file->uploadable;

        if ($property->manager_id !== $user->id) {
            return $this->error('Unauthorized access to property', 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $newFile = $request->file('image');

        try {
            $fileService = new FileUploadService();
            $response = $fileService->updateFile($file, $newFile);
            return $this->ok('Image updated successfully', [
                'updated_file' => asset('storage/' . $response['new_path'])
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to update image', 500, $e->getMessage());
        }
    }
    /**
     * Delete a specific image from a property
     */
    public function deleteImage(string $fileId)
    {
        $file = FileUpload::findOrFail($fileId);
        $property = $file->uploadable;
        $user = auth()->user();

        if ($property->manager_id !== $user->id) {
            return $this->error('Unauthorized access to property', 403);
        }

        try {
            $fileService = new FileUploadService();
            $fileService->deleteFile($file);
            return $this->ok('Image deleted successfully', new PropertyResource($property->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to delete image', 500, $e->getMessage());
        }
    }

    /**
     * Set an image as the main image for a property
     */
    public function setMainImage(string $fileId)
    {
        $user = auth()->user();
        $file = FileUpload::findOrFail($fileId);
        $property = $file->uploadable;

        if ($property->manager_id !== $user->id) {
            return $this->error('Unauthorized access to property', 403);
        }

        try {
            $fileService = new FileUploadService();
            $fileService->setMainFile($property, $fileId);
            return $this->ok('Main image set successfully', new PropertyResource($property->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to set main image', 500, $e->getMessage());
        }
    }

    /**
     * Reorder images for a property
     */
    public function reorderImages(Request $request, string $propertyId)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:file_uploads,id',
        ]);
        $property = Property::findOrFail($propertyId);
        $fileService = new FileUploadService();
        $fileService->reorderFiles($property, $request->file_ids);
        return $this->ok('Images reordered successfully', [
            'order_files' => PropertyResource::collection($property->files()->orderBy('order')->get())
        ]);
    }
    /**
     * Display all properties.
     */
    public function allProperties(PropertyFilter $filter)
    {
        $properties = Property::where('status', PropertyStatus::APPROVED)->filter($filter)->get();
        return $this->ok('Properties fetched successfully', PropertyResource::collection($properties));
    }
    public function getReviews(string $id)
    {
        $property = Property::findOrFail($id);
        $reviews = $property->reviews()->get();
        return $this->ok('Reviews fetched successfully', ReviewResource::collection($reviews));
    }
}
