jQuery(document).ready(function ($) {
  $("#edit-civicrm-2-contact-1-contact-existing").change(function () {
    toggleFieldVisibility();

    $("#edit-flag-markup").find("svg.saceflag").remove();

    if (!isNaN($("#edit-civicrm-2-contact-1-contact-existing").val())) {
      CRM.api4("Activity", "get", {
        select: ["a_colours.colour_hex", "activity_type_id:label"],
        join: [
          [
            "Saceflags AS saceflags",
            "INNER",
            ["activity_type_id", "=", "saceflags.activity_type_id"],
          ],
          [
            "AppointmentColours AS a_colours",
            "LEFT",
            ["activity_type_id", "=", "a_colours.activity_type_id"],
          ],
        ],
        where: [
          ["saceflags.used_for_flagging_contacts", "=", true],
          ["status_id", "!=", 2],
          ["status_id", "!=", 19],
          [
            "target_contact_id",
            "=",
            $("#edit-civicrm-2-contact-1-contact-existing").val(),
          ],
        ],
        limit: 25,
      }).then(
        function (activities) {
          let flagContainer = $("#edit-flag-markup");

          activities.forEach(function (activity) {
            let flag_html = "";

            // Construct the flag HTML
            flag_html += '<i class="fa fa-flag saceflag"';
            if (activity["a_colours.colour_hex"]) {
              flag_html +=
                ' flag-colour="#' + activity["a_colours.colour_hex"] + '"';
              flag_html +=
                ' style="color:' + activity["a_colours.colour_hex"] + ';"';
            }
            flag_html +=
              ' alt="' + (activity["activity_type_id:label"] || "") + '"';
            flag_html +=
              ' title="' + (activity["activity_type_id:label"] || "") + '">';
            flag_html += "&nbsp;</i>";

            flagContainer.append(flag_html);
          });
        },
        function (failure) {
          console.log("Failure: " + failure);
        }
      );
    }
  });
  // Hide new client option unless selected in autocomplete
  function toggleFieldVisibility() {
    var selectValue = $(".token-input-token p").text();
    if (selectValue === "+ Create new +") {
      $("#edit-flexbox-01").show();
    } else {
      $("#edit-flexbox-01").hide();
    }
  }
});
