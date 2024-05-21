jQuery(document).ready(function ($) {
  // Change button value to Submit if legal guardian is no
  function updateButtonLabel() {
    if (
      $("#edit-are-you-the-legal-guardian-no").is(":checked") ||
      $("#edit-has-this-been-reported-no").is(":checked")
    ) {
      $("#edit-actions-submit").val("Submit");
    } else {
      $("#edit-actions-submit").val("Book Intake");
    }
  }

  $('input[name="are_you_the_legal_guardian"]').change(updateButtonLabel);

  $('input[name="has_this_been_reported_"]').change(updateButtonLabel);

  updateButtonLabel();

  // Removing blank create new contact option
  $('#edit-civicrm-3-contact-1-contact-existing option[value="-"]').remove();
});
