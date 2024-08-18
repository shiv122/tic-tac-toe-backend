<?php

namespace App\Http\Controllers\Api\Game;

use Pusher\Pusher;
use App\Models\Room;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

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
        $user = $request->user();
        $room = Room::where('room_code', $request->room_id)->firstOrFail();
        $gameId = $request->room_id;
        $gameState = Redis::get("game:state:$gameId");
        $channelUsers = $this->getChannelUsers($request->room_id);
        if (!$gameState && count($channelUsers) <= 1) {
            $gameState = json_encode([
                'grid' => [
                    [null, null, null],
                    [null, null, null],
                    [null, null, null]
                ],
                'gameover' => false,
                'winner' => null,
                'draw' => null,
                'player' => $user->id,
                'current_player' => $user->id,
            ]);
        }

        Redis::set("game:state:$gameId", $gameState);



        if (count($channelUsers) >= 2) {
            throw ValidationException::withMessages(['message' => 'Room is full']);
        }
        // Redis::del("game:state:{$room->room_code}");
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


    public function state(Request $request)
    {
        $request->validate(['game_id' => 'required|string']);
        $user = $request->user();
        $state = Redis::get("game:state:" . $request->game_id);
        if (!$state) {
            $this->resetGameState($request->game_id, $user->id, $user->id);
        }
        return response(['state' => $state]);
    }


    private function getChannelUsers(string $channelId): array
    {
        $connection = config('reverb.apps.apps')[0];
        $data = [];
        $pusher = new Pusher(
            $connection['key'],
            $connection['secret'],
            $connection['app_id'],
            $connection['options'] ?? []
        );

        // Example 1: get all active channels
        $occupied = $pusher->getChannelInfo('presence-room.' . $channelId)->occupied;
        if ($occupied) {
            $data = $pusher->getPresenceUsers('presence-room.' . $channelId);
        }
        return $data?->user ?? [];
    }

    public function resetGameState(string $gameId, $current, $player)
    {

        $gameState = [
            'grid' => [
                [null, null, null],
                [null, null, null],
                [null, null, null]
            ],
            'gameover' => false,
            'winner' => null,
            'draw' => null,
            'player' => $player,
            'current_player' => intval($current)
        ];

        Redis::set("game:state:$gameId", json_encode($gameState));
    }
}
