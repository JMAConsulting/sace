(function ($, Drupal, drupalSettings) {
    $(document).ready(function () {
        let searchParams = new URLSearchParams(window.location.search);
        //Dynamic Your Organization / School fields
        if (searchParams.get('booking_type') == 196 || searchParams.get('booking_type') == 199) {
            field = '.form-item-civicrm-2-contact-1-contact-existing';  //request form
            fieldset = '#edit-your-organization-or-school';
            description = '#edit-civicrm-2-contact-1-contact-existing--description';

            $(`${fieldset}`).hide();
            $(`${field} ${description}`).html('If you cannot find your organization, please click <a id="new-org" href=#> here</a> to add one.')
            $('#new-org').on('click', function (e) {
                e.preventDefault();
                $(fieldset).show();
                $(field).hide();
                
            });
        }
    });
})(jQuery, Drupal);