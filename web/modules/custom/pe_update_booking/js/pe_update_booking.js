jQuery(document).ready(function ($, settings) {  
  //Presentation Topic Custom/SOmething Different toggle text field
  $(`${drupalSettings.ped_booking_update.start_date}-date, ${drupalSettings.ped_booking_update.start_date}-time, ${drupalSettings.ped_booking_update.end_date}-date, ${drupalSettings.ped_booking_update.end_date}-time`).on('change', function () {
        var end = $(`${drupalSettings.ped_booking_update.end_date}-date`).val();
        var endTime = $(`${drupalSettings.ped_booking_update.end_date}-time`).val();
        var start = $(`${drupalSettings.ped_booking_update.start_date}-date`).val();
        var startTime = $(`${drupalSettings.ped_booking_update.start_date}-time`).val();
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
          $(`${drupalSettings.ped_booking_update.end_date}-date, ${drupalSettings.ped_booking_update.end_date}-time`).val('');
          $('#edit-civicrm-1-activity-1-activity-duration').val('');
          return;
        }

        $('#edit-civicrm-1-activity-1-activity-duration').val((end.getTime() - start.getTime()) / (1000 * 60));
      }

  let checkbox = "#edit-civicrm-1-activity-1-cg2-custom-40-customsomethingdifferent";
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

  if (bt == 204 || bt == 203) {
    $('div.js-form-type-textfield form-item-civicrm-1-activity-1-cg58-custom-1267').insertAfter($('#edit-flexbox-08'));
  }

});
