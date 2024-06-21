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

    // Calculate luminance (to see if text colour needs to be black/white)
    function calculateLuminance(hex) {
      var r = parseInt(hex.slice(0, 2), 16) / 255;
      var g = parseInt(hex.slice(2, 4), 16) / 255;
      var b = parseInt(hex.slice(4, 6), 16) / 255;

      var a = [r, g, b].map(function (v) {
        return v <= 0.03928 ? v / 12.92 : Math.pow((v + 0.055) / 1.055, 2.4);
      });

      return 0.2126 * a[0] + 0.7152 * a[1] + 0.0722 * a[2];
    }

    $(".accordion-header").each(function () {
      var hexColor = $(this).find("b").attr("class");
      if (hexColor) {
        $(this).css("background-color", "#" + hexColor);

        // Set text color based on luminance
        var luminance = calculateLuminance(hexColor);
        if (luminance < 0.5) {
          $(this).css("color", "white");
        } else {
          $(this).css("color", "black");
        }
      }
    });
  });
})(jQuery);
