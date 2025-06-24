BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Joinery//NONSGML CiviCRM activities iCalendar feed//EN
METHOD:PUBLISH
BEGIN:VTIMEZONE
TZID:America/Edmonton
BEGIN:STANDARD
DTSTART:19700101T020000
TZOFFSETFROM:-0600
TZOFFSETTO:-0700
TZNAME:MST
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:19700101T020000
TZOFFSETFROM:-0700
TZOFFSETTO:-0600
TZNAME:MDT
END:DAYLIGHT
END:VTIMEZONE
{foreach from=$activities key=uid item=activity}
  BEGIN:VEVENT
  UID:activity-{$activity.id}-{$smarty.now|crmICalDate}@{$domain}
  SUMMARY:{$activity.activity_subject|crmICalText}
  {if $activity.description}
    DESCRIPTION:{$activity.description|crmICalText}
  {/if}
  {if $activity.activity_type}
    CATEGORIES:{$activity.activity_type|crmICalText}
  {/if}
  CALSCALE:GREGORIAN
  DTSTAMP;TZID=America/Edmonton:{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'|crmICalDate}
  {if $activity.activity_date_time}
    DTSTART;TZID=America/Edmonton:{$activity.activity_date_time|crmICalDate}
  {/if}
  {if $activity.activity_duration}
    DURATION:PT{$activity.activity_duration}M
  {else}
    DTEND;TZID=America/Edmonton:{$activity.activity_date_time|crmICalDate}
  {/if}
  {if $activity.activity_location}
    LOCATION:{$activity.activity_location|crmICalText}
  {elseif $activity.location}
    LOCATION:{$activity.location|crmICalText}
  {/if}
  {if $activity.contact_email}
    ORGANIZER:MAILTO:{$activity.contact_email|crmICalText}
  {/if}
  URL:{$activity.url}
  CONTACT;ALTREP={$base_url}/civicrm/contact/view?reset=1&cid={$activity.source_id}:{$activity.source_display_name}
  X-ALT-DESC;FMTTYPE=text/html:{$activity.description|activityicalHtml}
  END:VEVENT
{/foreach}
END:VCALENDAR
