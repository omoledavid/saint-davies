<?php

namespace App\Http\Controllers;

use App\Http\Requests\HotelRoomCategoryCreateRequest;
use App\Http\Resources\HotelRoomCategoryResource;
use App\Models\HotelRoomCategory;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class HotelRoomCategoryController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = HotelRoomCategory::whereHas('hotel', function ($query) {
            $query->where('manager_id', Auth::user()->id);
        })->with('hotel')->get();

        return $this->ok('Hotel room categories retrieved successfully', HotelRoomCategoryResource::collection($categories));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(HotelRoomCategoryCreateRequest $request)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('hotel_room_categories', 'public');
        }
        $hotelRoomCategory = HotelRoomCategory::create($data);
        return $this->ok('Hotel room category created successfully', new HotelRoomCategoryResource($hotelRoomCategory));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(HotelRoomCategoryCreateRequest $request, string $id)
    {
        $data = $request->validated();
        if ($request->hasFile('image')) {
            // Delete the old image if it exists before storing the new one
            $hotelRoomCategory = HotelRoomCategory::find($id);
            if ($hotelRoomCategory && $hotelRoomCategory->image) {
                Storage::disk('public')->delete($hotelRoomCategory->image);
            }
            $data['image'] = $request->file('image')->store('hotel_room_categories', 'public');
        }
        $hotelRoomCategory = HotelRoomCategory::find($id);
        if (!$hotelRoomCategory) {
            return $this->error('Hotel room category not found');
        }
        if ($hotelRoomCategory->hotel->manager_id !== Auth::user()->id) {
            return $this->error('You do not have permission to update this hotel room category');
        }
        $hotelRoomCategory->update($data);
        return $this->ok('Hotel room category updated successfully', new HotelRoomCategoryResource($hotelRoomCategory));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
