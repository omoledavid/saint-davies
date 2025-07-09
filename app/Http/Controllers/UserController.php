<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\UserResource;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    use ApiResponses;
    public function getProfile()
    {
        $user = auth()->user();
        return $this->ok('Profile fetched successfully', new UserResource($user));
    }
    public function updateProfile(ProfileUpdateRequest $request)
    {
        $user = auth()->user();
        $user->update($request->all());
        return $this->ok('Profile updated successfully', new UserResource($user));
    }
    public function updatePassword(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect');
        }
        $user->update(['password' => Hash::make($request->new_password)]);
        return $this->ok('Password updated successfully');
    }
    public function updateImage(Request $request)
    {
        $user = auth()->user();
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = time() . '_' . $image->getClientOriginalName();
            $imagePath = $image->storeAs('users/images', $imageName, 'public');

            // Delete old image if exists
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }

            $user->update(['image' => $imagePath]);
        }
        return $this->ok('Image updated successfully');
    }
}
