(function ($, Drupal) {
  $(document).ready(function () {
  let checkbox = "#edit-civicrm-1-activity-1-cg2-custom-40-customunsure";
  let field = "#edit-civicrm-1-activity-1-cg2-custom-41";
  let label = 'label[for="edit-civicrm-1-activity-1-cg2-custom-41"]';
console.log($(`${field}`));  
$(`${field}`).toggle($(`${checkbox}`).is(":checked"));
    $(`${label}`).toggle($(`${checkbox}`).is(":checked"));

  $(`${checkbox}`).on('click', function() {
    $(`${field}`).toggle($(this).is(":checked"));
    $(`${label}`).toggle($(this).is(":checked"));
});

    });
})(jQuery, Drupal);
