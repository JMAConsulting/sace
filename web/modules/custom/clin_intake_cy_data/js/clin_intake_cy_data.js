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

  function toggleCustom1384() {
    if ($("#edit-civicrm-1-contact-1-cg65-custom-1383-0").is(":checked")) {
      // If "No" is checked, show element
      $(".form-item-civicrm-1-contact-1-cg65-custom-1384").show();
    } else {
      // If "Yes" is checked, hide element
      $(".form-item-civicrm-1-contact-1-cg65-custom-1384").hide();
    }
  }

  $(
    "#edit-civicrm-1-contact-1-cg65-custom-1383-0, #edit-civicrm-1-contact-1-cg65-custom-1383-1"
  ).on("change", toggleCustom1384);

  toggleCustom1384();

  // Populating parent and guardian field
  var guardian1 = $("#edit-civicrm-1-contact-1-cg21-custom-234").val();
  var guardian2 = $("#edit-civicrm-1-contact-1-cg23-custom-242").val();
  var concatenatedValue;

  if (guardian1 != "" && guardian2 != "") {
    concatenatedValue = guardian1 + " & " + guardian2;
  } else {
    concatenatedValue = guardian1 + guardian2;
  }

  $("#edit-civicrm-1-contact-1-cg65-custom-1391").val(concatenatedValue);

  if ($("#edit-civicrm-1-contact-1-cg21-custom-298-1").prop("checked")) {
    $("#edit-civicrm-1-contact-1-cg65-custom-1389").val(
      $("#edit-civicrm-1-contact-1-cg21-custom-234").val()
    );
    $("#edit-civicrm-1-contact-1-cg65-custom-1390").val(
      $("#edit-civicrm-1-contact-1-cg21-custom-238").val()
    );
  } else if ($("#edit-civicrm-1-contact-1-cg23-custom-300-1").prop("checked")) {
    $("#edit-civicrm-1-contact-1-cg65-custom-1389").val(
      $("#edit-civicrm-1-contact-1-cg23-custom-242").val()
    );
    $("#edit-civicrm-1-contact-1-cg65-custom-1390").val(
      $("#edit-civicrm-1-contact-1-cg23-custom-244").val()
    );
  } else if ($("#edit-civicrm-1-contact-1-cg19-custom-301-1").prop("checked")) {
    $("#edit-civicrm-1-contact-1-cg65-custom-1389").val(
      $("#edit-civicrm-1-contact-1-cg19-custom-175").val()
    );
    $("#edit-civicrm-1-contact-1-cg65-custom-1390").val("Case Worker");
  } else if ($("#edit-civicrm-1-contact-1-cg22-custom-299-1").prop("checked")) {
    $("#edit-civicrm-1-contact-1-cg65-custom-1389").val(
      $("#edit-civicrm-1-contact-1-cg22-custom-236").val()
    );
    $("#edit-civicrm-1-contact-1-cg65-custom-1390").val(
      "Family Support Worker"
    );
  } else {
    $("#edit-civicrm-1-contact-1-cg65-custom-1390").val("Self Intake");
  }

  if ($("#edit-civicrm-1-contact-1-cg21-custom-234").val() != "") {
    $("#edit-civicrm-1-contact-1-cg21-fieldset").addClass(
      "form-readonly webform-readonly"
    );
    $("#edit-civicrm-1-contact-1-cg21-fieldset")
      .find("input")
      .each(function () {
        $(this).prop("readonly", true);
      });
  }

  if ($("#edit-civicrm-1-contact-1-cg23-custom-242").val() != "") {
    $("#edit-civicrm-1-contact-1-cg23-fieldset").addClass(
      "form-readonly webform-readonly"
    );
    $("edit-civicrm-1-contact-1-cg23-fieldset")
      .find("input")
      .each(function () {
        $(this).prop("readonly", true);
      });
  }

  if ($("#edit-civicrm-1-contact-1-cg19-custom-175").val() != "") {
    $("#edit-civicrm-1-contact-1-cg19-fieldset").addClass(
      "form-readonly webform-readonly"
    );
    $("#edit-civicrm-1-contact-1-cg19-fieldset")
      .find("input")
      .each(function () {
        $(this).prop("readonly", true);
      });
  }

  if ($("#edit-civicrm-1-contact-1-cg22-custom-236").val() != "") {
    $("#edit-civicrm-1-contact-1-cg22-fieldset").addClass(
      "form-readonly webform-readonly"
    );
    $("#edit-civicrm-1-contact-1-cg22-fieldset")
      .find("input")
      .each(function () {
        $(this).prop("readonly", true);
      });
  }

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
  convertDateFormat("#edit-civicrm-1-activity-1-cg67-custom-1413");

  function freezeDates(fieldName) {
    $(fieldName + "-date").prop("disabled", true);
    $(fieldName + "-time").prop("disabled", true);
    $(fieldName).addClass("webform-readonly");
  }

  freezeDates("#edit-civicrm-1-activity-1-cg67-custom-1413");
  freezeDates("#edit-civicrm-1-activity-1-cg67-custom-1414");
});
