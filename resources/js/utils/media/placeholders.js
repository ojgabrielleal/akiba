export const placeholderImages = {
    avatar: "/img/placeholders/avatar.webp",
    avatarFemale: "/img/placeholders/avatar-female.webp",
    avatarMale: "/img/placeholders/avatar-male.webp",
    placeholder: "/img/placeholders/placeholder.webp",
    program: "/img/placeholders/program.webp",
};

export const resolvePlaceholderImage = (image, placeholder = "avatar", gender = null) => {
    const avatarPlaceholders = [
        placeholderImages.avatar,
        placeholderImages.avatarFemale,
        placeholderImages.avatarMale,
    ];

    if (placeholder === "avatar" && (!image || avatarPlaceholders.includes(image))) {
        if (gender === "male") return placeholderImages.avatarMale;
        if (gender === "female") return placeholderImages.avatarFemale;

        return placeholderImages.avatar;
    }

    return image || placeholderImages[placeholder] || placeholderImages.avatar;
};
