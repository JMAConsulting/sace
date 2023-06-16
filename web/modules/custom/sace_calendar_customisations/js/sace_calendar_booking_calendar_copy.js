(function($, Drupal) {
  $.on('jsframe_create', function(event) {
    let frame = event.data.jsFrame;
    frame.on('#copy-evaluation-url-button', 'click', function(e) {
      e.preventDefault();
      navigator.clipboard.writeText($('#evaluation-form-link').prop('href'));
      Drupal.Message().add('Evaluation Url copied');
    });
  });
})(jQuery, Drupal);