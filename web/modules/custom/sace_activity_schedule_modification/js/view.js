(function ($, Drupal) {
  $(document).ready(function () {
  let checkbox = "#edit-civicrm-1-activity-1-cg2-custom-40-customunsure";
  let field = "#edit-civicrm-1-activity-1-cg2-custom-41";
  let label = 'label[for="edit-civicrm-1-activity-1-cg2-custom-41"]';

$(`${drupalSettings.ped_activity_schedule.start_date}-date, ${drupalSettings.ped_activity_schedule.start_date}-time, ${drupalSettings.ped_activity_schedule.end_date}-date, ${drupalSettings.ped_activity_schedule.end_date}-time`).on('change', function () {
  var end = $(`${drupalSettings.ped_activity_schedule.end_date}-date`).val();
  var endTime = $(`${drupalSettings.ped_activity_schedule.end_date}-time`).val();
  var start = $(`${drupalSettings.ped_activity_schedule.start_date}-date`).val();
  var startTime = $(`${drupalSettings.ped_activity_schedule.start_date}-time`).val();

  if (end && start) {
    var end1 = new Date(end);
    var start1 = new Date(start);
    var sameDay = (((end1.getTime() - start1.getTime()) / (1000 * 60)) == 0);
    setDuration(start + ' ' + startTime, end + ' ' + endTime, sameDay);
  }
});

function setDuration(start, end, sameDay) {
  start = new Date(start);
  end = new Date(end);
  if (start > end && !sameDay) {
    alert('Start Date cannot be after the End Date');
    $(`${drupalSettings.ped_activity_schedule.end_date}-date, ${drupalSettings.ped_activity_schedule.end_date}-time`).val('');
    $('#edit-civicrm-1-activity-1-activity-duration').val('');
    return;
  }

  $('#edit-civicrm-1-activity-1-activity-duration').val((end.getTime() - start.getTime()) / (1000 * 60));
}

$(`${field}`).toggle($(`${checkbox}`).is(":checked"));
    $(`${label}`).toggle($(`${checkbox}`).is(":checked"));

  $(`${checkbox}`).on('click', function() {
    $(`${field}`).toggle($(this).is(":checked"));
    $(`${label}`).toggle($(this).is(":checked"));
});

    });
})(jQuery, Drupal);
