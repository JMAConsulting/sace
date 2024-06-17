{literal}
  <script type="text/javascript">
    CRM.$(function($) {
    // Add flag field to form
    $('.form-layout-compressed tr:last').after(`
      <tr id="user_teams-tr">
        <td class='label'><label for="user_teams">Add Activity Type to User Team(s)</label></td>
        <td>{/literal}{$form.user_teams.html}{literal}</td>
      </tr>
    `);
    $('#user_teams').select2();
  });
  </script>
{/literal}