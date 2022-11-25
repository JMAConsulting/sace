(function ($, Drupal) {
  $(document).ready(function () {
    update_q1('presentation topic');
  });
  $(document).bind('change', 'select', function () {
    //Get current question value 
    let curVal = $("#edit-q1--wrapper-legend").children('span').text().substring(37);
    update_q1(curVal);
    //update_q2()
  });
  function update_q1(curval) {
    //
    let topic = document.getElementById('edit-civicrm-1-activity-1-cg2-custom-40').value;
    let q1 = $("#edit-q1--wrapper-legend").children('span').text();
    let newVal = q1.replace(curval, topic);
    $("#edit-q1--wrapper-legend").children('span').text(function () {
      return newVal;
    });
  }
})(jQuery, Drupal);

