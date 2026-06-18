/**
 * OFFITRAVEL
 * Botón flotante de reserva para páginas individuales de tours.
 */
(function () {
  "use strict";

  const SELECTORS = {
    container: ".ova-forms-product",
    form: ".ova-forms-product form.booking-form",
    originalButton: ".ova-forms-product .booking-form-submit",
    requiredFields: ".ova-forms-product .ovabrw-input-required",
    ajaxError: ".ova-forms-product .ajax-error",
  };

  const CLASSES = {
    button: "offitravel-floating-booking",
    visible: "is-visible",
    invalid: "offitravel-booking-field-invalid",
  };

  let bookingContainer = null;
  let bookingForm = null;
  let originalButton = null;
  let floatingButton = null;
  let buttonObserver = null;
  let containerObserver = null;
  let containerHasBeenSeen = false;
  let originalButtonIsVisible = true;

  /**
   * Inicializa el botón flotante.
   */
  function init() {
    bookingContainer = document.querySelector(SELECTORS.container);
    bookingForm = document.querySelector(SELECTORS.form);
    originalButton = document.querySelector(SELECTORS.originalButton);

    if (!bookingContainer || !bookingForm || !originalButton) {
      return;
    }

    createFloatingButton();
    observeBookingContainer();
    observeOriginalButton();
    bindEvents();
  }

  /**
   * Crea el botón flotante.
   */
  function createFloatingButton() {
    if (document.querySelector(`.${CLASSES.button}`)) {
      return;
    }

    floatingButton = document.createElement("button");
    floatingButton.type = "button";
    floatingButton.className = CLASSES.button;
    floatingButton.setAttribute("aria-label", "Ir al formulario de reserva");
    floatingButton.setAttribute("aria-hidden", "true");

    floatingButton.innerHTML = `
			<span class="offitravel-floating-booking__icon" aria-hidden="true">
				<i class="icomoon icomoon-calendar"></i>
			</span>
			<span class="offitravel-floating-booking__text">
				Reservar ahora
			</span>
		`;

    document.body.appendChild(floatingButton);
  }

  /**
   * Detecta si el usuario ya llegó a la zona del formulario.
   */
  function observeBookingContainer() {
    containerObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            containerHasBeenSeen = true;
          }

          updateFloatingButtonVisibility();
        });
      },
      {
        threshold: 0.05,
      },
    );

    containerObserver.observe(bookingContainer);
  }

  /**
   * Detecta si el botón original está visible.
   */
  function observeOriginalButton() {
    buttonObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          originalButtonIsVisible = entry.isIntersecting;
          updateFloatingButtonVisibility();
        });
      },
      {
        threshold: 0.15,
      },
    );

    buttonObserver.observe(originalButton);
  }

  /**
   * Decide cuándo mostrar u ocultar el botón.
   */
  // function updateFloatingButtonVisibility() {
  //   if (!floatingButton) {
  //     return;
  //   }

  //   const containerPosition = bookingContainer.getBoundingClientRect();
  //   const userPassedBookingArea = containerPosition.top < 0;

  //   const shouldShow =
  //     containerHasBeenSeen && userPassedBookingArea && !originalButtonIsVisible;

  //   floatingButton.classList.toggle(CLASSES.visible, shouldShow);
  //   floatingButton.setAttribute("aria-hidden", shouldShow ? "false" : "true");
  // }
  function updateFloatingButtonVisibility() {
    if (!floatingButton || !bookingContainer || !originalButton) {
      return;
    }
    const originalButtonPosition = originalButton.getBoundingClientRect();
    /*
     * Mostrar cuando el botón original ya pasó por encima
     * del viewport, con un margen de 40 px.
     */
    const originalButtonPassedViewport = originalButtonPosition.bottom < 40;
    const shouldShow = containerHasBeenSeen && originalButtonPassedViewport;
    floatingButton.classList.toggle(CLASSES.visible, shouldShow);
    floatingButton.setAttribute("aria-hidden", shouldShow ? "false" : "true");
  }

  /**
   * Eventos.
   */
  function bindEvents() {
    floatingButton.addEventListener("click", handleFloatingButtonClick);

    window.addEventListener("scroll", updateFloatingButtonVisibility, {
      passive: true,
    });

    window.addEventListener("resize", updateFloatingButtonVisibility, {
      passive: true,
    });

    bookingForm.addEventListener("input", clearFieldError);
    bookingForm.addEventListener("change", clearFieldError);
  }

  /**
   * Acción al presionar "Reservar ahora".
   */
  function handleFloatingButtonClick() {
    refreshFormReferences();
    clearPreviousErrors();

    const firstInvalidField = findFirstInvalidField();

    if (firstInvalidField) {
      scrollToBookingForm(firstInvalidField);
      return;
    }

    if (originalButton.disabled) {
      scrollToBookingForm(originalButton);
      return;
    }

    /*
     * Ejecutamos el botón original para mantener:
     * - validaciones del plugin;
     * - cálculo de precios;
     * - AJAX;
     * - add-to-cart;
     * - redirecciones.
     */
    originalButton.click();
  }

  /**
   * Actualiza referencias por si OVA modificó el formulario mediante AJAX.
   */
  function refreshFormReferences() {
    bookingContainer =
      document.querySelector(SELECTORS.container) || bookingContainer;

    bookingForm = document.querySelector(SELECTORS.form) || bookingForm;

    originalButton =
      document.querySelector(SELECTORS.originalButton) || originalButton;
  }

  /**
   * Busca el primer campo obligatorio incompleto.
   */
  function findFirstInvalidField() {
    const requiredFields = bookingForm.querySelectorAll(
      ".ovabrw-input-required",
    );

    for (const field of requiredFields) {
      if (!isFieldAvailable(field)) {
        continue;
      }

      if (!isFieldValid(field)) {
        markFieldAsInvalid(field);
        return field;
      }
    }

    return null;
  }

  /**
   * Verifica que el campo pueda validarse.
   */
  function isFieldAvailable(field) {
    if (field.disabled) {
      return false;
    }

    if (field.type === "hidden") {
      /*
       * Algunos inputs hidden de OVA sí representan valores requeridos,
       * como ovabrw_adults. Se validan cuando pertenecen al formulario.
       */
      return field.closest("form") === bookingForm;
    }

    return field.offsetParent !== null;
  }

  /**
   * Validación básica según el tipo del campo.
   */
  function isFieldValid(field) {
    const type = (field.type || "").toLowerCase();
    const value = String(field.value || "").trim();

    if (type === "checkbox" || type === "radio") {
      const name = field.getAttribute("name");

      if (!name) {
        return field.checked;
      }

      const group = bookingForm.querySelectorAll(
        `[name="${escapeSelector(name)}"]`,
      );

      return Array.from(group).some(function (item) {
        return item.checked;
      });
    }

    if (field.tagName === "SELECT") {
      return value !== "";
    }

    if (type === "number") {
      if (value === "") {
        return false;
      }

      const numberValue = Number(value);

      if (Number.isNaN(numberValue)) {
        return false;
      }

      if (field.min !== "" && numberValue < Number(field.min)) {
        return false;
      }
    }

    return value !== "";
  }

  /**
   * Marca visualmente el campo inválido.
   */
  function markFieldAsInvalid(field) {
    const wrapper =
      field.closest(".rental_item") ||
      field.closest(".form-row") ||
      field.parentElement;

    if (wrapper) {
      wrapper.classList.add(CLASSES.invalid);
    }

    field.setAttribute("aria-invalid", "true");
  }

  /**
   * Limpia el error cuando el usuario modifica el campo.
   */
  function clearFieldError(event) {
    const field = event.target;

    if (!(field instanceof HTMLElement)) {
      return;
    }

    field.removeAttribute("aria-invalid");

    const wrapper =
      field.closest(".rental_item") ||
      field.closest(".form-row") ||
      field.parentElement;

    if (wrapper) {
      wrapper.classList.remove(CLASSES.invalid);
    }
  }

  /**
   * Limpia errores anteriores.
   */
  function clearPreviousErrors() {
    bookingForm
      .querySelectorAll(`.${CLASSES.invalid}`)
      .forEach(function (element) {
        element.classList.remove(CLASSES.invalid);
      });

    bookingForm
      .querySelectorAll('[aria-invalid="true"]')
      .forEach(function (element) {
        element.removeAttribute("aria-invalid");
      });
  }

  /**
   * Lleva al usuario al formulario.
   */
  function scrollToBookingForm(invalidField) {
    const adminBar = document.getElementById("wpadminbar");
    const adminBarHeight = adminBar ? adminBar.offsetHeight : 0;
    const headerOffset = 90 + adminBarHeight;

    const top =
      bookingContainer.getBoundingClientRect().top +
      window.scrollY -
      headerOffset;

    window.scrollTo({
      top: Math.max(0, top),
      behavior: "smooth",
    });

    window.setTimeout(function () {
      focusInvalidField(invalidField);
    }, 550);
  }

  /**
   * Enfoca el campo si admite foco.
   */
  function focusInvalidField(field) {
    if (!field || field.type === "hidden") {
      return;
    }

    try {
      field.focus({
        preventScroll: true,
      });
    } catch (error) {
      field.focus();
    }
  }

  /**
   * Escapa nombres usados dentro de querySelector.
   */
  function escapeSelector(value) {
    if (window.CSS && typeof window.CSS.escape === "function") {
      return window.CSS.escape(value);
    }

    return value.replace(/["\\]/g, "\\$&");
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
