export const resolveStatusBackground = (item, options = {}) => {
    const { useValidity = true } = options;

    if (useValidity && item.is_valid) {
        return "bg-purple-mystic";
    }

    const statusBackground = {
        draft: "bg-green-mint",
        revision: "bg-orange-amber",
        published: "bg-blue-cerulean",
    }[item.status];

    return statusBackground ?? "bg-blue-cerulean";
};
