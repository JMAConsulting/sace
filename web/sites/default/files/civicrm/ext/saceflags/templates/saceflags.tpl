{* <tr id="saceflags-tr">
  <td class='label'><label for="saceflags">Test Field</label></td>
  <td>{$form.saceflags.html}</td>
</tr> *}
{literal}
  <script type="text/javascript">
    CRM.$(function($) {
        // Add flag field to form
      $('.form-layout-compressed tr:last').after(`
        <tr id="flag-tr">
          <td class='label'><label for="saceflags">Used for flagging contacts?</label></td>
          <td>{/literal}{$form.saceflags.html}{literal}</td>
        </tr>
      `);

    });
  </script>
{/literal}