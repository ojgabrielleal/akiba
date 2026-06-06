const JIKAN_BASE_URL = "https://api.jikan.moe/v4";

function buildUrl(endpoint, params = {}) {
    const url = new URL(`${JIKAN_BASE_URL}${endpoint}`);

    Object.entries(params).forEach(([key, value]) => {
        if (value === undefined || value === null || value === "") {
            return;
        }

        url.searchParams.set(key, value);
    });

    return url.toString();
}

async function request(endpoint, params = {}) {
    const response = await fetch(buildUrl(endpoint, params), {
        headers: {
            Accept: "application/json",
        },
    });

    if (!response.ok) {
        throw new Error(`Jikan request failed with status ${response.status}`);
    }

    return response.json();
}

export function searchAnimes(query, params = {}) {
    return request("/anime", {
        q: query,
        ...params,
    });
}

export function getAnime(id) {
    return request(`/anime/${id}`);
}

export function getAnimeCharacters(id) {
    return request(`/anime/${id}/characters`);
}

export function getAnimeRecommendations(id) {
    return request(`/anime/${id}/recommendations`);
}

export function getTopAnimes(params = {}) {
    return request("/top/anime", params);
}

export function getCurrentSeason(params = {}) {
    return request("/seasons/now", params);
}

export function searchMangas(query, params = {}) {
    return request("/manga", {
        q: query,
        ...params,
    });
}

export function getManga(id) {
    return request(`/manga/${id}`);
}

export const jikan = {
    request,
    searchAnimes,
    getAnime,
    getAnimeCharacters,
    getAnimeRecommendations,
    getTopAnimes,
    getCurrentSeason,
    searchMangas,
    getManga,
};

export const searchAnime = searchAnimes;
export const getAnimeById = getAnime;
export const getTopAnime = getTopAnimes;
export const searchManga = searchMangas;
export const getMangaById = getManga;
