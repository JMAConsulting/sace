jQuery(document).ready(function ($) {
  $('input[id*="edit-select-staff-"]').on('click', function(e) {
    CRM.api3('UFMatch', 'get', {
      "uf_id": $(this).val(),
      "return": ["contact_id.first_name","contact_id.last_name", "uf_name"]
    }).then(function(result) {
     $('#edit-civicrm-3-contact-1-contact-first-name').val(result.values[result.id]['contact_id.first_name']);
     $('#edit-civicrm-3-contact-1-contact-last-name').val(result.values[result.id]['contact_id.last_name']);
     $('#edit-civicrm-3-contact-1-email-email').val(result.values[result.id]['uf_name']);
    });
  });
});
