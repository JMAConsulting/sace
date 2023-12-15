(function ($, Drupal, drupalSettings) {
  $(document).ready(function () {
    //Dynamic Your Organization / School fields
    field = '.form-item-civicrm-3-contact-1-contact-existing';
    fieldset = '#edit-civicrm-3-contact-1-fieldset-fieldset legend, div.form-item-civicrm-3-contact-1-contact-organization-name';
    description = '#edit-civicrm-3-contact-1-contact-existing--description';
    $(`${fieldset}`).hide();
    $('#existing-org').on('click', function (e) {
      e.preventDefault();
      $(`${field}`).show();
      $(`${fieldset}`).hide();
      $(`${field} .token-input-delete-token`).trigger('click');
    });

    $('#new-org-block').on('click', function (e) {
      e.preventDefault();
      $(`${fieldset}`).show();
      $(`${field}`).hide();
      $(`${field} .token-input-delete-token`).trigger('click');
    });

    $('#edit-civicrm-1-activity-1-cg2-custom-40-customunsure').on('click', function(e) {
      $('.form-item-civicrm-1-activity-1-cg2-custom-41').toggle($(this).is(":checked"));
    });

    // Visually Hide Youth and Adult field so that it can still be submitted by webform.
    $("[name='civicrm_1_activity_1_cg2_custom_90']").parent().parent().parent().hide();

  });
})(jQuery, Drupal);
