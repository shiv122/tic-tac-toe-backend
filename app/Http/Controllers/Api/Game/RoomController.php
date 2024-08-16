<?php

namespace App\Http\Controllers\Api\Game;

use App\Models\Room;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class RoomController extends Controller
{
    public function create(Request $request)
    {
        $user = $request->user();

        $room =   Room::create([
            'user_id' => $user->id,
            'room_code' => Str::uuid(),
        ]);

        return response([
            "room" => $room
        ], 201);
    }


    public function join(Request $request)
    {
        $request->validate([
            'room_id' => 'required|string|max:255'
        ]);

        $room = Room::where('room_code', $request->room_id)->firstOrFail();

        return response([
            "room" => $room
        ], 200);
    }

    public function list(Request $request)
    {
        $user = $request->user();
        $rooms = Room::where('user_id', $user->id)->orderBy('id', 'desc')->get();
        return response([
            'rooms' => $rooms
        ]);
    }
}
