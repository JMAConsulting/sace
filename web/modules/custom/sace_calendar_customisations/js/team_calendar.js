(function($, Drupal, once, drupalSettings) {
  $('#block-mainpagecontent').insertBefore($('#block-oliverolocal-fullcalendarteam'));
  $('#edit-tid').parent().hide();
  $('.view-content > div').hide();
  $('#block-pagetitle > h1').text($('#edit-tid option:selected').text().replace('- Any -', 'All Staff') + ' Team Calendar');
})(jQuery, Drupal, once, drupalSettings);
