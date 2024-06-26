(function ($) {
  $(document).ready(function () {
    $("tr").each(function () {
      // Find the cell with the activity type ID
      var activityTypeCell = $(this).find(
        ".views-field-activity-type-id > div"
      );

      if (activityTypeCell.length) {
        // Get the class name (assuming there is only one class and it represents the color)
        var className = activityTypeCell.attr("class");

        // Apply the class as the background color of the first cell in the row
        if (className) {
          $(this)
            .find("td:first")
            .css("background-color", "#" + className);
        }
      }
    });
  });
})(jQuery);
