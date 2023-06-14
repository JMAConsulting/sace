(function ($, Drupal, drupalSettings) {
  $(document).ready(function () {
    // Dynamic Your Organization / School fields
    field = '.form-item-civicrm-2-contact-1-contact-existing';
    fieldset = '#edit-civicrm-2-contact-1-fieldset-fieldset';
    description = '#edit-civicrm-2-contact-1-contact-existing--description';
    $(`${fieldset}`).hide();
    $('#new-org').on('click', function (e) {
      e.preventDefault();
      $(fieldset).toggle();
      $(field).toggle();
    });

    $('#existing-org').on('click', function (e) {
      $('#new-org').trigger('click');
    });

    let optional_addons_select = document.getElementById(`${drupalSettings.ped_online_course_booking_request.optional_addons_select}`);
    let option = optional_addons_select.options[1].value;
    $(`#${drupalSettings.ped_online_course_booking_request.optional_addons_select} option`).each(function () {
      if ($(this).val() != option && $(this).val() != '') {
        $(this).hide();
      }
    });
    $(`#${drupalSettings.ped_online_course_booking_request.high_school_checkbox}`).click(function () {
      if ($(this).is(":checked")) {
        $(`#${drupalSettings.ped_online_course_booking_request.optional_addons_select} option`).each(function () {
          if ($(this).val() == option) {
            $(this).hide();
          }
          else {
            $(this).show();
          }
        });
      }
      else {
        $(`#${drupalSettings.ped_online_course_booking_request.optional_addons_select} option`).each(function () {
          if ($(this).val() != option && $(this).val() != '') {
            $(this).hide();
          }
          else {
            $(this).show();
          }
        });
      }
    });
  });
})(jQuery, Drupal, drupalSettings);
