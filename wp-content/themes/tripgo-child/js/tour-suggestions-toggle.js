/**
 * Tour list: toggle "Product related" vs alternate destination+month suggestions
 * when Tour Search Ajax finishes.
 */
(function ($) {
	'use strict';

	/**
	 * @param {JQuery} $any Container that may contain .tour_number_results_found (e.g. .brw-search-ajax-result).
	 * @return {null|number} null = counter not in DOM yet.
	 */
	function getMainSearchCount($any) {
		if (!$any || !$any.length) {
			return null;
		}
		var $h = $any.find('.tour_number_results_found').first();
		if (!$h.length) {
			return null;
		}
		var v = $h.val();
		return parseInt(v, 10) || 0;
	}

	function findSearchWraps() {
		return $('.ovabrw-search-ajax .wrap-search-ajax');
	}

	/**
	 * Swiper for .offitravel-tour-suggestions-fallback (mirrors ova product-related.js).
	 */
	function initOffitravelSuggestionSliders() {
		if (typeof Swiper !== 'function') {
			return;
		}
		$(
			'.offitravel-tour-suggestions-fallback .ova-product-slider.elementor-ralated'
		).each(function () {
			var that = $(this);
			if (that.data('offitravelSwiperInited')) {
				return;
			}
			var sliderEl = that.find('.swiper')[0];
			if (!sliderEl) {
				return;
			}
			var opts = that.data('options');
			if (!opts) {
				return;
			}
			that.data('offitravelSwiperInited', 1);
			var swiperWrapper = $(sliderEl).find('.swiper-wrapper');
			var slides = swiperWrapper.find('.swiper-slide');
			var sliderData = {
				loop: opts.loop,
				loopAddBlankSlides: false,
				speed: opts.speed || 500,
				slidesPerGroup: opts.slidesPerGroup,
				slidesPerView: opts.slidesPerView,
				spaceBetween: opts.spaceBetween,
				rtl: opts.rtl,
				breakpoints: opts.breakpoints,
				on: {
					beforeInit: function (swiper) {
						if (opts.loop) {
							var numberOfItems = slides.length;
							var flag =
								slides.length > 1 && numberOfItems === swiper.params.slidesPerView
									? 1
									: 0;
							while (numberOfItems <= swiper.params.slidesPerView) {
								if (flag === slides.length) {
									flag = 0;
								}
								swiperWrapper.append(slides[flag].cloneNode(true));
								numberOfItems = sliderEl.querySelectorAll('.swiper-slide')
									.length;
								flag++;
							}
						}
					},
					init: function () {
						$(sliderEl).removeClass('swiper-loading');
					},
				},
			};
			if (opts.autoplay) {
				sliderData.autoplay = {
					delay: opts.delay || 3000,
					disableOnInteraction: false,
					pauseOnMouseEnter: opts.pauseOnMouseEnter,
				};
			}
			if (opts.nav) {
				sliderData.navigation = {
					nextEl: that.find('.button-next')[0],
					prevEl: that.find('.button-prev')[0],
				};
			}
			if (opts.dots) {
				sliderData.pagination = {
					el: that.find('.button-dots')[0],
					clickable: true,
					dynamicBullets: true,
					dynamicMainBullets: 3,
				};
			}
			new Swiper(sliderEl, sliderData);
		});
	}

	function hasSuggestionSlides() {
		return $(
			'.offitravel-tour-suggestions-fallback .ova-product-slider .swiper-slide'
		).length > 0;
	}

	/**
	 * @param {number} n Found posts from ajax.
	 */
	function applyVisibility(n) {
		var $s = $('.offitravel-tour-suggestions-fallback');
		var $r = $('.elementor-widget-ovabrw_product_related').not(
			'[data-offitravel-skip]'
		);
		if (!$s.length) {
			$r.show();
			return;
		}
		if (n > 0) {
			$s.hide();
			$r.show();
			return;
		}
		if (n === 0 && hasSuggestionSlides()) {
			$r.hide();
			$s.show();
			initOffitravelSuggestionSliders();
		} else {
			$s.hide();
			$r.show();
		}
	}

	function onResultsUpdated($wrap) {
		if (!$wrap || !$wrap.length) {
			return;
		}
		var $r = $wrap.find('.brw-search-ajax-result').first();
		if (!$r.length) {
			$r = $wrap;
		}
		var c = getMainSearchCount($r);
		if (c === null) {
			return;
		}
		applyVisibility(c);
	}

	$(function () {
		var $result = $('.ovabrw-search-ajax .brw-search-ajax-result').first();
		var c0 = getMainSearchCount($result);
		if (c0 === null) {
			c0 = parseInt(
				$('.offitravel-tour-suggestions-fallback').attr('data-main-count') || '0',
				10
			);
			if (isNaN(c0)) {
				c0 = 0;
			}
		}
		applyVisibility(c0);
	});

	$(document).ajaxComplete(function (event, xhr, settings) {
		var s = settings && settings.data;
		if (s === undefined || s === null) {
			return;
		}
		if (String(s).indexOf('ovabrw_search_ajax') === -1) {
			return;
		}
		var $wrap = findSearchWraps().first();
		setTimeout(function () {
			onResultsUpdated($wrap);
		}, 10);
	});

	// After AJAX result HTML is replaced (backup if ajaxComplete order differs).
	if (window.MutationObserver) {
		var t;
		$(function () {
			$('.brw-search-ajax-result').each(function () {
				var el = this;
				var obs = new MutationObserver(function () {
					clearTimeout(t);
					t = setTimeout(function () {
						var $w = $(el).closest('.wrap-search-ajax');
						onResultsUpdated($w);
					}, 100);
				});
				obs.observe(el, { childList: true, subtree: true });
			});
		});
	}
})(jQuery);
