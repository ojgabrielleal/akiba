<?php

namespace App\Actions\Profile;

use Illuminate\Support\Facades\DB;
use App\Services\Process\ImageProcessService;

use App\Models\User;

class UpdateUserAction
{
    private ImageProcessService $image;

    public function __construct(ImageProcessService $image)
    {
        $this->image = $image;
    }

    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data,) {
            $user->fill([
                'avatar' => $this->image->store('users', $data['avatar'], 'public', $user->avatar),
                'is_virtual' => $data['is_virtual'],
                'name' => $data['name'],
                'nickname' => $data['nickname'],
                'gender' => $data['gender'],
                'birthday' => $data['birthday'],
                'city' => $data['city'],
                'state' => $data['state'],
                'country' => $data['country'],
                'bibliography' => $data['bibliography'],
            ]);

            if ($user->isDirty()) {
                $user->save();
            }

            if (!empty($data['socials'])) {
                foreach ($data['socials'] as $social) {
                    $user->socials()->where('uuid', $social['uuid'])->update([
                        'name' => $social['name'],
                        'url' => $social['url'],
                    ]);
                }
            }

            if (!empty($data['preferences'])) {
                $preferences = $data['preferences'];

                $likes = $preferences['likes'];
                $unlikes = $preferences['unlikes'];

                foreach (collect($likes)->merge($unlikes) as $preference) {
                    $user->preferences()->where('uuid', $preference['uuid'])->update([
                        'content' => $preference['content']
                    ]);
                }
            }

            if (!empty($data['favorites'])) {
                foreach ($data['favorites'] as $favorite) {
                    $user->favorites()->where('uuid', $favorite['uuid'])->update([
                        'name' => $favorite['name'],
                        'image' => $favorite['image'],
                    ]);
                }
            }

            return $user;
        });
    }
}
