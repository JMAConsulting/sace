(function ($){    
    var viewHtml = '';
    var counsellorName = '';
    $( '#edit-civicrm-3-contact-1-contact-existing').on( "change", function() {
        viewHtml = '';
        counsellorName = $("option:selected", this).text();
        var counsellorId = this.value;
        $.ajax({
            url: '/views/ajax',
            type: 'POST',
            data: {
                view_name: 'counsellor_calendar',
                view_display_id: 'page_1',
                view_args: counsellorId,
            },
            dataType: 'json',
            success: function (response) {
                // Add any scripts
                if (response[2].data !== undefined) {
                    response[2].data.forEach(script => {
                        viewHtml.concat(`<script src="${script.src}"></script>`);
                    });
                }
                // Add the HTML
                if (response[3].data !== undefined) {
                    viewHtml.concat(response[3].data);
                }
            }
        });
    });
    
    $( "#open-calendar" ).on( "click", function() {
        if (viewHtml) {
            const jsFrame = new JSFrame({fixed: false});
            const width = Math.round(window.innerWidth * 0.8);
            const height = Math.round(window.innerHeight * 0.8);
            const calendar = jsFrame.create({
                title: (counsellorName ? counsellorName+"'s": "Counsellor")+" Calendar",
                width: width, height: height,
                html: viewHtml,
            });
            const x = window.innerWidth / 2;
            const y = window.innerHeight / 2;
            calendar.setPosition(x, y, 'CENTER_CENTER');
            
        

            calendar.showModal();
            $("[id^='windowManager']").css("position", "fixed")
        }
    });
})(jQuery, Drupal);