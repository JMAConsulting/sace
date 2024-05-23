{literal}
<script type="text/javascript">
  CRM.$(function($) {
    // Add flag field to form
    $('.form-layout-compressed tr:last').after(`
      <tr id="saceflags-tr">
        <td class='label'><label for="saceflags">Used for flagging contacts?</label></td>
        <td>{/literal}{$form.saceflags.html}{literal}</td>
      </tr>
    `);
    var isFlag = '{/literal}{$used_for_flagging_contacts}{literal}';
    $('#saceflags').val(isFlag);
  });
</script>
{/literal}