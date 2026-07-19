export const placeholderImages = {
    avatar: "/img/placeholders/avatar.webp",
    placeholder: "/img/placeholders/placeholder.webp",
    program: "/img/placeholders/program.webp",
};

export const resolvePlaceholderImage = (image, placeholder = "avatar") => {
    return image || placeholderImages[placeholder] || placeholderImages.avatar;
};
