(function ($) {
  $(document).ready(function () {
    // Get the current URL.
    var currentUrl = window.location.href;

    // Define a regular expression to match the specified pattern.
    var urlPattern = /\/contact-information(?:\/[\w-]+)*\/(\d+)/;
    if (urlPattern.test(currentUrl)) {
      // Get page title and split into two titles
      var $pageTitle = $("#block-pagetitle .page-title");
      var titleText = $pageTitle.text();
      var titleParts = titleText.split(" - ");
      $pageTitle.text(titleParts[0]);

      var $newPageTitle = $("<h2>")
        .addClass("title page-title")
        .text(titleParts[0]);
      $("#block-pagetitle .page-title").replaceWith($newPageTitle);

      // Create new title with client name
      var $newHeader = $("<h1>").text(titleParts[1]);
      $("#block-tabs").before($newHeader);

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
      });
    }
  });
})(jQuery);