(function ($) {
    "use strict";

    function ovabrwNormalizeSelect2Term(s) {
        if (!s) {
            return '';
        }
        try {
            return String(s).toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
        } catch (e) {
            return String(s).toLowerCase();
        }
    }

    /**
     * Match destination title or ACF province keywords (data-ovabrw-search); hide placeholder "all" while searching.
     */
    function ovabrwDestinationSelect2Matcher(params, data) {
        if (!data) {
            return null;
        }
        if (data.children && data.children.length) {
            return null;
        }
        var term = $.trim(params.term || '');
        var $opt = data.element ? $(data.element) : $();
        var isDestSelect = $opt.length && $opt.parent('#brw-destinations-select-box').length;

        if (isDestSelect && data.id === 'all') {
            if (term !== '') {
                return null;
            }
            return data;
        }
        if (term === '') {
            return data;
        }
        if (typeof data.text === 'undefined') {
            return null;
        }
        var nt = ovabrwNormalizeSelect2Term(term);
        if (ovabrwNormalizeSelect2Term(data.text).indexOf(nt) !== -1) {
            return data;
        }
        if ($opt.length) {
            var extra = $opt.attr('data-ovabrw-search');
            if (extra && ovabrwNormalizeSelect2Term(extra).indexOf(nt) !== -1) {
                return data;
            }
        }
        return null;
    }

    var ovabrwSelect2SearchLang = {
        noResults: function () {
            return 'No se encontraron resultados';
        }
    };

    var ovabrwMonthSelect2Lang = $.extend({}, ovabrwSelect2SearchLang, {
        /* Cadena vacía: no mostrar tooltip al llegar al máximo (2 meses). */
        maximumSelected: function () {
            return '';
        }
    });

    function ovabrwUpdateMonthSummary($monthSelect) {
        if (!$monthSelect || !$monthSelect.length) {
            return;
        }

        var items = $monthSelect.select2('data') || [];
        var sorted = items
            .filter(function (item) {
                return item && item.id && item.text;
            })
            .sort(function (a, b) {
                var ai = $monthSelect.find('option[value="' + a.id + '"]').index();
                var bi = $monthSelect.find('option[value="' + b.id + '"]').index();
                return ai - bi;
            })
            .map(function (item) {
                return String(item.text).trim();
            });

        var summary = '';
        if (sorted.length > 3) {
            summary = sorted.slice(0, 3).join(', ') + ' y más';
        } else if (sorted.length) {
            summary = sorted.join(', ');
        } else {
            summary = $monthSelect.data('placeholder') || '';
        }
        var $rendered = $monthSelect.next('.select2-container').find('.select2-selection__rendered');

        $rendered.attr('data-summary', summary);
        $rendered.attr('title', sorted.length ? summary : '');
    }

    $(window).on('elementor/frontend/init', function () {
        $('.ovabrw-search .ovabrw-search-form').each(function () {
            const that = $(this);

            // Guests picker
            const guestspicker = that.find('.ovabrw-guestspicker');

            // Guests picker controls
            let guestsPickerControl = $(this).find('.guestspicker-control')
            guestspicker.on('click', function () {
                guestsPickerControl = $(this).closest('.guestspicker-control').toggleClass('active');
            });

            $(window).click(function (e) {
                const guestsPickerContent = $('.ovabrw-guestspicker-content');
                if (!guestspicker.is(e.target) && guestspicker.has(e.target).length === 0 && !guestsPickerContent.is(e.target) && guestsPickerContent.has(e.target).length === 0) {
                    guestsPickerControl.removeClass('active');
                }
            });

            const minus = that.find('.minus');
            minus.on('click', function () {
                gueststotal($(this), 'sub');
            });

            const plus = that.find('.plus');
            plus.on('click', function () {
                gueststotal($(this), 'sum');
            });

            // select2 — destinos / taxonomías (matcher ACF) y meses (multi-selección)
            that.find('#brw-destinations-select-box, .brw_custom_taxonomy_dropdown').select2({
                width: '100%',
                matcher: ovabrwDestinationSelect2Matcher,
                language: ovabrwSelect2SearchLang
            });
            that.find('.ovabrw-monthpicker-start').each(function () {
                var $m = $(this);
                $m.select2({
                    width: '100%',
                    multiple: true,
                    maximumSelectionLength: 12,
                    closeOnSelect: false,
                    placeholder: $m.data('placeholder') || '',
                    matcher: ovabrwDestinationSelect2Matcher,
                    language: ovabrwMonthSelect2Lang,
                    containerCssClass: 'ovabrw-monthpicker-container',
                    dropdownCssClass: 'ovabrw-monthpicker-dropdown'
                });

                ovabrwUpdateMonthSummary($m);
                $m.on('change select2:select select2:unselect', function () {
                    ovabrwUpdateMonthSummary($m);
                });
            });

        });

        function gueststotal(that, cal) {
            const guestsButton = that.closest('.guests-button');

            // Guest input
            const input = guestsButton.find('input[type="text"]');

            // Guest data
            let value = input.val();
            const min = input.attr('min');
            const max = input.attr('max');

            if (cal == 'sub' && parseInt(value) > parseInt(min)) {
                input.val(parseInt(value) - 1);
            }

            if (cal == 'sum' && parseInt(value) < parseInt(max)) {
                input.val(parseInt(value) + 1);
            }

            const guestsPickerControl = that.closest('.guestspicker-control');

            // Adults
            const adults = parseInt(guestsPickerControl.find('.ovabrw_adults').val()) || 0;

            // Children
            const children = parseInt(guestsPickerControl.find('.ovabrw_childrens').val()) || 0;

            // Babies
            const babies = parseInt(guestsPickerControl.find('.ovabrw_babies').val()) || 0;

            // Guests total
            const gueststotal = guestsPickerControl.find('.gueststotal');
            if (gueststotal) {
                gueststotal.text(adults + children + babies);
            }
        }

    });
})(jQuery);