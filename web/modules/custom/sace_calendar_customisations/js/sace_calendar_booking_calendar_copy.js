(function($) {
  $('#copy-evaluation-url-button').on('click', function(e) {
    e.preventDefault();
    navigator.clipboard.writeText($('#evaluation-form-link').prop('href'));
    Drupal.Message().add('Evaluation Url copied');
  });
})(jQuery);