jQuery(document).ready(function ($) {
  let currRow = 2;
  let currRow2 = 2;

  // Hide extra incident fields
  $("#edit-civicrm-1-contact-2-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-3-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-4-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-5-cg70-fieldset").hide();

  // Add button element to unhide extra SA history fields
  var addAnother = $("<input>", {
    class: "webform-button button button--primary",
    type: "button",
    id: "add-row-button",
    value: "Add Another Incident",
    click: function () {
      elementUnhide = "#edit-civicrm-1-contact-" + currRow + "-cg70-fieldset";
      $(elementUnhide).show();
      currRow += 1;
      if (currRow == 6) {
        addAnother.hide();
      }
    },
  });

  // Hide extra referral fields
  $("#edit-civicrm-2-contact-1-fieldset-fieldset").hide();
  $("#edit-civicrm-3-contact-1-fieldset-fieldset").hide();
  $("#edit-civicrm-4-contact-1-fieldset-fieldset").hide();

  var addAnotherRef = $("<input>", {
    class: "webform-button button button--primary",
    type: "button",
    id: "add-referral-button",
    value: "Add Another Referral",
    click: function () {
      elementUnhide =
        "#edit-civicrm-" + currRow2 + "-contact-1-fieldset-fieldset";
      $(elementUnhide).show();
      currRow2 += 1;
      if (currRow2 == 5) {
        addAnotherRef.hide();
      }
    },
  });

  // Insert the button before mental health section
  $("#edit-medical-mental-health-history").before(addAnother);

  // Insert button after first referral
  $("#edit-civicrm-1-contact-1-cg68-custom-1366--wrapper").before(
    addAnotherRef
  );

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
      $("#edit-civicrm-5-contact-1-fieldset-fieldset").show();
    } else {
      $("#edit-civicrm-5-contact-1-fieldset-fieldset").hide();
    }
  }

  updateDetailsTextVisibility();
  updateReferralSource();

  // Uncheck auto-checked element
  $("#edit-civicrm-1-contact-1-cg68-custom-1371-0").removeAttr("checked");
  $("#edit-civicrm-1-contact-1-cg68-custom-1418-0").removeAttr("checked");

  function convertDateFormat(fieldName) {
    var originalDate = $(fieldName).val();
    if (originalDate.trim() !== "") {
      // Convert the date format using JavaScript Date object
      var convertedDate = new Date(originalDate).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
      $(fieldName).val(convertedDate);
    }
  }

  convertDateFormat("#edit-civicrm-1-activity-1-cg67-custom-1414");

  function freezeDates(fieldName) {
    $(fieldName + "-date").prop("disabled", true);
    $(fieldName + "-time").prop("disabled", true);
    $(fieldName).addClass("webform-readonly");
  }

  // Hide button is client does not comply with SACE policy
  function toggleButton() {
    if ($("#edit-civicrm-1-contact-1-cg68-custom-1432-1").is(":checked")) {
      $("#add-row-button").hide();
      $("#add-referral-button").hide();
    } else {
      $("#add-row-button").show();
      $("#add-referral-button").show();
    }
  }

  toggleButton();

  $('input[name="civicrm_1_contact_1_cg68_custom_1432"]').change(function () {
    toggleButton();
  });

  // Add styling and show submitted date if form is locked
  if ($(".webform-locked-message").length) {
    $("#webform-submission-clin-adult-intake-data-add-form div").addClass(
      "webform-readonly"
    );
    $(".form-item-civicrm-1-activity-1-cg67-custom-1414").show();
    freezeDates("#edit-civicrm-1-activity-1-cg67-custom-1414");
    $("#edit-submit").hide();

    for (let currContact = 2; currContact < 5; currContact++) {
      var orgNameText = $(
        "#civicrm_" + currContact + "_contact_1_contact_organization_name"
      )
        .text()
        .trim();

      if (orgNameText.length > 0) {
        $(
          "#edit-civicrm-" + currContact + "-contact-1-fieldset-fieldset"
        ).show();
      }
    }
    // Expose incidents if filled in
    for (let currIncident = 2; currIncident < 5; currIncident++) {
      var $fieldset = $(
        "#edit-civicrm-1-contact-" + currIncident + "-cg70-fieldset"
      );
      var $textareas = $fieldset.find("textarea");
      var hasText = false;
      $textareas.each(function () {
        var textareaText = $(this).val().trim();
        if (textareaText.length > 0) {
          hasText = true;
          return false;
        }
      });

      if (hasText) {
        $("#edit-civicrm-1-contact-" + currIncident + "-cg70-fieldset").show();
      }
    }
    addAnotherRef.hide();
    addAnother.hide();
  } else {
    $(".form-item-civicrm-1-activity-1-cg67-custom-1414").hide();
  }
});
