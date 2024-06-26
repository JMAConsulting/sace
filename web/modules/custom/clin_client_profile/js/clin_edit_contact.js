(function ($) {
  $(document).ready(function () {
    if ($(".webform-locked-message").length) {
      $("#webform-submission-client-profile-add-form div").addClass(
        "webform-readonly"
      );
      $("#edit-submit").hide();
    }
  });
})(jQuery);
