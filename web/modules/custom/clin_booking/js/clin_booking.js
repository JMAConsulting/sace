jQuery(document).ready(function ($) {

  let currRow = 1;

  // Hide extra referral fields
  $("#edit-civicrm-2-contact-2-cg76-fieldset").hide();
  $("#edit-civicrm-2-contact-1-cg76-fieldset").hide();

  var addAnotherRef = $("<input>", {
    class: "webform-button button button--primary",
    type: "button",
    id: "add-contact-button",
    value: "Add Additional Contact",
    click: function () {
      elementUnhide = "#edit-civicrm-2-contact-" + currRow + "-cg76-fieldset";
      $(elementUnhide).show();
      currRow += 1;
      if (currRow == 3) {
        addAnotherRef.hide();
      }
    },
  });

  // Hide/show different submit buttons depending on input
  function updateButtonLabel() {
    if (
      $("#edit-proceed-with-booking-an-intake-no").is(":checked") ||
      $("#edit-has-this-been-reported-no").is(":checked")
    ) {
      $("#edit-actions-01-submit").show();
      $("#edit-actions-submit").hide();
      addAnotherRef.hide();
    } else {
      $("#edit-actions-01-submit").hide();
      $("#edit-actions-submit").show();
      if (currRow != 3) {
        addAnotherRef.show();
      }
    }
  }

  $('input[name="are_you_the_legal_guardian"]').change(updateButtonLabel);

  $('input[name="has_this_been_reported_"]').change(updateButtonLabel);

  updateButtonLabel();

  // Removing blank create new contact option
  $('#edit-civicrm-3-contact-1-contact-existing option[value="-"]').remove();

  // Making caller checkboxes mutually exclusive
  function makeMutuallyExclusive(checkboxes) {
    checkboxes.change(function () {
      if (this.checked) {
        checkboxes.not(this).prop("checked", false);
      }
    });
  }

  // Select caller checkboxes
  var checkboxes = $(
    "#edit-civicrm-2-contact-1-cg23-custom-300-1, " +
      "#edit-civicrm-2-contact-1-cg19-custom-301-1, " +
      "#edit-civicrm-2-contact-1-cg22-custom-299-1, " +
      "#edit-civicrm-2-contact-1-cg21-custom-298-1"
  );

  makeMutuallyExclusive(checkboxes);

  // Hide default activity type option
  $(
    'label[for="edit-civicrm-1-activity-1-activity-activity-type-id-336"]'
  ).hide();
  $("#edit-civicrm-1-activity-1-activity-activity-type-id-336").hide();

  // Hide new client option unless selected in autocomplete
  function toggleFieldVisibility() {
    var selectValue = $(".token-input-token p").text();
    if (selectValue === "+ Create new +") {
      $("#edit-civicrm-2-contact-1-fieldset-fieldset").show();
    } else {
      $("#edit-civicrm-2-contact-1-fieldset-fieldset").hide();
      convertDateFormat();
    }
  }

  toggleFieldVisibility();

  $("#edit-civicrm-2-contact-1-contact-existing").change(function () {
    toggleFieldVisibility();
  });

  function convertDateFormat() {
    var originalDate = $("#edit-civicrm-2-contact-1-contact-birth-date").val();
    if (originalDate.trim() !== "") {
      // Convert the date format using JavaScript Date object
      var convertedDate = new Date(originalDate).toLocaleDateString("en-GB", {
        day: "2-digit",
        month: "short",
        year: "numeric",
      });
      $("#edit-civicrm-2-contact-1-contact-birth-date").val(convertedDate);
    }
  }

  $("#edit-civicrm-2-contact-1-contact-birth-date").on("change", function () {
    convertDateFormat();
  });

  convertDateFormat();

  // Insert the button before case worker section
  $("#edit-civicrm-2-contact-1-cg19-fieldset").before(addAnotherRef);
  
});
