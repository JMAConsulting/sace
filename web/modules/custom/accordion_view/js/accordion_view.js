(function ($) {
  $(document).ready(function () {
    $(".view-content .views-row").each(function (index) {
      var accordionItem = $('<div class="accordion-item"></div>');

      // Move fields in views-row inside accordion item
      $(this).children().appendTo(accordionItem);

      // Replace views-row with the accordion item
      $(this).replaceWith(accordionItem);

      var header = accordionItem.find(".views-field").first();
      header.addClass("accordion-header");
      header.on("click", function () {
        $(this).nextAll().slideToggle();
        $(this).toggleClass("active");
      });

      // Hide tabs
      accordionItem.find(".views-field").not(header).hide();

      if (index === 0) {
        header.addClass("first-header");
      }
    });
  });
})(jQuery);
