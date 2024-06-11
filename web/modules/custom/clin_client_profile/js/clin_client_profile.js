(function ($) {
  $(document).ready(function () {
    // Define the URL pattern to match.
    var urlPattern =
      /^https:\/\/staging\.sace\.jmaconsulting\.biz\/client-home\/\d+$/;
    // Get the current URL.
    var currentUrl = window.location.href;

    // Check if the current URL matches the defined pattern.
    if (urlPattern.test(currentUrl)) {
      // Update the href attribute of the anchor element.
      $(".tabs__link.js-tabs-link.is-active").attr(
        "href",
        function (index, oldHref) {
          return oldHref + "?cid=" + oldHref.match(/\d+$/)[0];
        }
      );
    }
  });
})(jQuery);
