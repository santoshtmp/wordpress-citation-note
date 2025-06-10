jQuery(document).ready(function ($) {
});

document.addEventListener("DOMContentLoaded", () => {
    const popup = document.createElement("div");
    popup.className = "yipl-citation-popup";
    document.body.appendChild(popup);

    let currentRef = null;
    let hideTimeout;

    function showPopup(ref, content) {
        clearTimeout(hideTimeout);
        popup.innerHTML = content;
        popup.classList.add("show");
        popup.classList.remove("hide");

        const rect = ref.getBoundingClientRect();
        popup.style.top = window.scrollY + rect.bottom + 6 + "px";
        popup.style.left = window.scrollX + rect.left + "px";
    }

    function hidePopupDelayed() {
        hideTimeout = setTimeout(() => {
            popup.classList.remove("show");
            popup.classList.add("hide");
        }, 250);
    }

    const references = document.querySelectorAll("sup.reference");
    references.forEach(ref => {
        const href = ref.querySelector("a")?.getAttribute("href");
        const citationId = href?.replace("#", "");
        const citationEl = citationId && document.getElementById(citationId);

        if (!citationEl) return;

        const citationContent = citationEl.querySelector(".yipl-citation-description")?.innerHTML;
        if (!citationContent) return;

        ref.addEventListener("mouseenter", () => {
            currentRef = ref;
            showPopup(ref, citationContent);
        });

        ref.addEventListener("mouseleave", () => {
            hidePopupDelayed();
        });

    });

    popup.addEventListener("mouseenter", () => {
        clearTimeout(hideTimeout);
    });

    popup.addEventListener("mouseleave", () => {
        hidePopupDelayed();
    });
});