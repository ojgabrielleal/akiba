import { router } from "@inertiajs/svelte"

const defaultInterval = 30 * 1000

export function startPublicSiteRefreshWatcher(getVersion, interval = defaultInterval) {
    if (typeof window === "undefined") return () => {}

    let currentVersion = getVersion()
    let checking = false
    let stopped = false

    const refreshCurrentPage = () => {
        router.visit(window.location.href, {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        })
    }

    const checkVersion = () => {
        if (checking || stopped || document.visibilityState !== "visible") return

        checking = true

        router.reload({
            only: ["publicSiteVersion"],
            preserveScroll: true,
            preserveState: true,
            onSuccess: (page) => {
                const nextVersion = page.props.publicSiteVersion ?? getVersion()
                if (! currentVersion) {
                    currentVersion = nextVersion
                    return
                }

                if (nextVersion && nextVersion !== currentVersion) {
                    currentVersion = nextVersion
                    refreshCurrentPage()
                }
            },
            onFinish: () => {
                checking = false
            },
        })
    }

    const intervalId = window.setInterval(checkVersion, interval)

    const handleVisibilityChange = () => {
        if (document.visibilityState === "visible") {
            checkVersion()
        }
    }

    document.addEventListener("visibilitychange", handleVisibilityChange)

    return () => {
        stopped = true
        window.clearInterval(intervalId)
        document.removeEventListener("visibilitychange", handleVisibilityChange)
    }
}
