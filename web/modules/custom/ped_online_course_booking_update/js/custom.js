(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
      $(`${drupalSettings.ped_online_course_booking_update.start_date}-date, ${drupalSettings.ped_online_course_booking_update.start_date}-time, ${drupalSettings.ped_online_course_booking_update.end_date}-date, ${drupalSettings.ped_online_course_booking_update.end_date}-time`).on('input', function () {
        var end = $(`${drupalSettings.ped_online_course_booking_update.end_date}-date`).val();
        var endTime = $(`${drupalSettings.ped_online_course_booking_update.end_date}-time`).val();
        var start = $(`${drupalSettings.ped_online_course_booking_update.start_date}-date`).val();
        var startTime = $(`${drupalSettings.ped_online_course_booking_update.start_date}-time`).val();

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
          $(`${drupalSettings.ped_online_course_booking_update.end_date}-date, ${drupalSettings.ped_online_course_booking_update.end_date}-time`).val('');
          $('#edit-civicrm-1-activity-1-activity-duration').val('');
          return;
        }
  
        $('#edit-civicrm-1-activity-1-activity-duration').val(duration);
      }

//Dynamic Your Organization / School fields
var field1 = '.form-item-civicrm-2-contact-1-contact-existing';
var fieldset = '#edit-civicrm-2-contact-1-fieldset-fieldset';
var description = '#edit-civicrm-2-contact-1-contact-existing--description';
$(`${field1}`).hide();
$('#existing-org').on('click', function (e) {
  e.preventDefault();
  $(`${field1}`).show();
  $(`${fieldset}`).hide();
});

$('#new-org-block').on('click', function (e) {
    e.preventDefault();
    $(`${fieldset}`).show();
    $(`${field1}`).hide();
});
 
      // let optional_addons_select = document.getElementById(`${drupalSettings.ped_online_course_booking_update.optional_addons_select}`);
      // let option = optional_addons_select.options[1].value;
      // $(`#${drupalSettings.ped_online_course_booking_update.optional_addons_select} option`).each(function() {
      //   if ($(this).val() != option && $(this).val() != '') {
      //     $(this).hide();
      //   }
      // });
      // $(`#${drupalSettings.ped_online_course_booking_update.high_school_checkbox}`).click(function () {
      //   if ($(this).is(":checked")) {
      //     $(`#${drupalSettings.ped_online_course_booking_update.optional_addons_select} option`).each(function() {
      //       if ($(this).val() == option) {
      //         $(this).hide();
      //       }
      //       else {
      //         $(this).show();
      //       }
      //     });
      //   }
      //   else {
      //     $(`#${drupalSettings.ped_online_course_booking_update.optional_addons_select} option`).each(function() {
      //       if ($(this).val() != option && $(this).val() != '') {
      //         $(this).hide();
      //       }
      //       else {
      //         $(this).show();
      //       }
      //     });
      //   }
      // });
    });
  })(jQuery, Drupal, drupalSettings);
  
