jQuery(document).ready(function ($, settings) {

  let currRow = 2;

  // Hide extra fields
  $("#edit-civicrm-1-contact-2-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-3-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-4-cg70-fieldset").hide();
  $("#edit-civicrm-1-contact-5-cg70-fieldset").hide();

  // Create the button element
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
  $("#edit-medical-mental-health-history").before(button);

});
