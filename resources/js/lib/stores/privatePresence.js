import { readable } from "svelte/store";
import { echo } from "@/echo";

const initialState = {
    users: [],
    status: "connecting",
    error: null,
};

function uniqueUsers(users) {
    return Array.from(
        new Map(users.map((user) => [user.id, user])).values(),
    );
}

export const privatePresence = readable(initialState, (set) => {
    let users = [];
    const channel = echo.join("private-presence");

    channel
        .here((currentUsers) => {
            users = uniqueUsers(currentUsers);
            set({ users, status: "connected", error: null });
        })
        .joining((user) => {
            users = uniqueUsers([...users, user]);
            set({ users, status: "connected", error: null });
        })
        .leaving((user) => {
            users = users.filter((currentUser) => currentUser.id !== user.id);
            set({ users, status: "connected", error: null });
        })
        .error((error) => {
            set({ users, status: "error", error });
        });

    return () => {
        echo.leave("private-presence");
    };
});
