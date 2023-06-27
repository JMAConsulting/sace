(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
//console.log(`${drupalSettings.ped_online_course_booking_update.start_date}`);
      $(`${drupalSettings.ped_online_course_booking_update.start_date}`).on('change', function () {
        var end = $(`${drupalSettings.ped_online_course_booking_update.end_date}`).val();
        if (end && $(this).val()) {
          setDuration($(this).val(), end);
        }
      });
  
      $(`${drupalSettings.ped_online_course_booking_update.end_date}`).on('change', function () {
        var start = $(`${drupalSettings.ped_online_course_booking_update.start_date}`).val();
        if (start && $(this).val()) {
          setDuration(start, $(this).val());
        }
      });
  
      function setDuration(start, end) {
        start = new Date(start);
        end = new Date(end);
        if (start > end) {
          alert('Start Date cannot be after the End Date');
          $(`${drupalSettings.ped_online_course_booking_update.end_date}`).val('');
          $('#edit-civicrm-1-activity-1-activity-duration').val('');
          return;
        }
  
        $('#edit-civicrm-1-activity-1-activity-duration').val((end.getTime() - start.getTime()) / (1000 * 60));
      }
  
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
  
