export function errorFor(errors, keys) {
    for (const key of keys) {
        if (errors?.[key]) {
            return Array.isArray(errors[key]) ? errors[key][0] : errors[key];
        }
    }

    return null;
}

function normalizeMessage(message) {
    return Array.isArray(message) ? message[0] : message;
}

function flattenErrors(errors, prefix = "") {
    return Object.entries(errors ?? {}).reduce((normalized, [key, message]) => {
        const normalizedKey = prefix ? `${prefix}.${key}` : key;

        if (Array.isArray(message) && message.some((item) => item && typeof item === "object")) {
            return {
                ...normalized,
                ...flattenErrors(Object.fromEntries(message.map((item, index) => [index, item])), normalizedKey),
            };
        }

        if (
            message
            && typeof message === "object"
            && !Array.isArray(message)
        ) {
            return {
                ...normalized,
                ...flattenErrors(message, normalizedKey),
            };
        }

        normalized[normalizedKey] = normalizeMessage(message);

        return normalized;
    }, {});
}

export function normalizeErrors(errors) {
    const flattened = flattenErrors(errors);

    return Object.entries(flattened).reduce((normalized, [key, message]) => {
        normalized[key] = message;
        normalized[key.replace(/\.([^.]+)/g, "[$1]")] = message;

        if (key.startsWith("default.")) {
            const unbaggedKey = key.replace(/^default\./, "");

            normalized[unbaggedKey] = message;
            normalized[unbaggedKey.replace(/\.([^.]+)/g, "[$1]")] = message;
        }

        return normalized;
    }, {});
}

export function generalErrors(errors, mappedKeys) {
    const mapped = new Set(mappedKeys);

    return Object.entries(errors ?? {})
        .filter(([key]) => !mapped.has(key))
        .flatMap(([, message]) => Array.isArray(message) ? message : [message])
        .filter(Boolean);
}
