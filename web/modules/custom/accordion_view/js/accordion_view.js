(function ($) {
  $(document).ready(function () {
    function createAccordionItem(item, headerClass, contentClass) {
      var accordionItem = $('<div class="accordion-item"></div>');

      // Move fields inside accordion item
      item.children().appendTo(accordionItem);
      item.replaceWith(accordionItem);

      // Find first field to be header
      var header = accordionItem.children().first();
      header.addClass(headerClass);
      header.on("click", function () {
        $(this)
          .nextAll("." + contentClass)
          .slideToggle();
        $(this).toggleClass("active");
      });
      accordionItem.children().not(header).addClass(contentClass).hide();
    }

    // Add wrapper for outer border
    $(".view-content").wrap('<div class="accordion-wrapper"></div>');

    $(".view-appointment-notes .view-content .views-row").each(function () {
      createAccordionItem(
        $(this),
        "inner-accordion-header",
        "inner-accordion-content"
      );
    });

    $(".view-content > .views-row").each(function () {
      createAccordionItem($(this), "accordion-header", "accordion-content");
    });

    $('span:contains("private://webform/add_note_to_appointment")').each(
      function () {
        var url = $(this).text().trim();
        if (url.startsWith("private://webform/add_note_to_appointment")) {
          // Create a download link from the URL
          var fileName = url.split("/").pop();
          var downloadUrl = url.replace("private://", "/system/files/");

          // Create the download button
          var downloadButton = $("<a>")
            .attr("href", downloadUrl)
            .attr("download", fileName)
            .addClass("download-button")
            .text("Download " + fileName);

          // Replace the text with the download button
          $(this).html(downloadButton);
        }
      }
    );
  });
})(jQuery);
