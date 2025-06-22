// jQuery(document).ready(function ($) {});
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
    // Position the popup relative to the reference element
    const rect = ref.getBoundingClientRect();
    const popupWidth = popup.offsetWidth;
    const popupHeight = popup.offsetHeight;
    // Calculate position to avoid overflow
    let top = window.scrollY + rect.bottom + 6;
    let left = window.scrollX + rect.left - 14;
    // Prevent right overflow
    const rightEdge = left + popupWidth;
    if (rightEdge > window.innerWidth) {
      left = window.innerWidth - popupWidth - 16; // 16px padding
    }
    // Prevent bottom overflow
    const bottomEdge = top + popupHeight;
    if (bottomEdge > window.scrollY + window.innerHeight) {
      top = window.scrollY + rect.top - popupHeight - 6; // show above the reference
    }
    popup.style.top = top + "px";
    popup.style.left = left + "px";

    // Arrow position relative to the popup
    const refCenter = window.scrollX + rect.left + rect.width / 2;
    const arrowLeft = refCenter - left;
    popup.style.setProperty("--arrow-left", `${arrowLeft}px`);

  }
  function hidePopupDelayed() {
    hideTimeout = setTimeout(() => {
      popup.classList.remove("show");
      popup.classList.add("hide");
    }, 250);
  }
  const references = document.querySelectorAll("sup.reference");
  references.forEach((ref) => {
    const href = ref.querySelector("a")?.getAttribute("href");
    const citationId = href?.replace("#", "");
    const citationEl = citationId && document.getElementById(citationId);
    if (!citationEl) return;
    const citationContent = citationEl.querySelector(
      ".yipl-citation-description"
    )?.innerHTML;
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

// document.addEventListener("DOMContentLoaded", () => {
//   const references = document.querySelectorAll(
//     ".reference.reference-number > a"
//   );

//   function updateArrowPositions() {
//     references.forEach((ref) => {
//       const rect = ref.getBoundingClientRect();
//       const top = window.scrollY + rect.top;
//       const left = window.scrollX + rect.left;

//       // Store values as CSS custom properties
//       ref.style.setProperty("--arrow-top", `${top}px`);
//       ref.style.setProperty("--arrow-left", `${left}px`);

//       console.log("position", left);
//     });
//   }

//   // Run on load and on scroll/resize
//   updateArrowPositions();
//   window.addEventListener("scroll", updateArrowPositions);
//   window.addEventListener("resize", updateArrowPositions);
// });
