<?php

namespace App\Http\Controllers;

use App\Http\Requests\PropertyUnitRegistrationRequest;
use App\Http\Requests\PropertyUnitUpdateRequest;
use App\Http\Resources\PropertyUnitResource;
use App\Models\FileUpload;
use App\Models\PropertyUnit;
use App\Services\FileUploadService;
use App\Services\PropertyUnitImageService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyUnitController extends Controller
{
    use ApiResponses;

    protected $imageService;

    public function __construct(PropertyUnitImageService $imageService)
    {
        $this->imageService = $imageService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $propertyUnits = PropertyUnit::where('user_id', Auth::user()->id)->get();
        return $this->ok('Property units fetched successfully', PropertyUnitResource::collection($propertyUnits));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PropertyUnitRegistrationRequest $request)
    {
        $data = $request->validated();
        $data['user_id'] = Auth::user()->id;
        if ($request->hasFile('agreement_file')) {
            $data['agreement_file'] = $request->file('agreement_file')->storeAs('property/property_unit/agreement_files', time() . '_' . $request->file('agreement_file')->getClientOriginalName(), 'public');
        }
        if ($request->hasFile('payment_receipt')) {
            $data['payment_receipt'] = $request->file('payment_receipt')->storeAs('property/property_unit/payment_receipts', time() . '_' . $request->file('payment_receipt')->getClientOriginalName(), 'public');
        }
        $propertyUnit = PropertyUnit::create($data);
        return $this->success('Property unit created successfully', new PropertyUnitResource($propertyUnit));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(PropertyUnitUpdateRequest $request, string $id)
    {
        $data = $request->validated();
        $propertyUnit = PropertyUnit::find($id);
        if (!$propertyUnit) {
            return $this->error('Property unit not found', 404);
        }
        if ($propertyUnit->user_id !== Auth::user()->id) {
            return $this->error('Unauthorized access to property unit', 403);
        }
        if ($request->hasFile('agreement_file')) {
            $data['agreement_file'] = $request->file('agreement_file')->storeAs('property/property_unit/agreement_files', time() . '_' . $request->file('agreement_file')->getClientOriginalName(), 'public');
        }
        if ($request->hasFile('payment_receipt')) {
            $data['payment_receipt'] = $request->file('payment_receipt')->storeAs('property/property_unit/payment_receipts', time() . '_' . $request->file('payment_receipt')->getClientOriginalName(), 'public');
        }
        $propertyUnit->update($data);
        return $this->ok('Property unit updated successfully', new PropertyUnitResource($propertyUnit));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $propertyUnit = PropertyUnit::findOrFail($id);
        if ($propertyUnit->user_id !== Auth::user()->id) {
            return $this->error('Unauthorized access to property unit', 403);
        }
        if ($propertyUnit->tenancy()->exists()) {
            return $this->error('Cannot delete property unit with active tenancy', 400);
        }
        $propertyUnit->delete();
        return $this->ok('Property unit deleted successfully');
    }

    /**
     * Upload images for a property unit
     */
    public function uploadImages(Request $request, string $id)
    {
        $user = Auth::user();
        $propertyUnit = PropertyUnit::findOrFail($id);

        if ($propertyUnit->user_id !== $user->id) {
            return $this->error('Unauthorized access to property unit', 403);
        }

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $fileService = new FileUploadService();
            $response = $fileService->uploadFiles($propertyUnit, $request, 'images');

            if (!$response['success']) {
                return $this->error('Some images failed to upload', 400, $response['errors']);
            }

            return $this->ok('Property unit images uploaded successfully', [
                'uploaded_files' => array_map(function ($path) {
                    return asset('storage/' . $path);
                }, $response['uploaded_paths'])
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to upload images', 500, $e->getMessage());
        }
    }

    /**
     * Update a specific image for a property unit
     */
    public function updateImage(Request $request, string $fileId)
    {
        $file = FileUpload::findOrFail($fileId);

        $propertyUnit = $file->uploadable;
        $user = Auth::user();

        if ($propertyUnit->user_id !== $user->id) {
            return $this->error('Unauthorized access to property unit', 403);
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
     * Delete a specific image from a property unit
     */
    public function deleteImage(string $fileId)
    {
        $file = FileUpload::findOrFail($fileId);
        $propertyUnit = $file->uploadable;
        $user = Auth::user();

        if ($propertyUnit->user_id !== $user->id) {
            return $this->error('Unauthorized access to property unit', 403);
        }

        try {
            $fileService = new FileUploadService();
            $fileService->deleteFile($file);
            return $this->success('Image deleted successfully', new PropertyUnitResource($propertyUnit));
        } catch (\Exception $e) {
            return $this->error('Failed to delete image', 500, $e->getMessage());
        }
    }

    /**
     * Set an image as the main image for a property unit
     */
    public function setMainImage(string $fileId)
    {
        $user = Auth::user();
        $file = FileUpload::findOrFail($fileId);
        $propertyUnit = $file->uploadable;

        if ($propertyUnit->user_id !== $user->id) {
            return $this->error('Unauthorized access to property unit', 403);
        }

        try {
            $fileService = new FileUploadService();
            $fileService->setMainFile($propertyUnit, $fileId);
            return $this->ok('Main image set successfully', new PropertyUnitResource($propertyUnit->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to set main image', 500, $e->getMessage());
        }
    }

    /**
     * Get all images for a property unit
     */
    public function getImages(string $propertyUnitId)
    {
        $user = Auth::user();
        $propertyUnit = PropertyUnit::findOrFail($propertyUnitId);

        if ($propertyUnit->user_id !== $user->id) {
            return $this->error('Unauthorized access to property unit', 403);
        }

        try {
            $result = $this->imageService->getImages($propertyUnit);
            return $this->success('Images retrieved successfully', $result);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve images', 500, $e->getMessage());
        }
    }

    /**
     * Reorder images for a property unit
     */
    public function reorderImages(Request $request, string $propertyUnitId)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:file_uploads,id',
        ]);
        $propertyUnit = PropertyUnit::findOrFail($propertyUnitId);
        $fileService = new FileUploadService();
        $fileService->reorderFiles($propertyUnit, $request->file_ids);
        return $this->ok('Images reordered successfully', [
            'order_files' => PropertyUnitResource::collection($propertyUnit->files()->orderBy('order')->get())
        ]);
    }
}
