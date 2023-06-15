(function($, Drupal) {
  $(document).on('ready', setTimeout(function() {
    drupalSettings.calendar.forEach(function(cal, index) {
      drupalSettings.calendar[index].on('select', function(info, el) {
        if (info.allDay) {
          var dateParts = info.startStr.split('T');
          $('#edit-civicrm-1-activity-1-activity-activity-date-time-date').val(dateParts[0]);
          $('#edit-civicrm-1-activity-1-activity-activity-date-time-time').val('00:00');
          $('#edit-civicrm-1-activity-1-activity-duration').val(1440);
        }
        else {
         var start = new Date(info.startStr).getTime(), end = new Date(info.endStr).getTime(), diff = (end - start)/ (1000 * 60);
         var dateParts = info.startStr.split('T');
          $('#edit-civicrm-1-activity-1-activity-activity-date-time-date').val(dateParts[0]);
          $('#edit-civicrm-1-activity-1-activity-activity-date-time-time').val(dateParts[1]);
          $('#edit-civicrm-1-activity-1-activity-duration').val(diff);
        }
      });
    });
  }, 3000));
})(jQuery, Drupal);
