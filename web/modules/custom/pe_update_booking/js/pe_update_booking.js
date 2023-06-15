jQuery(document).ready(function ($, settings) {  
  //Presentation Topic Custom/SOmething Different toggle text field
  let checkbox = "#edit-civicrm-1-activity-1-cg2-custom-40-customsomethingdifferent";
  let field = "#edit-civicrm-1-activity-1-cg2-custom-41";
  let lable = 'label[for="edit-civicrm-1-activity-1-cg2-custom-41"]';
  
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
});
