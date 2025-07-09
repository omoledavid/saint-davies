<?php

namespace App\Http\Controllers;

use App\Http\Requests\CarCreationRequest;
use App\Http\Resources\CarHireResource;
use App\Http\Resources\CarResource;
use App\Models\Car;
use App\Models\CarHire;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CarController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $cars = Car::where('manager_id', Auth::user()->id)->with('files', 'manager')->get();
        return $this->ok('Cars retrieved successfully', CarResource::collection($cars));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CarCreationRequest $request)
    {
        $data = $request->validated();
        $data['manager_id'] = Auth::user()->id;
        $car = Car::create($data);
        return $this->ok('Car created successfully', new CarResource($car->load('files', 'manager')));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CarCreationRequest $request, string $id)
    {
        $data = $request->validated();
        $car = Car::find($id);
        if (!$car) {
            return $this->error('Car not found');
        }
        if ($car->manager_id !== Auth::user()->id) {
            return $this->error('You do not have permission to update this car');
        }
        $car->update($data);
        return $this->ok('Car updated successfully', new CarResource($car->load('files', 'manager')));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $car = Car::find($id);
        if (!$car) {
            return $this->error('Car not found');
        }
        if ($car->manager_id !== Auth::user()->id) {
            return $this->error('You do not have permission to delete this car');
        }
        $car->delete();
        return $this->ok('Car deleted successfully');
    }
    public function uploadImages(Request $request, string $id)
    {
        $user = Auth::user();
        $car = Car::findOrFail($id);

        if ($car->manager_id !== $user->id) {
            return $this->error('Unauthorized access to car', 403);
        }

        $request->validate([
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            $fileService = new \App\Services\FileUploadService();
            $response = $fileService->uploadFiles($car, $request, 'images');

            if (!$response['success']) {
                return $this->error('Some images failed to upload', 400, $response['errors']);
            }

            return $this->ok('Car images uploaded successfully', [
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
        $user = Auth::user();
        $file = \App\Models\FileUpload::findOrFail($fileId);
        $car = $file->uploadable;

        if ($car->manager_id !== $user->id) {
            return $this->error('Unauthorized access to car', 403);
        }

        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
        $newFile = $request->file('image');

        try {
            $fileService = new \App\Services\FileUploadService();
            $response = $fileService->updateFile($file, $newFile);
            return $this->ok('Image updated successfully', [
                'updated_file' => asset('storage/' . $response['new_path'])
            ]);
        } catch (\Exception $e) {
            return $this->error('Failed to update image', 500, $e->getMessage());
        }
    }

    /**
     * Delete a specific image from a car
     */
    public function deleteImage(string $fileId)
    {
        $file = \App\Models\FileUpload::findOrFail($fileId);
        $car = $file->uploadable;
        $user = Auth::user();

        if ($car->manager_id !== $user->id) {
            return $this->error('Unauthorized access to car', 403);
        }

        try {
            $fileService = new \App\Services\FileUploadService();
            $fileService->deleteFile($file);
            return $this->ok('Image deleted successfully', new CarResource($car->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to delete image', 500, $e->getMessage());
        }
    }

    /**
     * Set an image as the main image for a car
     */
    public function setMainImage(string $fileId)
    {
        $user = Auth::user();
        $file = \App\Models\FileUpload::findOrFail($fileId);
        $car = $file->uploadable;

        if ($car->manager_id !== $user->id) {
            return $this->error('Unauthorized access to car', 403);
        }

        try {
            $fileService = new \App\Services\FileUploadService();
            $fileService->setMainFile($car, $fileId);
            return $this->ok('Main image set successfully', new CarResource($car->load('files')));
        } catch (\Exception $e) {
            return $this->error('Failed to set main image', 500, $e->getMessage());
        }
    }

    /**
     * Reorder images for a car
     */
    public function reorderImages(Request $request, string $carId)
    {
        $request->validate([
            'file_ids' => 'required|array',
            'file_ids.*' => 'integer|exists:file_uploads,id',
        ]);
        $car = Car::findOrFail($carId);
        $fileService = new \App\Services\FileUploadService();
        $fileService->reorderFiles($car, $request->file_ids);
        return $this->ok('Images reordered successfully', [
            'order_files' => CarResource::collection($car->files()->orderBy('order')->get())
        ]);
    }
    public function myHires()
    {
        // Get all cars managed by the authenticated user
        $carIds = Car::where('manager_id', Auth::user()->id)->pluck('id');
        // Get all car hires for those cars
        $hires = CarHire::whereIn('car_id', $carIds)->latest()->get();
        return $this->ok('Car hires fetched successfully', CarHireResource::collection($hires));
    }
}
