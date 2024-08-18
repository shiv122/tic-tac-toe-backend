<?php

use Pusher\Pusher;
use App\Models\User;
use App\Events\GameEvent;
use App\Enums\NotificationType;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use App\Events\BasicNotificationEvent;

Route::get('/', function () {
    // broadcast(new BasicNotificationEvent(
    //     type: NotificationType::WEB,
    //     title: "Testing Web Notification",
    //     body: "This Should be the body on the notification",
    //     icon: "https://nuxt.com/assets/design-kit/icon-green.svg",
    //     onClick: ""
    // ));
    $user = User::all();
    $connection = config('reverb.apps.apps')[0];
    $pusher = new Pusher(
        $connection['key'],
        $connection['secret'],
        $connection['app_id'],
        $connection['options'] ?? []
    );

    // Example 1: get all active channels
    // $channels = $pusher->getPresenceUsers('presence-room.5b99f360-9952-4a43-b8be-821503d2f702');
    // $channels = $pusher->getChannelInfo('presence-room.5b99f360-9952-4a43-b8be-821503d2f702');
    Redis::del('game:state:5b99f360-9952-4a43-b8be-821503d2f702');
    // dd($pusher->getPresenceUsers('presence-room.5b99f360-9952-4a43-b8be-821503d2f702')->users);
    dd(Redis::get("game:state:5b99f360-9952-4a43-b8be-821503d2f702"));
    dump($user);
    return [
        'status' => 'success'
    ];
});
