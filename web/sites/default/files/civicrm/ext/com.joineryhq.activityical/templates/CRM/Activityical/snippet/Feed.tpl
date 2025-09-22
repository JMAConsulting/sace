BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Joinery//NONSGML CiviCRM activities iCalendar feed//EN
X-WR-TIMEZONE:{$timezone}
METHOD:PUBLISH
{foreach from=$timezones item=tzItem}
BEGIN:VTIMEZONE
TZID:{$tzItem.id}
{foreach from=$tzItem.transitions item=tzTr}
BEGIN:{$tzTr.type}
TZOFFSETFROM:{$tzTr.offset_from}
TZOFFSETTO:{$tzTr.offset_to}
TZNAME:{$tzTr.abbr}
{if $tzTr.dtstart}
DTSTART:{$tzTr.dtstart|crmICalDate}
{/if}
END:{$tzTr.type}
{/foreach}
END:VTIMEZONE
{/foreach}
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
DTSTAMP;TZID={$timezone}:{$smarty.now|date_format:'%Y-%m-%d %H:%M:%S'|crmICalDate}
{if $activity.activity_date_time}
DTSTART;TZID={$timezone}:{$activity.activity_date_time|crmICalDate}
{/if}
{if $activity.activity_duration}
DURATION:PT{$activity.activity_duration}M
{else}
DTEND;TZID={$timezone}:{$activity.activity_date_time|crmICalDate}
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

