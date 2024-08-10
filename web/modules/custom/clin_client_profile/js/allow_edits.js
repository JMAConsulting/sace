jQuery(document).ready(function ($) {
  // Define the ID of the block where the button will be added
  var $block = $("#block-oliverolocal-webform");

  // Create a button element
  var $button = $(
    '<a class="meeting-buttons button--secondary" id="edit-fields-button">Edit Contact Information</button>'
  );

  // Insert the button above the block
  $block.after($button);

  $button.on("click", function () {
    $("#webform-submission-client-profile-add-form div").removeClass(
      "webform-readonly"
    );
  });
});
