jQuery(document).ready(function ($, settings) {

  $('fieldset.radios--wrapper .fieldset__wrapper input[value="Strongly Disagree"]').each(function() {
    $('<a href="#" class="crm-hover-button crm-clear-link" title="Clear"><i class="crm-i fa-times" aria-hidden="true"></i></a>').insertAfter($(this));
  });
  
  $('a.crm-clear-link').on('click', function(e) {
    e.preventDefault();
    $(this).parent().siblings().each(function() {
      if ($(this).children('input[type="radio"]').is(':checked')) {
        $(this).children('input[type="radio"]').prop('checked', false).trigger('change');
      }
    });
  });

});
