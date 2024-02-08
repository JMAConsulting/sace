(function($, Drupal, once, drupalSettings) {

    function eventCalendarBeforeBuild(event, calendarOptions) {
      calendarOptions.loading = function (isLoading) {
        if (isLoading) {
          $('div#block-oliverolocal-progressbar').show();
        //  $('div#block-oliverolocal-fullcalendarblock').hide();
        }
        else {
          $('div#block-oliverolocal-progressbar').hide();
        //  $('div#block-oliverolocal-fullcalendarblock').show();
        }
      }
    }


$(document).on("fullcalendar_block.beforebuild", eventCalendarBeforeBuild);

})(jQuery, Drupal, once, drupalSettings);
