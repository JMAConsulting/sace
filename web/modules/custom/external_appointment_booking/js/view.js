(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {

      // Dynamic Your Organization / School fields
      field = '.form-item-civicrm-1-contact-1-contact-existing';
      fieldset = '#edit-contact-1-fieldset-fieldset';
      description = '#edit-civicrm-1-contact-1-contact-existing--description';
      $(`${fieldset}`).hide();
      $('#new-org').on('click', function (e) {
          e.preventDefault();
          $(fieldset).toggle();
          $(field).toggle();
      });

      $('#existing-org').on('click', function (e) {
          $('#new-org').trigger('click');
      });

      $(`${drupalSettings.external_appointment_booking.start_date}`).on('change', function() {
        var end = $(`${drupalSettings.external_appointment_booking.end_date}`).val();
        if (end && $(this).val()) {
          setDuration($(this).val(), end);
        }
      });

      $(`${drupalSettings.external_appointment_booking.end_date}`).on('change', function() {
        var start = $(`${drupalSettings.external_appointment_booking.start_date}`).val();
        if (start && $(this).val()) {
          setDuration(start, $(this).val());
        }
      });

      function setDuration(start, end) {
        start = new Date(start);
        end = new Date(end);
        if (start > end) {
          alert('Start Date cannot be after the End Date');
          $(`${drupalSettings.external_appointment_booking.end_date}`).val('');
          $('#edit-civicrm-1-activity-1-activity-duration').val('');
          return;
        }

        $('#edit-civicrm-1-activity-1-activity-duration').val((end.getTime() - start.getTime()) / (1000 * 60));
      }
    });
})(jQuery, Drupal, drupalSettings);
