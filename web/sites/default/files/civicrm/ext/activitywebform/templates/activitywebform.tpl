{literal}
  <script type="text/javascript">
    CRM.$(function($) {
    // Add flag field to form
    $('.form-layout-compressed tr:last').after(`
      <tr id="activity_webform-tr">
        <td class='label'><label for="activity_webform">Add Activity Type to Webform(s)</label></td>
        <td>{/literal}{$form.activity_webform.html}{literal}</td>
      </tr>
    `);
    $('#activity_webform').select2();
  });
  </script>
{/literal}
