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

      calendarOptions.customButtons = {
	goToButton: {
          text: 'Go to date',
          click:  function () {
            var calendar =  Drupal.fullcalendar_block.instances[$('.fullcalendar-block').attr('data-calendar-block-index')].calendar;
            var date = $('#edit-activity-date-time').val();
            if (date != null && date != '') {
              calendar.gotoDate(Date.parse(date));
            }
          }
        }
      }
  }


$(document).on("fullcalendar_block.beforebuild", eventCalendarBeforeBuild);
$('.js-form-item-activity-date-time').insertBefore($('#block-oliverolocal-fullcalendarblock'));
$('.js-form-item-activity-date-time').css('width', '200px');

if ($('#block-oliverolocal-fullcalendar-myactivity').length > 0) {
   $('.js-form-item-activity-date-time').insertBefore($('#block-oliverolocal-fullcalendar-myactivity'));
   $('#edit-submit-calendar-counsellors').parent().hide();
}
if ($('#block-oliverolocal-fullcalendarteam').length > 0) {
   $('.js-form-item-activity-date-time').insertBefore($('#block-oliverolocal-fullcalendarteam'));
}

})(jQuery, Drupal, once, drupalSettings);
