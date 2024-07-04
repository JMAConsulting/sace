jQuery(document).ready(function ($) {
  var currentUrl = window.location.href;
  var editContactInfo = /\/contact-information\/edit\/(\d+)/;
  var flags = /\/contact-information\/flags\/(\d+)/;

  if (editContactInfo.test(currentUrl)) {
    $("#webform-submission-client-profile-add-form div").addClass(
      "webform-readonly"
    );
    $("#edit-submit").hide();
  } else if (flags.test(currentUrl)) {
    $(".meeting-buttons .button--secondary").remove();
  }
});
