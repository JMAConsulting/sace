jQuery(document).ready(function ($) {
  function extractNumberInBrackets(value) {
    var match = value.match(/\((\d+)\)$/);
    if (match) {
      return match[1];
    } else {
      return null;
    }
  }

  // Update contact ID with whatever is selected in the counsellor autocomplete
  $("#edit-select-counsellor").on("change", function () {
    var selectedValue = $(
      "#edit-civicrm-1-activity-1-activity-activity-type-id"
    ).val();
    if (selectedValue != 77 && selectedValue != 78) {
      var newValue = $(this).val();
      var numberInBrackets = extractNumberInBrackets(newValue);
      var contactField = $("#edit-civicrm-3-contact-1-contact-existing");

      if (numberInBrackets !== null) {
        $("#edit-civicrm-3-contact-1-contact-first-name").val("");
        $("#edit-civicrm-3-contact-1-contact-last-name").val("");
        $("#edit-civicrm-3-contact-1-email-email").val("");
        contactField.val(numberInBrackets).trigger("change");
      } else {
        contactField.val("").trigger("change");
      }
    }
  });

  // Update contact ID with whatever is selected in the intake staff autocomplete
  $("#edit-select-intake-staff").on("change", function () {
    var selectedValue = $(
      "#edit-civicrm-1-activity-1-activity-activity-type-id"
    ).val();

    if (selectedValue == 77 || selectedValue == 78) {
      var newValue = $(this).val();
      var numberInBrackets = extractNumberInBrackets(newValue);
      var contactField = $("#edit-civicrm-3-contact-1-contact-existing");

      if (numberInBrackets !== null) {
        $("#edit-civicrm-3-contact-1-contact-first-name").val("");
        $("#edit-civicrm-3-contact-1-contact-last-name").val("");
        $("#edit-civicrm-3-contact-1-email-email").val("");
        contactField.val(numberInBrackets).trigger("change");
      } else {
        contactField.val("").trigger("change");
      }
    }
  });
});
