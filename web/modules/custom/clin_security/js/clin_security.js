(function ($) {
  $(document).ready(function () {
    // Add checkboxes to view by checking custom div html
    $('[class^="relationship_checkbox_"]').each(function () {
      var classNames = $(this).attr("class").split(" ");
      var id = classNames[0].split("_")[2];
      var checkbox = $('<input type="checkbox">');

      // Set the checkbox ID
      checkbox.attr("id", "checkbox_" + id);

      // Check if relationship is active
      if (classNames[0].split("_")[3] == "1") {
        checkbox.prop("checked", true);
      }

      $(this).append(checkbox);
    });

    $(document).on(
      "change",
      '[class^="relationship_checkbox_"] input[type="checkbox"]',
      function () {
        var checkedValues = [];
        var uncheckedValues = [];

        // Get ids of checked and unchecked boxes
        $('[class^="relationship_checkbox_"] input[type="checkbox"]').each(
          function () {
            if ($(this).is(":checked")) {
              checkedValues.push($(this).attr("id").split("_")[1]);
            } else {
              uncheckedValues.push($(this).attr("id").split("_")[1]);
            }
          }
        );
        // Store ids in webform
        $("#edit-is-active").text(checkedValues.join(","));
        $("#edit-is-not-active").text(uncheckedValues.join(","));
      }
    );
  });
})(jQuery);
