import { page } from '@inertiajs/svelte';
import { get } from 'svelte/store';

export function hasPermission(permission) {
    const permissions = get(page).props.user.permissions ?? [];
    return permissions.includes(permission);
}

export const activityPermissions = () => ({
    create: hasPermission("activity.create"),
    participate: hasPermission("activity.participate"),
    update: hasPermission("activity.update"),
});

export const calendarPermissions = () => ({
    create: hasPermission("calendar.create"),
    update: hasPermission("calendar.update"),
});

export const eventPermissions = () => ({
    create: hasPermission("event.create"),
    deactivate: hasPermission("event.deactivate"),
});

export const listenerMonthPermissions = () => ({
    set: hasPermission("listener.month.set"),
});

export const locutionPermissions = () => ({
    start: hasPermission("locution.start"),
    finish: hasPermission("locution.finish"),
});

export const musicPermissions = () => ({
    update: hasPermission("music.update"),
    set: hasPermission("music.set.ranking"),
});

export const podcastPermissions = () => ({
    create: hasPermission("podcast.create"),
    update: hasPermission("podcast.update"),
    deactivate: hasPermission("podcast.deactivate"),
});

export const pollPermissions = () => ({
    create: hasPermission("poll.create"),
    update: hasPermission("poll.update"),
    deactivate: hasPermission("poll.deactivate"),
    vote: {
        create: hasPermission("poll.create.vote"),
    },
});

export const postPermissions = () => ({
    create: hasPermission("post.create"),
    update: hasPermission("post.update"),
    publish: hasPermission("post.publish"),
    approve: hasPermission("post.approve"),
    deactivate: hasPermission("post.deactivate"),
});

export const programPermissions = () => ({
    create: hasPermission("program.create"),
    update: hasPermission("program.update"),
    deactivate: hasPermission("program.deactivate"),
});

export const programFormPermissions = () => ({
    create: hasPermission("program.tete"),
    update: hasPermission("program.update"),
});

export const repositoryPermissions = () => ({
    create: hasPermission("repository.create"),
    update: hasPermission("repository.update"),
    deactivate: hasPermission("repository.deactivate"),
});

export const rolePermissions = () => ({
    create: hasPermission("role.create"),
    update: hasPermission("role.update"),
    remove: hasPermission("role.remove"),
});

export const songRequestPermissions = () => ({
    list: hasPermission("songrequest.list"),
    toggle: hasPermission("songrequest.toggle"),
    reproduce: hasPermission("songrequest.reproduce"),
    cancel: hasPermission("songrequest.cancel"),
    locution: {
        finish: hasPermission("locution.finish"),
    },
});

export const taskPermissions = () => ({
    create: hasPermission("task.create"),
    update: hasPermission("task.update"),
    completed: hasPermission("task.complete"),
});

export const userPermissions = () => ({
    create: hasPermission("user.create"),
    update: hasPermission("user.update"),
    deactivate: hasPermission("user.deactivate"),
    access: {
        update: hasPermission("user.access.update"),
    },
    authority: {
        update: hasPermission("user.update.authority"),
    },
});

export const userGridPermissions = () => ({
    ...userPermissions(),
    activity: {
        create: hasPermission("activity.create"),
    },
});

export const quickAccessPermissions = () => ({
    activity: {
        create: hasPermission("activity.create"),
    },
    post: {
        create: hasPermission("post.create"),
    },
    repository: {
        create: hasPermission("repository.create"),
    },
    locution: {
        start: hasPermission("locution.start"),
    },
    event: {
        create: hasPermission("event.create"),
    },
});
