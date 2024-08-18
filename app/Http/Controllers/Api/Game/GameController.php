<?php

namespace App\Http\Controllers\Api\Game;

use Log;
use Pusher\Pusher;
use App\Models\Room;
use App\Events\GameEvent;
use App\Manager\GameManager;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

class GameController extends Controller
{
    public function play(Request $request, GameManager $gameManager)
    {
        $data = $request->validate([
            'game_id' => 'required|uuid',
            'data.grid.i' => 'required|integer|min:0|max:2',
            'data.grid.j' => 'required|integer|min:0|max:2',
            'data.player' => 'required|numeric',
        ]);

        $user = $request->user();
        $response = $gameManager->processMove($data, $user);

        return response()->json($response);
    }
}
