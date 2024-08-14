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
    $("#webform-submission-client-profile-add-form")
      .find("fieldset")
      .each(function () {
        var $fieldset = $(this);

        // Remove readonly and disabled attributes from the fieldset itself
        $fieldset.removeAttr("readonly").removeAttr("disabled");

        // Remove the 'is-disabled' class from the legend if it exists
        $fieldset.find(".fieldset__label").removeClass("is-disabled");

        // Handle inputs inside the fieldset
        $fieldset.find("input, textarea, select").each(function () {
          var $element = $(this);

          // Check if the current element is one of the fields to keep readonly
          if (
            $element.attr("id") ===
              "edit-civicrm-1-contact-1-contact-contact-id" ||
            $element.attr("id") === "edit-civicrm-1-contact-1-cg12-custom-116"
          ) {
            // Skip removing readonly attribute for these specific fields
            return;
          }

          // Remove readonly attribute
          if ($element.is("[readonly]")) {
            $element.removeAttr("readonly");
          }

          // Remove disabled attribute
          if ($element.is("[disabled]")) {
            $element.removeAttr("disabled");
          }

          // For checkboxes and radios, explicitly enable them
          if ($element.is(":checkbox, :radio")) {
            $element.prop("disabled", false);
            // Remove the 'form-disabled' class from the parent div if it exists
            $element.closest(".form-disabled").removeClass("form-disabled");
            // Remove 'is-disabled' class from the label
            $element.siblings("label").removeClass("is-disabled");
          }

          // For select elements, remove disabled
          if ($element.is("select")) {
            $element.prop("disabled", false);
          }
        });

        // Remove 'is-disabled' class from labels within fieldset
        $fieldset.find("label.is-disabled").removeClass("is-disabled");
      });

    $("#edit-submit").show();

    $("#webform-submission-client-profile-add-form div").removeClass(
      "webform-readonly"
    );

    $(".form-item-civicrm-1-contact-1-contact-contact-id").addClass(
      "webform-readonly"
    );

    $(".form-item-civicrm-1-contact-1-cg12-custom-116").addClass(
      "webform-readonly"
    );
    $button.hide();
  });
});
