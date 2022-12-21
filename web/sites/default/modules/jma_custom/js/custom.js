(function($, Drupal) {
   $(document).ready(function() {
  $('#edit-civicrm-1-contact-1-cg14-custom-245').on('change', function(){
    $('#edit-civicrm-1-activity-1-activity-activity-date-time').val($(this).val());
  });
  $('#edit-civicrm-1-contact-1-cg14-custom-246').on('change', function() {
    $('#edit-civicrm-2-activity-1-activity-activity-date-time').val($(this).val());
  });
  $('.form-item-civicrm-1-activity-1-activity-activity-date-time, .form-item-civicrm-2-activity-1-activity-activity-date-time').hide();
});
})(jQuery, Drupal);
