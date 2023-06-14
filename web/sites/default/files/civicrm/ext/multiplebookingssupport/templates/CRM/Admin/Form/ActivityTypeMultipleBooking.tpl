<table>
  <tbody>
    <tr class="crm-admin-options-form-block-is_multiple_booking">
      <td class="label">{$form.is_multiple_booking.label}</td>
      <td>{$form.is_multiple_booking.html}</td>
    </tr>
  </tbody>
</table>
{literal}
<script type="text/javascript">
  (function($) {
    $(document).ready(function() {
      var is_active = $('.crm-admin-options-form-block-is_active');
      var multiple_booking = $('.crm-admin-options-form-block-is_multiple_booking');
      multiple_booking.appendTo(is_active.parent());
    });
  })(CRM.$)
</script>
{/literal}

