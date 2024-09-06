(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
      $(`${drupalSettings.youth_online_course_booking_update.start_date}-date, ${drupalSettings.youth_online_course_booking_update.start_date}-time, ${drupalSettings.youth_online_course_booking_update.end_date}-date, ${drupalSettings.youth_online_course_booking_update.end_date}-time`).on('input', function () {
        var end = $(`${drupalSettings.youth_online_course_booking_update.end_date}-date`).val();
        var endTime = $(`${drupalSettings.youth_online_course_booking_update.end_date}-time`).val();
        var start = $(`${drupalSettings.youth_online_course_booking_update.start_date}-date`).val();
        var startTime = $(`${drupalSettings.youth_online_course_booking_update.start_date}-time`).val();

        if (end && start) {
          setDuration(start + ' ' + startTime, end + ' ' + endTime, endTime);
        }
      });

      function setDuration(start, end, endTime) {
        start = new Date(start);
        end = new Date(end);
        var duration = (end.getTime() - start.getTime()) / (1000 * 60);

        $('#edit-civicrm-1-activity-1-activity-duration').val(duration);
      }

      //Dynamic Your Organization / School fields
      var field1 = '.form-item-civicrm-2-contact-1-contact-existing';
      var fieldset = '#edit-civicrm-2-contact-1-fieldset-fieldset';
      var description = '#edit-civicrm-2-contact-1-contact-existing--description';
      $(`${field1}`).hide();
      $('#existing-org').on('click', function (e) {
        e.preventDefault();
        $(`${field1}`).show();
        $(`${fieldset}`).hide();
      });

      $('#new-org-block').on('click', function (e) {
          e.preventDefault();
          $(`${fieldset}`).show();
          $(`${field1}`).hide();
      });

     $('#block-oliverolocal-fullcalendarblock').insertAfter($('#edit-civicrm-1-activity-1-activity-details-value').parent().parent());

    });
  })(jQuery, Drupal, drupalSettings);
