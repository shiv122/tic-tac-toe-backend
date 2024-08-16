<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('room.{roomId}', function (User $user) {
    return ['user' => $user];
}, ['guards' => 'api']);
Broadcast::channel('game.{roomId}', function (User $user) {
    //can add some checks like only invited player are allowed
    return true;
}, ['guards' => 'api']);
