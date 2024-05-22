jQuery(document).ready(function ($) {
  // Attach change event handlers to mental health checkboxes
  $("#edit-civicrm-1-contact-1-cg65-custom-1393-8").change(function () {
    updateReferralSource();
  });

  function updateReferralSource() {
    if ($("#edit-civicrm-1-contact-1-cg65-custom-1393-8").prop("checked")) {
      $(".form-item-civicrm-1-contact-1-cg65-custom-1394").show();
    } else {
      $(".form-item-civicrm-1-contact-1-cg65-custom-1394").hide();
    }
  }
  updateReferralSource();

  // Uncheck elements
  $("#edit-civicrm-1-contact-1-cg65-custom-1383-0").removeAttr("checked");
  $("#edit-civicrm-1-contact-1-cg65-custom-1410-0").removeAttr("checked");
  $(".form-item-civicrm-1-contact-1-cg65-custom-1384").hide();

  // Populating parent and guardian field
  var guardian1 = $("#edit-civicrm-1-contact-1-cg21-custom-234").val();
  var guardian2 = $("#edit-civicrm-1-contact-1-cg23-custom-242").val();

  // Concatenate the values with "&" symbol
  var concatenatedValue = guardian1 + " & " + guardian2;

  // Set the concatenated value to the third input element
  $("#edit-civicrm-1-contact-1-cg65-custom-1391").val(concatenatedValue);
});
