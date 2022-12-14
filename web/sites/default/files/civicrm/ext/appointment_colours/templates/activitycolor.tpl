{* <tr id="activitycolor-tr">
  <td class='label'><label for="activitycolor">Test Field</label></td>
  <td>{$form.activitycolor.html}</td>
</tr> *}
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
      $('.form-layout-compressed tr:last').after(`
        <tr id="colour-tr">
          <td class='label'><label for="activitycolor   ">Activity Color</label></td>
          <td>{/literal}{$form.activitycolor.html}{literal}</td>
        </tr>
      `);
      //$("#activitycolor-tr").appendTo(".form-layout-compressed")
    });
  </script>
{/literal}