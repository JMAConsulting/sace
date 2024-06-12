(function ($) {
  $(document).ready(function () {
    // Get the current URL.
    var currentUrl = window.location.href;

    // Define a regular expression to match the specified pattern.
    var urlPattern = /\/client-home(?:\/[\w-]+)*\/(\d+)/;
    if (urlPattern.test(currentUrl)) {
      var cid = currentUrl.match(urlPattern)[1];
      // Update the href of form tabs
      $(".tabs__link").each(function () {
        var $this = $(this);
        var href = $this.attr("href");
        // Filter contact info form by cid
        if (href.indexOf("/client-home/contact-information/" + cid) !== -1) {
          var newHref =
            "/client-home/contact-information/" + cid + "?cid=" + cid;
          $this.attr("href", newHref);
        }
        // Filter security form by cid
        if (href.indexOf("/client-home/security/" + cid) !== -1) {
          var newHref = "/client-home/security/" + cid + "?cid=" + cid;
          $this.attr("href", newHref);
        }
      });
    }
  });
})(jQuery);