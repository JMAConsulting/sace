(function($, Drupal, once, drupalSettings) {

    function eventCalendarBeforeBuild(event, calendarOptions) {
      calendarOptions.loading = function (isLoading) {
        if (isLoading) {
          $('#cover-spin').show();
        //  $('div#block-oliverolocal-fullcalendarblock').hide();
        }
        else {
          $('#cover-spin').hide();
        //  $('div#block-oliverolocal-fullcalendarblock').show();
        }
      }
    }


$(document).on("fullcalendar_block.beforebuild", eventCalendarBeforeBuild);

})(jQuery, Drupal, once, drupalSettings);
