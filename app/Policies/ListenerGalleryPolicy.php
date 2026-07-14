<?php

namespace App\Policies;

use App\Models\ListenerGallery;
use App\Models\User;

class ListenerGalleryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('listener.gallery.list');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ListenerGallery $listenerGallery): bool
    {
        return $user->hasPermission('listener.gallery.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('listener.gallery.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ListenerGallery $listenerGallery): bool
    {
        return $user->hasPermission('listener.gallery.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ListenerGallery $listenerGallery): bool
    {
        return $user->hasPermission('listener.gallery.remove');
    }
}
