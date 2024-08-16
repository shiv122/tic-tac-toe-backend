<?php

namespace App\Http\Controllers\Api\Game;

use App\Events\GameEvent;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function play(Request $request)
    {
        broadcast(new GameEvent($request->game_id, $request->data))->toOthers();
        //Todo Need to add data in DB
        return response([
            'status' => "success",
            'message' => 'Event broadcasted successfully'
        ]);
    }
}
