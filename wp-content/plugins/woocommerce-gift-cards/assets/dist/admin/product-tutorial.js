/******/ (() => { // webpackBootstrap
/******/ 	"use strict";

;// external ["wp","hooks"]
const external_wp_hooks_namespaceObject = window["wp"]["hooks"];
;// external ["wp","i18n"]
const external_wp_i18n_namespaceObject = window["wp"]["i18n"];
;// ./resources/js/admin/product-tutorial/index.js
/**
 * External dependencies
 */


(0,external_wp_hooks_namespaceObject.addFilter)('experimental_woocommerce_admin_product_tour_steps', 'woocommerce-gift-cards', (tourSteps, tourType) => {
  if ('gift-card' !== tourType) {
    return tourSteps;
  }
  const steps = [{
    referenceElements: {
      desktop: 'label[for=_gift_card]'
    },
    focusElement: {
      desktop: '#_gift_card'
    },
    meta: {
      name: 'gift-card-checkbox',
      heading: (0,external_wp_i18n_namespaceObject.__)('Create a gift card product', 'woocommerce-gift-cards'),
      descriptions: {
        desktop: (0,external_wp_i18n_namespaceObject.__)('Start by checking the Gift Card option. You can use both Simple and Variable products to create gift cards.', 'woocommerce-gift-cards')
      }
    }
  }, {
    referenceElements: {
      desktop: '._regular_price_field'
    },
    focusElement: {
      desktop: '#_regular_price'
    },
    meta: {
      name: 'gift-card-price',
      heading: (0,external_wp_i18n_namespaceObject.__)('Assign a value to your gift card', 'woocommerce-gift-cards'),
      descriptions: {
        desktop: (0,external_wp_i18n_namespaceObject.__)("Use the price fields to assign a face value to your gift card. Simple gift cards have a fixed value. To offer multiple value options, create a Variable gift card.", 'woocommerce-gift-cards')
      }
    }
  }, {
    referenceElements: {
      desktop: '#product-type'
    },
    focusElement: {
      desktop: '#product-type'
    },
    meta: {
      name: 'gift-card-type',
      heading: (0,external_wp_i18n_namespaceObject.__)('Let shoppers personalize your gift card', 'woocommerce-gift-cards'),
      descriptions: {
        desktop: (0,external_wp_i18n_namespaceObject.__)('To offer multiple gift card designs for different occasions, create a Variable gift card and assign a unique name and design to each option. Alternatively, you could create a different Simple gift card for every occasion, and group all gift cards in a dedicated category or page.', 'woocommerce-gift-cards')
      }
    }
  }];
  return steps;
});
/******/ })()
;