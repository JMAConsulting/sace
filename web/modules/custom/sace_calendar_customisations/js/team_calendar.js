(function($, Drupal, once, drupalSettings) {
  $('#block-mainpagecontent').insertBefore($('#block-oliverolocal-fullcalendarteam'));
  $('#edit-tid').parent().hide();
  
  $('#block-pagetitle > h1').text($('#edit-tid option:selected').text() + ' Team Calendar');
})(jQuery, Drupal, once, drupalSettings);
