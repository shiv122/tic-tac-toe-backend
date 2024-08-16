<?php

use App\Enums\NotificationType;
use App\Events\BasicNotificationEvent;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // broadcast(new BasicNotificationEvent(
    //     type: NotificationType::WEB,
    //     title: "Testing Web Notification",
    //     body: "This Should be the body on the notification",
    //     icon: "https://nuxt.com/assets/design-kit/icon-green.svg",
    //     onClick: ""
    // ));
    $user = User::all();
    dump($user);
    return [
        'status' => 'success'
    ];
});
