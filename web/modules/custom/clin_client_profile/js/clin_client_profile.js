(function ($) {
  $(document).ready(function () {
    // Get the current URL.
    var currentUrl = window.location.href;

    // Define a regular expression to match the specified pattern.
    var urlPattern = /\/contact-information(?:\/[\w-]+)*\/(\d+)/;
    if (urlPattern.test(currentUrl)) {
      var cid = currentUrl.match(urlPattern)[1];
      // Update the href of form tabs
      $(".tabs__link").each(function () {
        var $this = $(this);
        var href = $this.attr("href");
        // Filter contact info form by cid
        if (href.indexOf("/contact-information/edit/" + cid) !== -1) {
          var newHref = "/contact-information/edit/" + cid + "?cid=" + cid;
          $this.attr("href", newHref);
        }
        // Filter security form by cid
        if (href.indexOf("/contact-information/assigned-staff/" + cid) !== -1) {
          var newHref =
            "/contact-information/assigned-staff/" + cid + "?cid=" + cid;
          $this.attr("href", newHref);
        }
      });
    }
  });
})(jQuery);