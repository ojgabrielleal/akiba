<?php

namespace App\Actions\Profile;

use App\Models\User;

use App\Services\Process\ImageProcessService;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

class UpdateProfileAction
{
    public function __construct(
        private ImageProcessService $image,
    ) {}

    public function execute(User $user, array $data, ?UploadedFile $avatar = null): User
    {
        return DB::transaction(function () use ($user, $data, $avatar) {
            $user->fill([
                'avatar' => $this->image->store('users', $avatar, $user->avatar),
                'is_virtual' => $data['is_virtual'] ?? $user->is_virtual,
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
                $likes = $data['preferences']['likes'] ?? [];
                $unlikes = $data['preferences']['unlikes'] ?? [];

                foreach (collect($likes)->merge($unlikes) as $preference) {
                    $user->preferences()->where('uuid', $preference['uuid'])->update([
                        'content' => $preference['content'],
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
