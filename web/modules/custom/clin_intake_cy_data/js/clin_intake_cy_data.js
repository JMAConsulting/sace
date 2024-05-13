jQuery(document).ready(function ($) {
  // Attach change event handlers to mental health checkboxes
  $("#edit-civicrm-1-contact-1-cg65-custom-1393-8").change(function () {
    updateReferralSource();
  });

  function updateReferralSource() {
    if ($("edit-civicrm-1-contact-1-cg65-custom-1393-8").prop("checked")) {
      $(".form-item-civicrm-1-contact-1-cg65-custom-1394").show();
    } else {
      $(".form-item-civicrm-1-contact-1-cg65-custom-1394").hide();
    }
  }
  updateReferralSource();
});
