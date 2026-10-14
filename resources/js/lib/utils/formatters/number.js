const compactNumberSuffixes = [
    { value: 1_000_000_000, suffix: "b" },
    { value: 1_000_000, suffix: "m" },
    { value: 1_000, suffix: "k" },
];

export function formatCompactNumber(value) {
    const number = Number(value ?? 0);

    if (!Number.isFinite(number)) {
        return "0";
    }

    const absolute = Math.abs(number);
    const compact = compactNumberSuffixes.find((item) => absolute >= item.value);

    if (!compact) {
        return String(Math.trunc(number));
    }

    const formatted = (number / compact.value)
        .toFixed(absolute >= compact.value * 100 ? 0 : 1)
        .replace(/\.0$/, "");

    return `${formatted}${compact.suffix}`;
}
