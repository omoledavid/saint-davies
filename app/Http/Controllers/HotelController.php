<?php

namespace App\Http\Controllers;

use App\Http\Filters\HotelFilter;
use App\Http\Requests\HotelCreateRequest;
use App\Http\Resources\HotelBookingResource;
use App\Http\Resources\HotelResource;
use App\Models\FileUpload;
use App\Models\Hotel;
use App\Models\HotelBooking;
use App\Services\FileUploadService;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HotelController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $hotels = Hotel::with('files','manager')->where('manager_id', Auth::user()->id)->get();
        return $this->ok('Hotels retrieved successfully', HotelResource::collection($hotels));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HotelCreateRequest $request)
    {
        $data = $request->validated();
        $data['manager_id'] = Auth::user()->id;
        $hotel = Hotel::create($data);
        return $this->ok('Hotel created successfully', new HotelResource($hotel->load('files','manager')), 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HotelCreateRequest $request, string $id)
    {
        $data = $request->validated();
        $hotel = Hotel::find($id);
        if (!$hotel) {
            return $this->error('Hotel not found');
        }
        if ($hotel->manager_id !== Auth::user()->id) {
            return $this->error('You do not have permission to update this hotel');
        }
        $hotel->update($data);
        return $this->ok('Hotel updated successfully', new HotelResource($hotel->load('files','manager')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hotel = Hotel::find($id);
        // Check if hotel exists
        if (!$hotel) {
            return $this->error('Hotel not found');
        }

        // Check if hotel belongs to the authenticated user
        if ($hotel->manager_id !== Auth::user()->id) {
            return $this->error('You do not have permission to delete this hotel');
        }

        // Check if hotel has any room categories
        if ($hotel->roomCategories()->exists()) {
            return $this->error('Cannot delete hotel with existing room categories');
        }
        $hotel->delete();
        return $this->ok('Hotel deleted successfully');
    }

    /**
     * Reorder images for a hotel
     */
    public function reorderImages(Request $request, string $hotelId)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:file_uploads,id',
        ]);
        $hotel = Hotel::findOrFail($hotelId);
        $fileService = new FileUploadService();
        $fileService->reorderFiles($hotel, $request->file_ids);
        return $this->ok('Images reordered successfully', [
            'order_files' => $hotel->files()->orderBy('order')->get()
        ]);
    }

    /**
     * Upload images for a hotel
     */
    public function uploadImages(Request $request, string $id)
    {
        $user = Auth::user();
        $hotel = Hotel::findOrFail($id);

        if ($hotel->manager_id !== $user->id) {
            return $this->error('Unauthorized access to hotel', 403);
        }

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $fileService = new FileUploadService();
            $response = $fileService->uploadFiles($hotel, $request, 'images');

            if (!$response['success']) {
                return $this->error('Some images failed to upload', 400, $response['errors']);
            }

            return $this->ok('Hotel images uploaded successfully', [
                'uploaded_files' => array_map(function ($path) {
                    return asset('storage/' . $path);
                }, $response['uploaded_paths'])
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to upload images', 500, $e->getMessage());
        }
    }

    /**
     * Update the specified image for a hotel
     */
    public function updateImage(Request $request, string $fileId)
    {
        $user = Auth::user();
        $file = FileUpload::findOrFail($fileId);
        $hotel = $file->uploadable;

        if ($hotel->manager_id !== $user->id) {
            return $this->error('Unauthorized access to hotel', 403);
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
     * Delete a specific image from a hotel
     */
    public function deleteImage(string $fileId)
    {
        $file = \App\Models\FileUpload::findOrFail($fileId);
        $hotel = $file->uploadable;
        $user = Auth::user();

        if ($hotel->manager_id !== $user->id) {
            return $this->error('Unauthorized access to hotel', 403);
        }

        try {
            $fileService = new FileUploadService();
            $fileService->deleteFile($file);
            return $this->ok('Image deleted successfully', new HotelResource($hotel->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to delete image', 500, $e->getMessage());
        }
    }

    /**
     * Set an image as the main image for a hotel
     */
    public function setMainImage(string $fileId)
    {
        $user = Auth::user();
        $file = FileUpload::findOrFail($fileId);
        $hotel = $file->uploadable;

        if ($hotel->manager_id !== $user->id) {
            return $this->error('Unauthorized access to hotel', 403);
        }

        try {
            $fileService = new FileUploadService();
            $fileService->setMainFile($hotel, $fileId);
            return $this->ok('Main image set successfully', new HotelResource($hotel->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to set main image', 500, $e->getMessage());
        }
    }
    public function myBookings()
    {
        // Get all bookings for hotels managed by the authenticated user
        $hotelIds = Hotel::where('manager_id', Auth::user()->id)->pluck('id');
        $bookings = HotelBooking::whereIn('hotel_id', $hotelIds)->latest()->get();
        return $this->ok('Hotel bookings fetched successfully', HotelBookingResource::collection($bookings));
    }
    public function allHotels(HotelFilter $filter)
    {
        $hotels = Hotel::with('files', 'manager')->where('is_active', true)->filter($filter)->get();
        return $this->ok('Hotels fetched successfully', HotelResource::collection($hotels));
    }
}
