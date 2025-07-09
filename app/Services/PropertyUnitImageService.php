<?php

namespace App\Services;

use App\Models\PropertyUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PropertyUnitImageService
{
    /**
     * Upload multiple images for a property unit
     */
    public function uploadImages(PropertyUnit $propertyUnit, Request $request): array
    {
        $uploadedPaths = [];
        $errors = [];

        try {
            DB::beginTransaction();

            if ($request->hasFile('images')) {
                $images = $request->file('images');

                // Check if property unit already has a main image
                $hasMainImage = $propertyUnit->images()->where('is_main', true)->exists();

                foreach ($images as $index => $image) {
                    try {
                        $path = $image->storeAs('property/property_unit_images', time() . '_' . $image->getClientOriginalName(), 'public');
                        $uploadedPaths[] = $path;

                        // Set as main image if:
                        // 1. Property unit has no main image AND this is the first image being uploaded, OR
                        // 2. This is the only image being uploaded (total count = 1)
                        $isMain = (!$hasMainImage && $index === 0) || count($images) === 1;

                        $propertyUnit->images()->create([
                            'image_path' => $path,
                            'is_main' => $isMain,
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = "Failed to upload image {$image->getClientOriginalName()}: " . $e->getMessage();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up uploaded files if they exist
            foreach ($uploadedPaths as $path) {
                if (Storage::disk('public')->exists($path)) {
                    Storage::disk('public')->delete($path);
                }
            }

            throw $e;
        }

        return [
            'success' => empty($errors),
            'uploaded_paths' => $uploadedPaths,
            'errors' => $errors
        ];
    }

    /**
     * Update a specific image for a property unit
     */
    public function updateImage(PropertyUnit $propertyUnit, int $imageId, Request $request): array
    {
        $image = $propertyUnit->images()->findOrFail($imageId);
        $oldPath = $image->image_path;
        $newPath = null;

        try {
            DB::beginTransaction();

            // Delete old image file
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Upload new image
            $newPath = $request->file('image')->storeAs('property/property_unit_images', time() . '_' . $request->file('image')->getClientOriginalName(), 'public');

            // Update image record
            $updateData = ['image_path' => $newPath];

            // Handle main image logic
            if ($request->has('is_main') && $request->is_main) {
                // Remove main flag from other images
                $propertyUnit->images()->where('is_main', true)->update(['is_main' => false]);
                $updateData['is_main'] = true;
            }

            $image->update($updateData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            // Clean up new file if it was uploaded
            if ($newPath && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        return [
            'success' => true,
            'old_path' => $oldPath,
            'new_path' => $newPath,
            'image' => $image
        ];
    }

    /**
     * Delete a specific image from a property unit
     */
    public function deleteImage(PropertyUnit $propertyUnit, int $imageId): array
    {
        $image = $propertyUnit->images()->findOrFail($imageId);
        $wasMainImage = $image->is_main;
        $imagePath = $image->image_path;

        try {
            DB::beginTransaction();

            // Delete the image file
            if (Storage::disk('public')->exists($imagePath)) {
                Storage::disk('public')->delete($imagePath);
            }

            // Delete the image record
            $image->delete();

            // If this was the main image, set another image as main (if any exist)
            $newMainImage = null;
            if ($wasMainImage) {
                $newMainImage = $propertyUnit->images()->first();
                if ($newMainImage) {
                    $newMainImage->update(['is_main' => true]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'deleted_path' => $imagePath,
            'was_main_image' => $wasMainImage,
            'new_main_image' => $newMainImage
        ];
    }

    /**
     * Set an image as the main image for a property unit
     */
    public function setMainImage(PropertyUnit $propertyUnit, int $imageId): array
    {
        $image = $propertyUnit->images()->findOrFail($imageId);

        try {
            DB::beginTransaction();

            // Remove main flag from other images
            $propertyUnit->images()->where('is_main', true)->update(['is_main' => false]);

            // Set this image as main
            $image->update(['is_main' => true]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'main_image' => $image
        ];
    }

    /**
     * Get all images for a property unit
     */
    public function getImages(PropertyUnit $propertyUnit): array
    {
        return [
            'images' => $propertyUnit->images()->orderBy('is_main', 'desc')->get(),
            'main_image' => $propertyUnit->images()->where('is_main', true)->first(),
            'total_count' => $propertyUnit->images()->count()
        ];
    }

    /**
     * Reorder images for a property unit
     */
    public function reorderImages(PropertyUnit $propertyUnit, array $imageIds): array
    {
        try {
            DB::beginTransaction();

            foreach ($imageIds as $index => $imageId) {
                $image = $propertyUnit->images()->find($imageId);
                if ($image) {
                    $image->update(['order' => $index + 1]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return [
            'success' => true,
            'reordered_images' => $propertyUnit->images()->orderBy('order')->get()
        ];
    }
}
