(function ($, Drupal) {
  $(document).ready(function () {
  let checkbox = "#edit-civicrm-1-activity-1-cg2-custom-40-customunsure";
  let field = "#edit-civicrm-1-activity-1-cg2-custom-41";
  let label = 'label[for="edit-civicrm-1-activity-1-cg2-custom-41"]';

$(`${drupalSettings.ped_activity_schedule.start_date}-date, ${drupalSettings.ped_activity_schedule.start_date}-time, ${drupalSettings.ped_activity_schedule.end_date}-date, ${drupalSettings.ped_activity_schedule.end_date}-time`).on('input', function () {
  var end = $(`${drupalSettings.ped_activity_schedule.end_date}-date`).val();
  var endTime = $(`${drupalSettings.ped_activity_schedule.end_date}-time`).val();
  var start = $(`${drupalSettings.ped_activity_schedule.start_date}-date`).val();
  var startTime = $(`${drupalSettings.ped_activity_schedule.start_date}-time`).val();

  if (end && start) {
    setDuration(start + ' ' + startTime, end + ' ' + endTime, endTime);
  }
});

function setDuration(start, end, endTime) {
  start = new Date(start);
  end = new Date(end);
  var duration = (end.getTime() - start.getTime()) / (1000 * 60);
  if (duration < 0 && endTime) {
    alert('Start Date cannot be after the End Date');
    $(`${drupalSettings.ped_activity_schedule.end_date}-date, ${drupalSettings.ped_activity_schedule.end_date}-time`).val('');
    $('#edit-civicrm-1-activity-1-activity-duration').val('');
    return;
  }

  $('#edit-civicrm-1-activity-1-activity-duration').val(duration);
}

//Dynamic Your Organization / School fields
field1 = '#edit-civicrm-1-contact-1-cg52-fieldset';
fieldset = '#edit-civicrm-1-contact-1-fieldset-fieldset';
description = '#edit-civicrm-1-contact-1-contact-existing--description';

$(`${fieldset}`).hide();
$('#new-org-block').on('click', function (e) {
  e.preventDefault();
  $(`${field1}`).show();
  $(`${fieldset}`).hide();
  $(`${field1} .token-input-delete-token`).trigger('click');
});

$('#existing-org').on('click', function (e) {
    e.preventDefault();
    $(`${fieldset}`).show();
    $(`${field1}`).hide();
    $(`${field1} .token-input-delete-token`).trigger('click');
});

$(`${field}`).toggle($(`${checkbox}`).is(":checked"));
    $(`${label}`).toggle($(`${checkbox}`).is(":checked"));

  $(`${checkbox}`).on('click', function() {
    $(`${field}`).toggle($(this).is(":checked"));
    $(`${label}`).toggle($(this).is(":checked"));
});

    });
})(jQuery, Drupal);
