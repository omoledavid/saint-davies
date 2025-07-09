<?php

namespace App\Http\Controllers;

use App\Http\Resources\HotelRoomResource;
use App\Models\HotelRoom;
use App\Traits\ApiResponses;
use Illuminate\Http\Request;

class HotelRoomController extends Controller
{
    use ApiResponses;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_category_id' => 'required|exists:hotel_room_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_number' => 'nullable|string|max:255',
        ]);
        $hotelRoom = HotelRoom::create($validatedData);
        return $this->ok('Hotel room created successfully', new HotelRoomResource($hotelRoom));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate([
            'hotel_id' => 'required|exists:hotels,id',
            'room_category_id' => 'required|exists:hotel_room_categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'room_number' => 'nullable|string|max:255',
        ]);
        $hotelRoom = HotelRoom::findOrFail($id);
        $hotelRoom->update($validatedData);
        return $this->ok('Hotel room updated successfully', new HotelRoomResource($hotelRoom));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $hotelRoom = HotelRoom::find($id);
        if (!$hotelRoom) {
            return $this->error('Hotel room not found.');
        }
        if ($hotelRoom->hotel->manager_id !== auth()->id()) {
            return $this->error('You do not have permission to delete this hotel room.');
        }
        $hotelRoom->delete();
        return $this->ok('Hotel room deleted successfully');
    }
}
