{literal}
  <script type="text/javascript">
    CRM.$(function($) {
    $('.form-layout tr:last').after(`
      <tr id="configuration_set-tr">
        <td class='label'><label for="configuration_set">Configuration Set</label></td>
        <td>{/literal}{$form.configuration_set.html}{literal}</td>
      </tr>
    `);
    $('#configuration_set').select2();
  });
  </script>
{/literal}