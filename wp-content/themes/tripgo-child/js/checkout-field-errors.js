(function () {  
  function normalizeText(text) {
    return (text || "")
      .toLowerCase()
      .normalize("NFD")
      .replace(/[\u0300-\u036f]/g, "");
  }

  function createValidationMessage(input, message) {
    const errorId = "validate-error-" + input.name;

    input.setAttribute("aria-invalid", "true");
    input.setAttribute("aria-errormessage", errorId);

    const error = document.createElement("div");
    error.className = "wc-block-components-validation-error";
    error.setAttribute("role", "alert");
    error.setAttribute("data-offitravel-forced-error", "true");

    error.innerHTML =
      '<p id="' + errorId + '">' + "<span>" + message + "</span>" + "</p>";

    return error;
  }

  function clearForcedErrors() {
    document
      .querySelectorAll("[data-offitravel-forced-error]")
      .forEach(function (error) {
        error.remove();
      });

    document
      .querySelectorAll("[data-offitravel-forced-invalid]")
      .forEach(function (input) {
        input.setAttribute("aria-invalid", "false");
        input.removeAttribute("aria-errormessage");
        input.removeAttribute("data-offitravel-forced-invalid");

        const wrapper = input.closest(".wc-block-components-text-input");
        if (wrapper) {
          wrapper.classList.remove("has-error");
        }
      });
  }

  function markFieldAsError(selector, message) {
    const input = document.querySelector(selector);
    if (!input) return;

    const wrapper = input.closest(".wc-block-components-text-input");
    if (!wrapper) return;

    wrapper.classList.add("has-error");
    input.setAttribute("data-offitravel-forced-invalid", "true");

    const oldError = wrapper.querySelector("[data-offitravel-forced-error]");
    if (oldError) {
      oldError.remove();
    }

    wrapper.appendChild(createValidationMessage(input, message));
  }

  function applyCheckoutErrors() {
    const notices = document.querySelectorAll(
      ".wc-block-components-notice-banner.is-error, .wc-block-store-notice.is-error",
    );

    clearForcedErrors();

    notices.forEach(function (notice) {
      const message = notice.textContent.trim();
      const normalized = normalizeText(message);

      if (
        normalized.includes("telefono") ||
        normalized.includes("numero de telefono")
      ) {
        markFieldAsError(
          "#billing-phone",
          "Por favor, introduce un teléfono válido",
        );
      }

      if (
        normalized.includes("correo electronico") ||
        normalized.includes("email")
      ) {
        markFieldAsError(
          "#email",
          "Por favor, introduce un correo electrónico válido",
        );
      }

      if (normalized.includes("codigo postal")) {
        markFieldAsError("#billing-postcode", message);
      }
    });
  }

  function initCheckoutErrorObserver() {
    const billingNotices = document.querySelector(
      "#billing-fields .wc-block-components-notices",
    );

    if (!billingNotices) return;

    const observer = new MutationObserver(function () {
      setTimeout(applyCheckoutErrors, 50);
    });

    observer.observe(billingNotices, {
      childList: true,
      subtree: true,
    });
  }

  document.addEventListener("click", function (event) {
    const button = event.target.closest(
      ".wc-block-components-checkout-place-order-button",
    );

    if (!button) return;

    setTimeout(applyCheckoutErrors, 300);
    setTimeout(applyCheckoutErrors, 800);
    setTimeout(applyCheckoutErrors, 1300);
  });

  document.addEventListener("input", function (event) {
    const input = event.target;

    if (!input.matches("[data-offitravel-forced-invalid]")) return;

    input.setAttribute("aria-invalid", "false");
    input.removeAttribute("aria-errormessage");
    input.removeAttribute("data-offitravel-forced-invalid");

    const wrapper = input.closest(".wc-block-components-text-input");
    if (!wrapper) return;

    wrapper.classList.remove("has-error");

    const error = wrapper.querySelector("[data-offitravel-forced-error]");
    if (error) {
      error.remove();
    }
  });

  document.addEventListener("DOMContentLoaded", function () {
    initCheckoutErrorObserver();
    setTimeout(applyCheckoutErrors, 500);
  });
})();
