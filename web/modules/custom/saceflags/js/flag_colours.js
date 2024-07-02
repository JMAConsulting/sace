(function ($) {
  $(document).ready(function () {
    $(".saceflag").each(function () {
      var flagColour = $(this).attr("flag-colour");
      $(this).css("color", flagColour);
    });
  });
})(jQuery);
