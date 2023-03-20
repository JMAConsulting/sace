(function($, Drupal) {
   $(document).ready(function() {
console.log('a');
  $('#edit-civicrm-3-contact-1-fieldset-fieldset').hide();
  $('.form-item-civicrm-3-contact-1-contact-existing #edit-civicrm-3-contact-1-contact-existing--description').html('If you cannot find your organization, please click <a id="new-org" href=#> here</a> to add one.')
  $('#new-org').on('click', function(e) {
    e.preventDefault();
    $('#edit-civicrm-3-contact-1-fieldset-fieldset').show();
    $('.form-item-civicrm-3-contact-1-contact-existing').hide();
  });
});
})(jQuery, Drupal);
