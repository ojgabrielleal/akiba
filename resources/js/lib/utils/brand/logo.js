export const defaultBrandLogo = {
    src: "/img/brand/logo.webp",
    alt: "Rede Akiba",
    key: "default",
};

export const commemorativeLogoVariants = [
    {
        key: "akiba-birthday",
        src: "/img/brand/logo-birthday.webp",
        alt: "Rede Akiba - Aniversário",
        start: "10-09",
        end: "10-31",
        startTime: "20:00",
        endTime: "23:59",
    },
];

const dateParts = (date) => {
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const day = String(date.getDate()).padStart(2, "0");
    const hours = String(date.getHours()).padStart(2, "0");
    const minutes = String(date.getMinutes()).padStart(2, "0");

    return {
        monthDay: `${month}-${day}`,
        time: `${hours}:${minutes}`,
        comparable: `${month}-${day} ${hours}:${minutes}`,
    };
};

const isDateInRecurringRange = (date, start, end) => {
    const current = dateParts(date).monthDay;

    if (start <= end) {
        return current >= start && current <= end;
    }

    return current >= start || current <= end;
};

const comparableDateTime = (monthDay, time) => `${monthDay} ${time}`;

const isDateTimeInRecurringRange = (date, variant) => {
    const startTime = variant.startTime ?? "00:00";
    const endTime = variant.endTime ?? "23:59";
    const parts = dateParts(date);
    const start = comparableDateTime(variant.start, startTime);
    const end = comparableDateTime(variant.end, endTime);

    if (start <= end) {
        return parts.comparable >= start && parts.comparable <= end;
    }

    return parts.comparable >= start || parts.comparable <= end;
};

export function resolveCommemorativeLogo(date = new Date(), variants = commemorativeLogoVariants) {
    const logo = variants.find((variant) => (
        variant.startTime || variant.endTime
            ? isDateTimeInRecurringRange(date, variant)
            : isDateInRecurringRange(date, variant.start, variant.end)
    ));

    return logo ?? defaultBrandLogo;
}
