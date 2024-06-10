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

    $(".views-field-note .field-content").each(function () {
      var $this = $(this);
      var uri = $this.text().trim();

      // Convert uri to the correct url path
      if (uri.startsWith("private://")) {
        var url = uri.replace("private://", "/system/files/");

        var fileName = url.split("/").pop();
        var downloadButton = `
          <a href="${url}" download class="download-button">
            Download ${fileName}
          </a>
          <span class="file__size"></span>
        `;

        // Replace the text content with the download button
        $this.html(downloadButton);
      }
    });
  });
})(jQuery);
