(function($) {
  $(window).on('jsframe_create', function(event) {
    let frame = event.data.jsFrame;
    frame.on('#copy-evaluation-url-button', 'click', function(_frame, e) {
      e.preventDefault();
      navigator.clipboard.writeText($('#evaluation-form-link').prop('href'));
    });
  });
})(jQuery);