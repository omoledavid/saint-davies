<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\FileUpload;
use Illuminate\Http\UploadedFile;

class FileUploadService
{
    public function uploadFiles($model, Request $request, string $inputName = 'files'): array
    {
        $uploadedPaths = [];
        $errors = [];

        try {
            DB::beginTransaction();

            if ($request->hasFile($inputName)) {
                $files = $request->file($inputName);
                $hasMain = $model->files()->where('is_main', true)->exists();

                foreach ($files as $index => $file) {
                    try {
                        $filename = time() . '_' . $file->getClientOriginalName();
                        $path = $file->storeAs('uploads', $filename, 'public');

                        $uploadedPaths[] = $path;

                        $isMain = (!$hasMain && $index === 0) || count($files) === 1;

                        $model->files()->create([
                            'file_path' => $path,
                            'file_type' => $file->getClientMimeType(),
                            'file_name' => $file->getClientOriginalName(),
                            'is_main' => $isMain,
                            'order' => $index + 1,
                        ]);
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($uploadedPaths as $path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }

        return [
            'success' => empty($errors),
            'uploaded_paths' => $uploadedPaths,
            'errors' => $errors
        ];
    }

    public function updateFile(FileUpload $file, UploadedFile $newFile, array $options = []): array
    {
        $oldPath = $file->file_path;
        $newPath = null;

        try {
            DB::beginTransaction();

            // Delete old file
            if (Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }

            // Upload new file
            $filename = time() . '_' . $newFile->getClientOriginalName();
            $newPath = $newFile->storeAs('uploads', $filename, 'public');

            // Update file record
            $file->update([
                'file_path' => $newPath,
                'file_type' => $newFile->getClientMimeType(),
                'file_name' => $newFile->getClientOriginalName(),
                ...$options, // e.g. ['is_main' => true]
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();

            if ($newPath && Storage::disk('public')->exists($newPath)) {
                Storage::disk('public')->delete($newPath);
            }

            throw $e;
        }

        return [
            'success' => true,
            'old_path' => $oldPath,
            'new_path' => $newPath,
            'file' => $file
        ];
    }

    public function deleteFile(FileUpload $file): bool
    {
        DB::beginTransaction();

        try {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }

            $file->delete();

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function setMainFile($model, int $fileId): bool
    {
        DB::beginTransaction();

        try {
            $model->files()->update(['is_main' => false]);
            $model->files()->where('id', $fileId)->update(['is_main' => true]);
            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function reorderFiles($model, array $orderedIds): void
    {
        DB::beginTransaction();

        try {
            foreach ($orderedIds as $index => $id) {
                $model->files()->where('id', $id)->update(['order' => $index + 1]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
