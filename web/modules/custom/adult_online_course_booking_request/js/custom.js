(function ($, Drupal, drupalSettings) {
  $(document).ready(function () {
    // Dynamic Your Organizatio fields
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

    // visually hide the youth or adult field so that it can still be submitted by webform.
    $("[name='" + `${drupalSettings.adult_online_course_booking_request.youth_or_adult}` + "']").parent().hide();
  });
})(jQuery, Drupal, drupalSettings);
