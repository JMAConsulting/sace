jQuery(document).ready(function ($) {
  // Define the ID of the block where the button will be added
  var $block = $("#block-oliverolocal-webform");

  // Create a button element
  var $button = $(
    '<a class="meeting-buttons button--secondary" id="edit-fields-button">Edit Contact Information</button>'
  );

  // Insert the button above the block
  $block.after($button);

  // Handle the button click event
  $button.on("click", function () {
    // Toggle the 'disabled' attribute of all input fields and textareas within the block
    $block
      .find("input, textarea, select")
      .prop("disabled", function (_, disabled) {
        return !disabled; // Toggle the disabled property
      });
  });
});
