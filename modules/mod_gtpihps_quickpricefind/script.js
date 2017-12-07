jQuery.noConflict();
(function($) {
    $(window).load(function() {
        var loadOptions = function(el, task, options) {
            var options = jQuery.extend({ 'task' : task }, options);
            el.prev('i').remove();
            el.prev().after('&nbsp;&nbsp;<i class="fa fa-refresh fa-spin fa-lg"></i>');
            el.attr('disabled', true);
            el.load(component_url, options, function() {
                el.removeAttr('disabled');
                el.prev('i').remove();
            });

        }

        var loadMarkets = function() {
            loadOptions($('#filter_qp_market_ids'), 'stats_province.loadMarkets', { 'filter_regency_ids' : $('#filter_qp_regency_ids').val() });
        }

       loadMarkets();
        $('#filter_qp_regency_ids').change(function() {
            loadMarkets();
        });
    });
})(jQuery);