(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
      var bt = $('#edit-civicrm-1-activity-1-activity-activity-type-id').val();
if (bt == 204 || bt == 203 || bt == 201) {
  $('#block-pagetitle').hide();
}
      //Dynamic Your Organization / School fields
      field = '.form-item-civicrm-3-contact-1-contact-existing';
      fieldset = '#edit-civicrm-3-contact-1-fieldset-fieldset';
      // description = '#edit-civicrm-3-contact-1-contact-existing--description';

      if (bt == 58) {
     //  $(`${field} > label`).text().replace('Organization', 'School');
      }

      $(`${fieldset}`).hide();
      $('#existing-org').on('click', function (e) {
        e.preventDefault();
        $(`${field}`).show();
        $(`${fieldset}`).hide();
      //  $('.form-item-civicrm-3-contact-1-contact-existing .token-input-delete-token').trigger('click');
        $(`${field} .token-input-delete-token`).trigger('click');
      });

      $('#new-org-block').on('click', function (e) {
          e.preventDefault();
          $(`${fieldset}`).show();
          $(`${field}`).hide();
       //   $('.form-item-civicrm-3-contact-1-contact-existing .token-input-delete-token').trigger('click');
        $(`${field} .token-input-delete-token`).trigger('click');
      });
    });
})(jQuery, Drupal);
