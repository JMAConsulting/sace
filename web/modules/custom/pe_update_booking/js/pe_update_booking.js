jQuery(document).ready(function ($, settings) {
  Drupal.behaviors.pe_update_booking = {
    attach: function(context, settings) {
      var Presentation_Topics = settings.pe_update_booking.Presentation_Topics;
      console.log(Presentation_Topics);
    }
  };
  if (!$("#edit-civicrm-1-activity-1-cg2-custom-40-customsomethingdifferent").is(
      ":checked")) {

    $("#edit-civicrm-1-activity-1-cg2-custom-41").hide();
    $('label[for="edit-civicrm-1-activity-1-cg2-custom-41"]').hide();
  }

  $("#edit-civicrm-1-activity-1-cg2-custom-40-customsomethingdifferent").click(
    function () {
      if ($(this).is(":checked")) {
        $("#edit-civicrm-1-activity-1-cg2-custom-41").show();
        $('label[for="edit-civicrm-1-activity-1-cg2-custom-41"]').show();
      } else {
        $("#edit-civicrm-1-activity-1-cg2-custom-41").hide();
        $('label[for="edit-civicrm-1-activity-1-cg2-custom-41"]').hide();
      }
    }
  );
});
