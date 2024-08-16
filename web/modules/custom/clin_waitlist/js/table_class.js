// Replace table class from views aggregator plus with default views-table class
(function ($) {
    $(document).ready(function(){
        $('.table').addClass('views-table').removeClass('table');
    });
})(jQuery);