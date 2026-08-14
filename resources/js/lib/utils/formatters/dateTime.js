const dayNames = {
    long: {
        0: "Domingo",
        1: "Segunda-feira",
        2: "Terça-feira",
        3: "Quarta-feira",
        4: "Quinta-feira",
        5: "Sexta-feira",
        6: "Sábado",
    },
    short: {
        0: "Domingo",
        1: "Segunda",
        2: "Terça",
        3: "Quarta",
        4: "Quinta",
        5: "Sexta",
        6: "Sábado",
    },
};

export const resolveDay = (day, format = "long") => {
    return dayNames[format]?.[day] ?? dayNames.long[day] ?? "";
};

export const resolveHour = (hour) => {
    if (!hour) {
        return "";
    }

    if (hour instanceof Date) {
        const hours = String(hour.getHours()).padStart(2, "0");
        const minutes = String(hour.getMinutes()).padStart(2, "0");

        return `${hours}h${minutes}`;
    }

    const [hours, minutes] = String(hour).split(":");

    if (!hours || !minutes) {
        return String(hour);
    }

    return `${hours.padStart(2, "0")}h${minutes.padStart(2, "0")}`;
};

export const resolveDateTime = (dateTime) => {
    if (!dateTime) {
        return "";
    }

    const value = String(dateTime);

    if (value.includes("T")) {
        const [date, hour] = value.split("T");
        const [year, month, day] = date.split("-");

        if (!year || !month || !day || !hour) {
            return value;
        }

        return `${day}/${month}/${year} - ${resolveHour(hour)}`;
    }

    const [date, hour] = value.split(" - ");

    if (!date || !hour) {
        return value;
    }

    return `${date} - ${resolveHour(hour)}`;
};

export const resolveDate = (date) => {
    if (!date) {
        return "";
    }

    const value = String(date);
    const [year, month, day] = value.split("-");

    if (!year || !month || !day) {
        return value;
    }

    return `${day}/${month}/${year}`;
};

export const resolveAge = (birthDate) => {
    if (!birthDate) {
        return "";
    }

    const date = new Date(birthDate);

    if (isNaN(date.getTime())) {
        return "";
    }

    const today = new Date();

    let age = today.getFullYear() - date.getFullYear();

    const hasBirthdayPassed =
        today.getMonth() > date.getMonth() ||
        (today.getMonth() === date.getMonth() &&
            today.getDate() >= date.getDate());

    if (!hasBirthdayPassed) {
        age--;
    }

    return age;
};
