(function ($) {
  "use strict";

  var LOG_PREFIX = "[OFFI Checkout Tracking]";
  var META_LOG_PREFIX = "[OFFI Meta Tracking]";
  var isSaving = false;
  var checkTimer = null;

  function log(message, data) {
    if (!window.console || typeof window.console.log !== "function") {
      return;
    }

    if (typeof data === "undefined") {
      console.log(LOG_PREFIX + " " + message);
      return;
    }

    console.log(LOG_PREFIX + " " + message, data);
  }

  function metaLog(title, data) {
    if (!window.console || typeof window.console.log !== "function") {
      return;
    }

    var payload = typeof data === "undefined" ? {} : data;
    var label = META_LOG_PREFIX + " " + title;

    if (
      typeof window.console.groupCollapsed === "function" &&
      typeof window.console.groupEnd === "function"
    ) {
      window.console.groupCollapsed(label);
      window.console.log(payload);
      window.console.groupEnd();
      return;
    }

    window.console.log(label, payload);
  }

  function getConfig() {
    if (
      typeof window.offiCheckoutTracking !== "object" ||
      !window.offiCheckoutTracking
    ) {
      return null;
    }
    return window.offiCheckoutTracking;
  }

  function getStep2Status() {
    var step2TabActive = $("#step-2").hasClass("active");
    var panel2Visible = $("#thwmscf-tab-panel-2").is(":visible");

    return {
      step2TabActive: step2TabActive,
      panel2Visible: panel2Visible,
      isActive: step2TabActive || panel2Visible,
    };
  }

  function collectStep1Fields() {
    return {
      billing_first_name: $("#billing_first_name").val() || "",
      billing_last_name: $("#billing_last_name").val() || "",
      billing_company: $("#billing_company").val() || "",
      billing_country: $("#billing_country").val() || "",
      billing_address_1: $("#billing_address_1").val() || "",
      billing_address_2: $("#billing_address_2").val() || "",
      billing_postcode: $("#billing_postcode").val() || "",
      billing_city: $("#billing_city").val() || "",
      billing_state: $("#billing_state").val() || "",
      billing_phone: $("#billing_phone").val() || "",
      billing_email: $("#billing_email").val() || "",
      order_comments: $("#order_comments").val() || "",
    };
  }

  function isValidEmail(email) {
    var value = (email || "").trim();
    if (!value) {
      return false;
    }
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  }

  function isValidPhone(phone) {
    var value = (phone || "").trim();
    if (!value) {
      return false;
    }

    var digits = value.replace(/\D+/g, "");
    return digits.length >= 6 && digits.length <= 15;
  }

  function isValidSpanishPostcode(postcode) {
    var value = (postcode || "").trim();
    if (!value) {
      return false;
    }

    return /^\d{5}$/.test(value);
  }

  function getStep1FieldRow(fieldName) {
    var field = document.getElementById(fieldName);
    if (!field) {
      return null;
    }

    return field.closest(".form-row") || field.closest("p") || null;
  }

  function clearStep1FieldError(fieldName) {
    var field = document.getElementById(fieldName);
    var row = getStep1FieldRow(fieldName);

    if (field) {
      field.setAttribute("aria-invalid", "false");
      field.removeAttribute("aria-errormessage");
    }

    if (row) {
      row.classList.remove("has-error", "woocommerce-invalid");

      var inlineError = row.querySelector(".offi-step1-field-error");
      if (inlineError) {
        inlineError.remove();
      }
    }
  }

  function clearStep1FieldErrors() {
    [
      "billing_first_name",
      "billing_last_name",
      "billing_country",
      "billing_address_1",
      "billing_postcode",
      "billing_city",
      "billing_state",
      "billing_phone",
      "billing_email",
    ].forEach(function (fieldName) {
      clearStep1FieldError(fieldName);
    });
  }

  function getStep1FieldLabel(fieldName) {
    var labels = {
      billing_first_name: "Nombre",
      billing_last_name: "Apellidos",
      billing_country: "País / Región",
      billing_address_1: "Dirección de la calle",
      billing_postcode: "Código postal / ZIP",
      billing_city: "Ciudad",
      billing_state: "Provincia",
      billing_phone: "Teléfono",
      billing_email: "Correo electrónico",
    };

    return labels[fieldName] || fieldName;
  }

  function getStep1FieldErrorMessage(fieldName) {
    if (fieldName === "billing_postcode") {
      return "El código postal no es válido para España.";
    }

    if (fieldName === "billing_email") {
      return "Introduce un correo electrónico válido.";
    }

    if (fieldName === "billing_phone") {
      return "Introduce un teléfono válido.";
    }

    return getStep1FieldLabel(fieldName) + " es obligatorio.";
  }

  function applyStep1FieldError(fieldName) {
    var field = document.getElementById(fieldName);
    var row = getStep1FieldRow(fieldName);

    if (!field || !row) {
      return;
    }

    clearStep1FieldError(fieldName);

    row.classList.add("has-error", "woocommerce-invalid");
    field.setAttribute("aria-invalid", "true");

    var errorId = fieldName + "_offi_step1_error";
    field.setAttribute("aria-errormessage", errorId);

    var error = document.createElement("div");
    error.id = errorId;
    error.className = "checkout-inline-error-message offi-step1-field-error";
    error.textContent = getStep1FieldErrorMessage(fieldName);

    row.appendChild(error);
  }

  function applyStep1ValidationErrors(validation) {
    clearStep1FieldErrors();

    validation.errors.forEach(function (fieldName) {
      applyStep1FieldError(fieldName);
    });
  }

  function validateStep1Fields(fields) {
    var required = [
      "billing_first_name",
      "billing_last_name",
      "billing_country",
      "billing_address_1",
      "billing_postcode",
      "billing_city",
      "billing_state",
      "billing_phone",
      "billing_email",
    ];

    var errors = [];

    required.forEach(function (key) {
      if (!String(fields[key] || "").trim()) {
        errors.push(key);
      }
    });

    if (String(fields.billing_email || "").trim() && !isValidEmail(fields.billing_email)) {
      errors.push("billing_email");
    }

    if (String(fields.billing_phone || "").trim() && !isValidPhone(fields.billing_phone)) {
      errors.push("billing_phone");
    }

    if (
      String(fields.billing_country || "").trim().toUpperCase() === "ES" &&
      String(fields.billing_postcode || "").trim() &&
      !isValidSpanishPostcode(fields.billing_postcode)
    ) {
      errors.push("billing_postcode");
    }

    return {
      isValid: errors.length === 0,
      errors: errors,
    };
  }

  function isStep1Active() {
    var step1TabActive = $("#step-1").hasClass("active");
    var panel1Visible = $("#thwmscf-tab-panel-1").is(":visible");
    return step1TabActive || panel1Visible;
  }

  function clearStep1ValidationNotice() {
    $("#thwmscf_wrapper .offi-step1-validation-notice").remove();
  }

  function scrollToStepPanels() {
    var $target = $("#thwmscf-tab-panels");
    if (!$target.length) {
      $target = $("#thwmscf_wrapper");
    }

    if (!$target.length) {
      return;
    }

    $("html, body")
      .stop(true)
      .animate(
        {
          scrollTop: Math.max($target.offset().top - 100, 0),
        },
        400,
      );
  }

  function showStep1ValidationNotice(validation) {
    var hasPhoneError = validation.errors.indexOf("billing_phone") !== -1;
    var hasEmailError = validation.errors.indexOf("billing_email") !== -1;
    var message =
      "Revisa los campos obligatorios del paso 1 antes de continuar.";

    if (hasPhoneError) {
      message = "Por favor, introduce un telefono valido para continuar.";
    } else if (hasEmailError) {
      message =
        "Por favor, introduce un correo electronico valido para continuar.";
    }

    clearStep1ValidationNotice();

    var html =
      '<div class="woocommerce-NoticeGroup woocommerce-NoticeGroup-checkout offi-step1-validation-notice">' +
      '<ul class="woocommerce-error" role="alert"><li>' +
      message +
      "</li></ul>" +
      "</div>";

    $("#thwmscf-tab-panel-1").prepend(html);
    scrollToStepPanels();
  }

  function maybeSendMetaEvent(payload, config) {
    var eventName =
      payload && payload.event_name ? payload.event_name : config.eventName;
    var leadId =
      payload && payload.lead_id ? String(payload.lead_id) : "unknown";
    var storageKey = "offi_checkout_step1_meta_sent_" + leadId;

    if (!eventName) {
      eventName = "CheckoutStep1Completed";
    }

    var metaPayload = {
      content_name: payload && payload.content_name ? payload.content_name : "",
      content_ids: payload && payload.content_ids ? payload.content_ids : [],
      contents: payload && payload.contents ? payload.contents : [],
      content_type:
        payload && payload.content_type ? payload.content_type : "product",
      num_items:
        payload && typeof payload.num_items !== "undefined"
          ? payload.num_items
          : 0,
      value:
        payload && typeof payload.value !== "undefined" ? payload.value : 0,
      currency: payload && payload.currency ? payload.currency : "",
    };

    if (typeof window.fbq !== "function") {
      log("Meta event preview", metaPayload);
      return;
    }

    if (window.sessionStorage && sessionStorage.getItem(storageKey) === "1") {
      metaLog("CheckoutStep1Completed no repetido", {
        event_sent: false,
        reason: "duplicate_prevented",
        lead_id: leadId,
      });

      log("Meta event already sent", {
        eventName: eventName,
        storageKey: storageKey,
        leadId: leadId,
      });
      return;
    }

    window.fbq("trackCustom", eventName, metaPayload);

    if (window.sessionStorage) {
      sessionStorage.setItem(storageKey, "1");
    }

    metaLog("CheckoutStep1Completed enviado", {
      event_name: eventName,
      event_type: "trackCustom",
      lead_id: leadId,
      content_name: metaPayload.content_name,
      content_ids: metaPayload.content_ids,
      contents: metaPayload.contents,
      content_type: metaPayload.content_type,
      num_items: metaPayload.num_items,
      value: metaPayload.value,
      currency: metaPayload.currency,
    });

    log("Meta event sent", {
      eventName: eventName,
      storageKey: storageKey,
      leadId: leadId,
      payload: metaPayload,
    });
  }

  function scheduleStep2Check(reason, delayMs) {
    if (checkTimer) {
      window.clearTimeout(checkTimer);
    }

    checkTimer = window.setTimeout(function () {
      saveStep1WhenStep2Reached(reason);
    }, delayMs);
  }

  function saveStep1WhenStep2Reached(reason) {
    var config = getConfig();
    var step2Status = getStep2Status();

    log("checking step 2", {
      reason: reason || "unknown",
      step2TabActive: step2Status.step2TabActive,
      panel2Visible: step2Status.panel2Visible,
      ajaxUrlExists: !!(config && config.ajaxUrl),
      nonceExists: !!(config && config.nonce),
    });

    if (!step2Status.isActive) {
      return;
    }

    log("step 2 active", step2Status);

    if (!config || !config.ajaxUrl || !config.nonce) {
      return;
    }

    if (isSaving) {
      log("AJAX in progress, skipping duplicate request");
      return;
    }

    var data = collectStep1Fields();
    var validation = validateStep1Fields(data);

    if (!validation.isValid) {
      log("step 1 validation blocked AJAX", {
        reason: reason || "unknown",
        errors: validation.errors,
      });
      return;
    }

    data.action = "offi_save_checkout_step";
    data.nonce = config.nonce;

    metaLog("Guardando paso 1", {
      action: "offi_save_checkout_step",
      target_step: 2,
    });

    log("sending AJAX", {
      action: "offi_save_checkout_step",
      targetStep: 2,
      ajaxUrlExists: !!config.ajaxUrl,
      hasNonce: !!config.nonce,
    });

    isSaving = true;

    $.ajax({
      url: config.ajaxUrl,
      method: "POST",
      dataType: "json",
      data: data,
    })
      .done(function (response) {
        log("AJAX success", response);

        if (!response || !response.success || !response.data) {
          metaLog("Error al guardar paso 1", {
            ajax_success: false,
            status: 200,
            reason:
              response && response.data && response.data.message
                ? response.data.message
                : "invalid_ajax_response",
          });
          return;
        }

        metaLog("Paso 1 guardado correctamente", {
          ajax_success: true,
          target_step: 2,
          lead_id:
            response.data && typeof response.data.lead_id !== "undefined"
              ? String(response.data.lead_id)
              : "unknown",
        });

        maybeSendMetaEvent(response.data, config);
      })
      .fail(function (xhr) {
        metaLog("Error al guardar paso 1", {
          ajax_success: false,
          status: xhr && xhr.status ? xhr.status : 0,
          reason:
            xhr && xhr.statusText ? String(xhr.statusText) : "ajax_failed",
        });

        log("AJAX error", {
          status: xhr && xhr.status,
          statusText: xhr && xhr.statusText,
        });
      })
      .always(function () {
        isSaving = false;
      });
  }

  function setupStep2Observers() {
    var step2Node = document.querySelector("#step-2");
    var panel2Node = document.querySelector("#thwmscf-tab-panel-2");

    if (!step2Node && !panel2Node) {
      log("observer setup skipped - step 2 nodes not found");
      return;
    }

    var observer = new MutationObserver(function (mutations) {
      log("step 2 mutation detected", {
        mutationCount: mutations.length,
      });
      scheduleStep2Check("mutation", 120);
    });

    if (step2Node) {
      observer.observe(step2Node, {
        attributes: true,
        attributeFilter: ["class", "style", "hidden", "aria-hidden"],
      });
    }

    if (panel2Node) {
      observer.observe(panel2Node, {
        attributes: true,
        attributeFilter: ["class", "style", "hidden", "aria-hidden"],
      });
    }

    log("mutation observers ready", {
      step2NodeFound: !!step2Node,
      panel2NodeFound: !!panel2Node,
    });
  }

  $(document).on("click", ".button-next.action-next", function () {
    log("next clicked - jQuery");
    scheduleStep2Check("jquery-click", 650);
  });

  document.addEventListener(
    "click",
    function (event) {
      var button =
        event.target && event.target.closest
          ? event.target.closest(".button-next.action-next")
          : null;
      if (!button) {
        return;
      }

      if (isStep1Active()) {
        var fields = collectStep1Fields();
        var validation = validateStep1Fields(fields);

        if (!validation.isValid) {
          event.preventDefault();
          event.stopImmediatePropagation();
          applyStep1ValidationErrors(validation);
          showStep1ValidationNotice(validation);
          metaLog("Paso 1 bloqueado", {
            event_sent: false,
            reason: "validation_failed",
            invalid_fields: validation.errors,
          });

          log("step 1 validation blocked navigation", {
            errors: validation.errors,
          });
          return;
        }

        clearStep1ValidationNotice();
      }

      applyStep1ValidationErrors(validateStep1Fields(collectStep1Fields()));

      log("next clicked - capture");
      scheduleStep2Check("capture-click", 650);
    },
    true,
  );

  $(document).on(
    "input change",
    "#thwmscf-tab-panel-1 input, #thwmscf-tab-panel-1 select, #thwmscf-tab-panel-1 textarea",
    function () {
      applyStep1ValidationErrors(validateStep1Fields(collectStep1Fields()));
    },
  );

  log("script loaded");

  $(function () {
    var config = getConfig();

    log("config", {
      exists: !!config,
      ajaxUrlExists: !!(config && config.ajaxUrl),
      nonceExists: !!(config && config.nonce),
      eventName:
        config && config.eventName
          ? config.eventName
          : "CheckoutStep1Completed",
    });

    setupStep2Observers();
    scheduleStep2Check("init", 250);
  });
})(jQuery);
