<?php

namespace App\Manager;

use Pusher\Pusher;
use App\Events\GameEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Validation\ValidationException;

class GameManager
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }


    public function processMove(array $data, $user): array
    {
        $gameId = $data['game_id'];
        $playerId = $user->id;
        $i = $data['data']['grid']['i'];
        $j = $data['data']['grid']['j'];

        // Retrieve and decode the game state from Redis
        $gameState = json_decode(Redis::get("game:state:$gameId"), true);

        $usersInRoom = $this->getChannelUsers($gameId);
        $otherPlayer = $this->getOtherPlayer($usersInRoom, $playerId);

        // Validate player status and game conditions
        $this->validateGameState($gameState, $usersInRoom, $playerId, $i, $j);

        // Make the move
        $gameState['grid'][$i][$j] = $playerId;
        $broadcast = $gameState;

        $gameState = match (true) {
            $this->checkWinner($gameState['grid'], $playerId) => $this->handleWin($gameId, $gameState, $playerId, $otherPlayer, $broadcast),
            $this->isDraw($gameState['grid']) => $this->handleDraw($gameId, $gameState, $playerId, $otherPlayer, $broadcast),
            default => $this->continueGame($gameId, $gameState, $playerId, $otherPlayer, $broadcast),
        };

        // Broadcast the event and log the game state
        broadcast(new GameEvent($gameId, $broadcast))->toOthers();
        Log::info(json_encode($gameState));

        return [
            'status' => 'success',
            'message' => 'Move processed successfully',
            'data' => $gameState,
        ];
    }

    private function validateGameState(array $gameState, array $usersInRoom, int $playerId, int $i, int $j): void
    {
        if ($gameState['gameover'] ?? false) {
            throw ValidationException::withMessages(['message' => "Game is over"]);
        }

        if ($gameState['grid'][$i][$j] !== null) {
            throw ValidationException::withMessages(['message' => "Cell already occupied"]);
        }

        if (!$this->isPlayerInRoom($usersInRoom, $playerId)) {
            throw ValidationException::withMessages(['message' => "You are not in room"]);
        }

        if (count($usersInRoom) < 2) {
            throw ValidationException::withMessages(['message' => "There's only one player in room"]);
        }
    }

    private function isPlayerInRoom(array $usersInRoom, int $playerId): bool
    {
        return collect($usersInRoom)->contains(fn($user) => $user->id == $playerId);
    }

    private function getOtherPlayer(array $usersInRoom, int $playerId): ?int
    {
        foreach ($usersInRoom as $usr) {
            if ($usr->id != $playerId) {
                return $usr->id;
            }
        }

        return null;
    }

    private function handleWin(string $gameId, array $gameState, int $winnerId, int $loserId, array &$broadcast): array
    {
        $gameState['winner'] = $winnerId;
        $gameState['gameover'] = true;
        $broadcast = $gameState;
        Redis::set("game:state:$gameId", json_encode($gameState));
        $this->resetGameState($gameId, $winnerId, $loserId);
        return $gameState;
    }

    private function handleDraw(string $gameId, array $gameState, int $playerId, int $otherPlayerId, array &$broadcast): array
    {
        $gameState['gameover'] = true;
        $gameState['draw'] = true;
        $broadcast = $gameState;

        $temp = [$playerId, $otherPlayerId];
        $chance = rand(0, 1);
        if ($chance) {
            $broadcast['current_player'] = $otherPlayerId;
            $broadcast['player'] = $playerId;
        }

        return $this->resetGameState($gameId, $temp[$chance], $temp[!$chance]);
    }

    private function continueGame(string $gameId, array $gameState, int $playerId, int $otherPlayerId, array &$broadcast): array
    {
        $gameState['current_player'] = $otherPlayerId;
        $gameState['player'] = $playerId;
        $broadcast = $gameState;
        Redis::set("game:state:$gameId", json_encode($gameState));
        return $gameState;
    }

    private function checkWinner($grid, $player)
    {
        // Horizontal, vertical, and diagonal checks
        for ($i = 0; $i < 3; $i++) {
            if ($grid[$i][0] === $player && $grid[$i][1] === $player && $grid[$i][2] === $player) return true;
            if ($grid[0][$i] === $player && $grid[1][$i] === $player && $grid[2][$i] === $player) return true;
        }
        if ($grid[0][0] === $player && $grid[1][1] === $player && $grid[2][2] === $player) return true;
        if ($grid[0][2] === $player && $grid[1][1] === $player && $grid[2][0] === $player) return true;

        return false;
    }

    private function isDraw($grid)
    {
        foreach ($grid as $row) {
            if (in_array(null, $row)) {
                return false;
            }
        }
        return true;
    }


    public function getChannelUsers(string $channelId): null|array
    {
        $connection = config('reverb.apps.apps')[0];
        $pusher = new Pusher(
            $connection['key'],
            $connection['secret'],
            $connection['app_id'],
            $connection['options'] ?? []
        );

        // Example 1: get all active channels
        $data = $pusher->getPresenceUsers('presence-room.' . $channelId);
        return $data?->users;
    }


    private function resetGameState(string $gameId, $current, $player)
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

        return $gameState;
    }
}
