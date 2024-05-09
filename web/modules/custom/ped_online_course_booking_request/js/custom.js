(function ($, Drupal, drupalSettings) {
  $(document).ready(function () {
    // Dynamic Your Organization / School fields
    field = '.form-item-civicrm-2-contact-1-contact-existing';
    fieldset = '#edit-civicrm-2-contact-1-fieldset-fieldset legend, div.form-item-civicrm-2-contact-1-contact-organization-name';
    description = '#edit-civicrm-2-contact-1-contact-existing--description';
    $(`${fieldset}`).hide();
    $('#existing-org').parent().hide();
    $('#new-org').on('click', function (e) {
      e.preventDefault();
      $(fieldset).toggle();
      $(field).toggle();
      $('#existing-org').parent().show();
      $('#edit-civicrm-3-contact-1-cg52-fieldset > legend').hide();
    });

    $('#existing-org').on('click', function (e) {
      $('#new-org').trigger('click');
      $('#edit-civicrm-3-contact-1-cg52-fieldset > legend').show();
      $(this).parent().hide();
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
    // visually hide the youth and adult field so that it can still be submitted by webform.
    $("[name='civicrm_1_activity_1_cg2_custom_90']").parent().hide();
    if ($('#edit-civicrm-1-activity-1-cg2-custom-90').val() == 'Adult') {
      $('#edit-civicrm-1-activity-1-cg2-custom-25--wrapper').hide();
      $('#edit-civicrm-1-activity-1-cg2-custom-25-adults-ages-18').trigger('click');
    }
  });
})(jQuery, Drupal, drupalSettings);
