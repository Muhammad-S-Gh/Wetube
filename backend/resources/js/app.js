import "./bootstrap";

const ACTIVE_UPLOAD_KEY = "wetube.activeUpload";
const ACTIVE_UPLOAD_TTL = 24 * 60 * 60 * 1000;

let uploadPoller = null;
let toastHideTimer = null;

function readActiveUpload() {
    const rawState = localStorage.getItem(ACTIVE_UPLOAD_KEY);

    if (!rawState) {
        return null;
    }

    try {
        const state = JSON.parse(rawState);

        if (!state?.videoId || !state?.startedAt) {
            localStorage.removeItem(ACTIVE_UPLOAD_KEY);
            return null;
        }

        if (Date.now() - state.startedAt > ACTIVE_UPLOAD_TTL) {
            localStorage.removeItem(ACTIVE_UPLOAD_KEY);
            return null;
        }

        return state;
    } catch {
        localStorage.removeItem(ACTIVE_UPLOAD_KEY);
        return null;
    }
}

function clearActiveUpload() {
    localStorage.removeItem(ACTIVE_UPLOAD_KEY);

    if (uploadPoller) {
        clearInterval(uploadPoller);
        uploadPoller = null;
    }
}

function ensureToast() {
    let toast = document.getElementById("global-upload-toast");

    if (toast) {
        return toast;
    }

    toast = document.createElement("div");
    toast.id = "global-upload-toast";
    toast.className =
        "fixed bottom-5 left-1/2 z-50 hidden max-w-md -translate-x-1/2 rounded-xl px-4 py-3 text-sm font-medium text-white shadow-2xl";
    toast.setAttribute("role", "status");
    toast.setAttribute("aria-live", "polite");
    document.body.appendChild(toast);

    return toast;
}

function showToast(message, color = "#111827") {
    const toast = ensureToast();
    toast.textContent = message;
    toast.style.backgroundColor = color;
    toast.classList.remove("hidden");

    if (toastHideTimer) {
        clearTimeout(toastHideTimer);
    }

    toastHideTimer = setTimeout(() => {
        toast.classList.add("hidden");
    }, 4500);
}

async function pollUpload(videoId, messages = {}) {
    if (uploadPoller) {
        clearInterval(uploadPoller);
        uploadPoller = null;
    }

    const tick = async () => {
        try {
            const response = await fetch(`/videos/${videoId}/status`, {
                headers: {
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                clearActiveUpload();
                return;
            }

            const contentType = response.headers.get("content-type") ?? "";
            if (!contentType.includes("application/json")) {
                clearActiveUpload();
                return;
            }

            if (response.status === 404) {
                clearActiveUpload();
                return;
            }

            const data = await response.json();

            if (data.processed === "completed") {
                clearActiveUpload();
                showToast(messages.success ?? "Upload completed", "#16a34a");
                return;
            }

            if (data.processed === "failed") {
                clearActiveUpload();
            }
        } catch (error) {
            console.error("Upload polling error:", error);
        }
    };

    await tick();
    uploadPoller = setInterval(tick, 3000);
}

function startUploadWatcher() {
    const activeUpload = readActiveUpload();

    if (!activeUpload) {
        return;
    }

    pollUpload(activeUpload.videoId, activeUpload.messages);
}

window.WeTubeUpload = {
    register(state) {
        localStorage.setItem(
            ACTIVE_UPLOAD_KEY,
            JSON.stringify({
                ...state,
                startedAt: state.startedAt ?? Date.now(),
            }),
        );

        if (document.readyState !== "loading") {
            startUploadWatcher();
        }
    },
    clear: clearActiveUpload,
    toast: showToast,
};

document.addEventListener("DOMContentLoaded", startUploadWatcher);
