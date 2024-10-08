jQuery(document).ready(function ($, settings) {
  //Presentation Topic Custom/SOmething Different toggle text field
  $(`${drupalSettings.community_engagement_booking_update.start_date}-date, ${drupalSettings.community_engagement_booking_update.start_date}-time, ${drupalSettings.community_engagement_booking_update.end_date}-date, ${drupalSettings.community_engagement_booking_update.end_date}-time`).on('input', function () {
    var end = $(`${drupalSettings.community_engagement_booking_update.end_date}-date`).val();
    var endTime = $(`${drupalSettings.community_engagement_booking_update.end_date}-time`).val();
    var start = $(`${drupalSettings.community_engagement_booking_update.start_date}-date`).val();
    var startTime = $(`${drupalSettings.community_engagement_booking_update.start_date}-time`).val();
    if (end && start) {
      setDuration(start + ' ' + startTime, end + ' ' + endTime, endTime);
    }
  });

  function setDuration(start, end, endTime) {
    start = new Date(start);
    end = new Date(end);
    var duration = (end.getTime() - start.getTime()) / (1000 * 60);
    $('#edit-civicrm-1-activity-1-activity-duration').val(duration);
  }

  let checkbox = "#edit-civicrm-1-activity-1-cg2-custom-40-customunsure";
  let field = "#edit-civicrm-1-activity-1-cg2-custom-41";
  let lable = 'label[for="edit-civicrm-1-activity-1-cg2-custom-41"]';
  let bt = $('#edit-civicrm-1-activity-1-activity-activity-type-id').val();
  if (!$(`${checkbox}`).is(":checked")) {
    $(`${field}`).hide();
    $(`${lable}`).hide();
  }

  $(`${checkbox}`).click(
    function () {
      if ($(this).is(":checked")) {
        $(`${field}`).show();
        $(`${lable}`).show();
      } else {
        $(`${field}`).hide();
        $(`${lable}`).hide();
      }
    }
  );

  //Dynamic Your Organization / School fields
  var field1 = '.form-item-civicrm-2-contact-1-contact-existing';
  var fieldset = '#edit-civicrm-2-contact-1-fieldset-fieldset';
  var description = '#edit-civicrm-2-contact-1-contact-existing--description';
  $(`${field1}`).hide();
  $('#existing-org').on('click', function (e) {
    e.preventDefault();
    $(`${field1}`).show();
    $(`${fieldset}`).hide();
    $(this).parent().hide();
  });

  $('#new-org-block').on('click', function (e) {
    e.preventDefault();
    $(`${fieldset}`).show();
    $(`${field1}`).hide();
    $('#existing-org').parent().hide();
  });

  $('#block-oliverolocal-fullcalendarblock').insertAfter($('#edit-civicrm-1-activity-1-activity-details-value').parent().parent());

});
