jQuery(document).ready(function ($) {
  let currRow = 2;

  // Hide extra fields
  $("#edit-civicrm-1-contact-2-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-3-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-4-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-5-cg70-fieldset").hide();

  // Add button element to unhide extra SA history fields
  var addAnother = $("<input>", {
    class: "webform-button button button--primary",
    type: "button",
    id: "add-row-button",
    value: "Add Another",
    click: function () {
      elementUnhide = "#edit-civicrm-1-contact-" + currRow + "-cg70-fieldset";
      $(elementUnhide).show();
      currRow += 1;
      if (currRow == 6) {
        addAnother.hide();
      }
    },
  });

  // Insert the button before mental health section
  $("#edit-medical-mental-health-history").before(addAnother);

  // Hide details text area unless either box is checked
  var detailsText = $(".form-item-civicrm-1-contact-1-cg68-custom-1340");

  // Attach change event handlers to mental health checkboxes
  $("#edit-civicrm-1-contact-1-cg68-custom-1325-8").change(function () {
    updateReferralSource();
  });

  // Attach change event handlers to checkboxes
  $(
    "#edit-civicrm-1-contact-1-cg68-custom-1339-1, #edit-civicrm-1-contact-1-cg68-custom-1339-2"
  ).change(function () {
    updateDetailsTextVisibility();
  });

  function updateDetailsTextVisibility() {
    if (
      $("#edit-civicrm-1-contact-1-cg68-custom-1339-1").prop("checked") ||
      $("#edit-civicrm-1-contact-1-cg68-custom-1339-2").prop("checked")
    ) {
      detailsText.show();
    } else {
      detailsText.hide();
    }
  }

  function updateReferralSource() {
    if ($("#edit-civicrm-1-contact-1-cg68-custom-1325-8").prop("checked")) {
      $(".form-item-civicrm-1-contact-1-cg68-custom-1326").show();
    } else {
      $(".form-item-civicrm-1-contact-1-cg68-custom-1326").hide();
    }
  }

  updateDetailsTextVisibility();
  updateReferralSource();

  // Uncheck auto-checked element
  $("#edit-civicrm-1-contact-1-cg68-custom-1371-0").removeAttr("checked");
  $("#edit-civicrm-1-contact-1-cg68-custom-1418-0").removeAttr("checked");

  function convertDateFormat() {
    var originalDate = $("#civicrm_1_activity_1_cg67_custom_1414").val();
    if (originalDate.trim() !== "") {
      // Convert the date format using JavaScript Date object
      var convertedDate = new Date(originalDate).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
      $("#civicrm_1_activity_1_cg67_custom_1414").val(convertedDate);
    }
  }

  convertDateFormat();
});
