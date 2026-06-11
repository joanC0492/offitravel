"use strict";

// reCAPTCHA functions
var ovabrwLoadingReCAPTCHAv2 = function() {
    if ( document.getElementById('ovabrw-g-recaptcha-booking') ) {
        grecaptcha.render(document.getElementById('ovabrw-g-recaptcha-booking'), {
            'sitekey' : ovabrw_recaptcha.site_key,
            'callback': ovabrwBookingFormVerify,
            'expired-callback': ovabrwBookingFormExpired,
            'error-callback': ovabrwBookingFormError
        });
    }

    if ( document.getElementById('ovabrw-g-recaptcha-enquiry') ) {
        grecaptcha.render(document.getElementById('ovabrw-g-recaptcha-enquiry'), {
            'sitekey' : ovabrw_recaptcha.site_key,
            'callback': ovabrwEnquiryFormVerify,
            'expired-callback': ovabrwEnquiryFormExpired,
            'error-callback': ovabrwEnquiryFormError
        });
    }
};

var ovabrwLoadingReCAPTCHAv3 = function() {
    if ( document.getElementById('ovabrw-g-recaptcha-booking') ) {
        grecaptcha.ready(function() {
            grecaptcha.execute(ovabrw_recaptcha.site_key, {action: 'booking'}).then(function(token) {
                var tokenInput = document.getElementById("ovabrw-recaptcha-booking-token");

                if ( tokenInput && token ) {
                    tokenInput.setAttribute('value', token);
                    tokenInput.setAttribute('data-error', '');
                }
            });
        });
    }

    if ( document.getElementById('ovabrw-g-recaptcha-enquiry') ) {
        grecaptcha.ready(function() {
            grecaptcha.execute(ovabrw_recaptcha.site_key, {action: 'request'}).then(function(token) {
                var tokenInput = document.getElementById("ovabrw-recaptcha-enquiry-token");

                if ( tokenInput && token ) {
                    tokenInput.setAttribute('value', token);
                    tokenInput.setAttribute('data-error', '');
                }
            });
        });
    }
};

// Booking Form Verify
var ovabrwBookingFormVerify = function( response ) {
    var tokenInput = document.getElementById("ovabrw-recaptcha-booking-token");

    if ( tokenInput && response ) {
        tokenInput.setAttribute('value', response);
        tokenInput.setAttribute('data-error', '');
    }
}

var ovabrwBookingFormExpired = function() {
    var tokenInput = document.getElementById("ovabrw-recaptcha-booking-token");

    if ( tokenInput ) {
        var expiresError = tokenInput.getAttribute('data-expired');

        tokenInput.setAttribute('value', '');
        tokenInput.setAttribute('data-error', expiresError);
    }
}

var ovabrwBookingFormError = function() {
    var tokenInput = document.getElementById("ovabrw-recaptcha-booking-token");

    if ( tokenInput ) {
        var error = tokenInput.getAttribute('data-error');

        tokenInput.setAttribute('value', '');
        tokenInput.setAttribute('data-error', error);
    }
}

// Enquiry Form Verify
var ovabrwEnquiryFormVerify = function( response ) {
    var tokenInput = document.getElementById("ovabrw-recaptcha-enquiry-token");

    if ( tokenInput && response ) {
        tokenInput.setAttribute('value', response);
        tokenInput.setAttribute('data-error', '');
    }
}

var ovabrwEnquiryFormExpired = function() {
    var tokenInput = document.getElementById("ovabrw-recaptcha-enquiry-token");

    if ( tokenInput ) {
        var expiresError = tokenInput.getAttribute('data-expired');

        tokenInput.setAttribute('value', '');
        tokenInput.setAttribute('data-error', expiresError);
    }
}

var ovabrwEnquiryFormError = function() {
    var tokenInput = document.getElementById("ovabrw-recaptcha-enquiry-token");

    if ( tokenInput ) {
        var error = tokenInput.getAttribute('data-error');

        tokenInput.setAttribute('value', '');
        tokenInput.setAttribute('data-error', error);
    }
}