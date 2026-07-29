export const resolveStatusBackground = (item, options = {}) => {
    const { useValidity = true } = options;

    if (useValidity && item.is_valid) {
        return "bg-purple-mystic";
    }

    const statusBackground = {
        draft: "bg-orange-amber",
        revision: "bg-green-mint",
        published: "bg-blue-cerulean",
    }[item.status];

    return statusBackground ?? "bg-blue-cerulean";
};
