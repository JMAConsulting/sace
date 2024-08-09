jQuery(document).ready(function ($) {
  function parseDate(dateStr) {
    return new Date(dateStr);
  }

  var $table = $(".views-table");
  var $rows = $table.find("tbody tr").get();

  $rows.sort(function (a, b) {
    var dateA = $(a).find(".views-field-date time").attr("datetime");
    var dateB = $(b).find(".views-field-date time").attr("datetime");

    // Parse the date strings
    var timestampA = parseDate(dateA);
    var timestampB = parseDate(dateB);

    // Sort in descending order
    return timestampB - timestampA;
  });

  // Append the sorted rows back to the table body
  $.each($rows, function (index, row) {
    $table.children("tbody").append(row);
  });
});
