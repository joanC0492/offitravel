/**
 * Front page: búsqueda sin destino ni mes → #categoria-viajes y tab "Todos".
 *
 * - Misma página + solo cambio de hash: no hay recarga; se fuerza el tab con timeouts y hashchange.
 * - Si el hash ya era el correcto, no hay scroll nativo; tras Buscar vacío se hace scrollIntoView al bloque.
 * - Si el CSS ID está en la sección padre, se usa #sectionId .ovabrw-category-ajax.
 */
(function ($) {
	'use strict';

	function monthsAreEmpty($form) {
		var $m = $form.find('.ovabrw-monthpicker-start');
		if ($m.length) {
			var mv = $m.val();
			if (mv === null || mv === undefined) {
				return true;
			}
			if (Array.isArray(mv)) {
				return mv.length === 0;
			}
			return String(mv).trim() === '';
		}
		var any = false;
		$form.find('[name="ovabrw_pickup_date[]"], [name="ovabrw_pickup_date"]').each(function () {
			var v = $(this).val();
			if (v === null || v === undefined) {
				return;
			}
			if (Array.isArray(v) && v.length) {
				any = true;
			} else if (String(v).trim() !== '') {
				any = true;
			}
		});
		return !any;
	}

	function destinationIsAll($form) {
		var $d = $form.find('#brw-destinations-select-box');
		if (!$d.length) {
			return false;
		}
		var v = $d.val();
		return v === 'all' || v === '' || v === null;
	}

	function hashMatchesSection() {
		if (typeof offitravelHomeSearch === 'undefined') {
			return false;
		}
		var id = offitravelHomeSearch.sectionId;
		return (window.location.hash || '').replace(/^#/, '') === id;
	}

	/**
	 * Misma URL de documento (origen + pathname + query); solo puede diferir el hash.
	 */
	function isSameDocumentAsHomeBase(baseUrl) {
		try {
			var target = new URL(baseUrl, window.location.href);
			var cur = window.location;
			if (target.origin !== cur.origin) {
				return false;
			}
			var tp = target.pathname.replace(/\/+$/, '') || '/';
			var cp = cur.pathname.replace(/\/+$/, '') || '/';
			return tp === cp && target.search === cur.search;
		} catch (err) {
			return false;
		}
	}

	/**
	 * Bloque OVA dentro del ancla configurada (section puede ser padre del widget).
	 */
	function resolveHomeCategoryAjaxRoot() {
		if (typeof offitravelHomeSearch === 'undefined') {
			return $();
		}
		var $anchor = $('#' + offitravelHomeSearch.sectionId);
		if (!$anchor.length) {
			return $();
		}
		var $ajax = $anchor.find('.ovabrw-category-ajax').first();
		if ($ajax.length) {
			return $ajax;
		}
		if ($anchor.hasClass('ovabrw-category-ajax')) {
			return $anchor;
		}
		return $();
	}

	/** Scroll al bloque con CSS ID de la sección (p. ej. cuando el hash no cambia y no hay scroll del navegador). */
	function scrollToCategorySection() {
		if (typeof offitravelHomeSearch === 'undefined') {
			return;
		}
		var el = document.getElementById(offitravelHomeSearch.sectionId);
		if (!el) {
			return;
		}
		try {
			el.scrollIntoView({ behavior: 'smooth', block: 'start' });
		} catch (err) {
			el.scrollIntoView(true);
		}
	}

	function clickTodosInScope($ajaxRoot) {
		if (!$ajaxRoot || !$ajaxRoot.length) {
			return;
		}
		var $todos = $ajaxRoot.find('.category-item[data-term-id="0"]').first();
		if (!$todos.length) {
			return;
		}
		if ($todos.hasClass('active')) {
			return;
		}
		$todos.trigger('click');
	}

	var SCHEDULE_DELAYS_MS = [0, 10, 50, 100, 200, 350];

	/**
	 * Si el hash es el de la sección de viajes, programa clics en «Todos» (reintentos por timing / AJAX OVA).
	 *
	 * @param {JQuery|null} $widget Fallback cuando resolve falla (p. ej. element_ready).
	 */
	function activateTodosIfAnchored($widget) {
		if (typeof offitravelHomeSearch === 'undefined' || !hashMatchesSection()) {
			return;
		}
		var $ajax = resolveHomeCategoryAjaxRoot();
		if (!$ajax.length && $widget && $widget.length) {
			$ajax = $widget.find('.ovabrw-category-ajax').first();
			if (!$ajax.length && $widget.hasClass('ovabrw-category-ajax')) {
				$ajax = $widget;
			}
		}
		if (!$ajax.length) {
			return;
		}
		SCHEDULE_DELAYS_MS.forEach(function (ms) {
			window.setTimeout(function () {
				clickTodosInScope($ajax);
			}, ms);
		});
	}

	var elementorHookDone = false;

	function registerElementorHook() {
		if (elementorHookDone) {
			return;
		}
		if (typeof elementorFrontend === 'undefined' || !elementorFrontend.hooks) {
			return;
		}
		elementorHookDone = true;
		elementorFrontend.hooks.addAction(
			'frontend/element_ready/ovabrw_product_category_ajax.default',
			function ($element) {
				if (typeof offitravelHomeSearch === 'undefined') {
					return;
				}
				var sid = offitravelHomeSearch.sectionId;
				var $anchor = $('#' + sid);
				if (!$anchor.length) {
					return;
				}
				var inside =
					$element.closest('#' + sid).length ||
					$element.attr('id') === sid;
				if (!inside) {
					return;
				}
				activateTodosIfAnchored($element);
			},
			100
		);
	}

	function activateTodosIfHashAfterLateLoad() {
		if (typeof offitravelHomeSearch === 'undefined' || !hashMatchesSection()) {
			return;
		}
		window.setTimeout(function () {
			activateTodosIfAnchored(null);
		}, 0);
	}

	$(document).on('submit', '.ovabrw-search-form', function (e) {
		if (typeof offitravelHomeSearch === 'undefined') {
			return;
		}
		var $form = $(this);
		if (!destinationIsAll($form)) {
			return;
		}
		if (!monthsAreEmpty($form)) {
			return;
		}
		e.preventDefault();

		var baseNoHash = String(offitravelHomeSearch.homeUrl || '').split('#')[0];
		var sectionId = offitravelHomeSearch.sectionId;

		if (isSameDocumentAsHomeBase(baseNoHash)) {
			var curHash = (window.location.hash || '').replace(/^#/, '');
			if (curHash !== sectionId) {
				window.location.hash = sectionId;
			}
			activateTodosIfAnchored(null);
			window.setTimeout(scrollToCategorySection, 0);
			window.setTimeout(scrollToCategorySection, 350);
		} else {
			window.location.href = baseNoHash + '#' + sectionId;
		}
	});

	$(window).on('hashchange', function () {
		if (typeof offitravelHomeSearch === 'undefined') {
			return;
		}
		activateTodosIfAnchored(null);
	});

	$(window).on('elementor/frontend/init', function () {
		registerElementorHook();
		activateTodosIfHashAfterLateLoad();
	});

	$(function () {
		if (typeof elementorFrontend !== 'undefined' && elementorFrontend.hooks) {
			registerElementorHook();
			activateTodosIfHashAfterLateLoad();
		}
	});

	$(window).on('load', function () {
		if (typeof offitravelHomeSearch === 'undefined' || !hashMatchesSection()) {
			return;
		}
		var $ajax = resolveHomeCategoryAjaxRoot();
		if (!$ajax.length) {
			return;
		}
		window.setTimeout(function () {
			clickTodosInScope($ajax);
		}, 200);
		window.setTimeout(function () {
			clickTodosInScope($ajax);
		}, 600);
	});
})(jQuery);
