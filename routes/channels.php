<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});


Broadcast::channel('private-presence', function ($user) {
    return [
        'id' => $user->id,
        'uuid' => $user->uuid,
        'name' => $user->name,
        'nickname' => $user->nickname,
        'avatar' => $user->avatar,
        'gender' => $user->gender,
    ];
});