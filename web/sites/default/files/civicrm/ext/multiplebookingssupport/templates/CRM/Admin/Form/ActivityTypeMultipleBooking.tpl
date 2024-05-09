<table>
  <tbody>
    <tr class="crm-admin-options-form-block-is_multiple_booking">
      <td class="label">{$form.is_multiple_booking.label}</td>
      <td>{$form.is_multiple_booking.html}</td>
    </tr>
    <tr class="crm-admin-options-form-block-booking_calendar_display">
      <td class="label">{$form.booking_calendar_display.label}</td>
      <td>{$form.booking_calendar_display.html}</td>
    </tr>
  </tbody>
</table>
{literal}
<script type="text/javascript">
  (function($) {
    $(document).ready(function() {
      let is_active = $('.crm-admin-options-form-block-is_active');
      let multiple_booking = $('.crm-admin-options-form-block-is_multiple_booking');
      let booking_calendar_display = $('.crm-admin-options-form-block-booking_calendar_display');
      multiple_booking.appendTo(is_active.parent());
      booking_calendar_display.appendTo(is_active.parent());
    });
  })(CRM.$)
</script>
{/literal}

