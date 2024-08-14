jQuery(document).ready(function ($) {
  var currentUrl = window.location.href;
  var contactInfo = /\/contact-information\/(\d+)/;
  var flags = /\/contact-information\/flags\/(\d+)/;
  var appointments = /\/contact-information\/appointments\/(\d+)/;

  if (contactInfo.test(currentUrl)) {
    $("#webform-submission-client-profile-add-form div").addClass(
      "webform-readonly"
    );
    $("#edit-submit").hide();
  } else if (flags.test(currentUrl)) {
    $(".meeting-buttons .button--secondary").remove();
  } else if (appointments.test(currentUrl)) {
    $(".meeting-buttons .button--secondary").remove();
  }
});
