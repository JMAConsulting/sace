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

  $("#edit-civicrm-1-activity-1-activity-activity-type-id").on(
    "change",
    function () {
      var selectedValue = $(
        "#edit-civicrm-1-activity-1-activity-activity-type-id"
      ).val();
      if (selectedValue == 77 || selectedValue == 78) {
        $("#edit-civicrm-1-activity-1-activity-duration").val(30);
      } else {
        $("#edit-civicrm-1-activity-1-activity-duration").val(60);
      }
    }
  );

  function prepopulateAutocomplete() {
    var contactField = $("#edit-civicrm-3-contact-1-contact-existing");
    var contactName = contactField.data("civicrm-name");
    var contactId = contactField.data("civicrm-id");

    if (contactName && contactId) {
      var prepopulateValue = contactName + " (" + contactId + ")";
    }

    var selectedValue = $(
      "#edit-civicrm-1-activity-1-activity-activity-type-id"
    ).val();
    var autocompleteField = $("#edit-select-counsellor");
    if (selectedValue == 77 || selectedValue == 78) {
      autocompleteField = $("#edit-select-intake-staff");
    }
    autocompleteField.val(prepopulateValue);
    autocompleteField.trigger("change"); // or any other relevant event

    // If not a CLIN Reminder
    if (
      $("#edit-civicrm-1-activity-1-activity-activity-type-id").val() != 346
    ) {
      // Remove "completed" status from form
      $(
        '#edit-civicrm-1-activity-1-activity-status-id option[value="2"]'
      ).remove();
      $(
        '#edit-civicrm-1-activity-1-activity-activity-type-id option[value="346"]'
      ).remove();
    } else {
      var selectElement = $("#edit-civicrm-1-activity-1-activity-status-id");
      selectElement.find("option").each(function () {
        var optionValue = $(this).val();
        if (optionValue !== "1" && optionValue !== "2") {
          $(this).remove();
        }
      });
    }
  }
  prepopulateAutocomplete();
});
