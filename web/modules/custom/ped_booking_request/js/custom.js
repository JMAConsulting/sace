(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
      //Dynamic Your Organization / School fields
      field = '.form-item-civicrm-3-contact-1-contact-existing';
      fieldset = '#edit-civicrm-3-contact-1-fieldset-fieldset';
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
    });
})(jQuery, Drupal);
