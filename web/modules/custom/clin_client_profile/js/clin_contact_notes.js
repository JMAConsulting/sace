(function ($) {
  $(document).ready(function () {
    $(".accordion-item").each(function () {
      var $accordionItem = $(this);
      var $nothingField = $accordionItem.find(".views-field-nothing");
      var $view = $nothingField.find(".view");
      // Check if the nothing field contains any notes on notes
      if ($view.children().length > 0) {
        // Remove the append buttons if there is already a note attached
        $accordionItem.find(".views-field-nothing-1").remove();
      }
    });
  });
})(jQuery);
