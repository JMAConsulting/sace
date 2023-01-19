(function($, Drupal) {
  $(document).on('ready', setTimeout(function() {
    drupalSettings.calendar.forEach(function(cal, index) {
      drupalSettings.calendar[index].on('select', function(info, el) {
        if (info.allDay) {
          var dateParts = info.startStr.split('T');
          $('#' + drupalSettings.sace_calender_customisations.fields.date).val(dateParts[0]);
          $('#' + drupalSettings.sace_calender_customisations.fields.time).val('00:00');
          $('#' + drupalSettings.sace_calender_customisations.fields.duration).val(1440);
        }
        else {
         var start = new Date(info.startStr).getTime(), end = new Date(info.endStr).getTime(), diff = (end - start)/ (1000 * 60);
         var dateParts = info.startStr.split('T');
          $('#' + drupalSettings.sace_calender_customisations.fields.date).val(dateParts[0]);
          $('#' + drupalSettings.sace_calender_customisations.fields.time).val(dateParts[1]);
          $('#' + drupalSettings.sace_calender_customisations.fields.duration).val(diff);
        }
      });
    });
  }, 3000));
})(jQuery, Drupal);
