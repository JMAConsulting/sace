# Titanics: Unsyncable situations

This page serves as a discussion of things that can go wrong and show as
"23 cannot sync" on the status page. Clicking on that link will show you details about those contacts.

- Some are inevitable because of the differences between two systems.
- Some might have sensible resolutions that are not implemented yet.
- Some might depend on the organisation's policy decisions.

This page lists situations and how they might arise and be treated by the
current codebase. Please use the [Issue
Queue](https://lab.civicrm.org/extensions/mailchimpsync/issues) as the
place to discuss suggested changes or mitigations. If what's written here
is factually incorrect, please create an issue (and a Merge Request if you
are able) for that, too.

## Forcing a re-sync {: #resync }

You may change data to mitigate problems, but the incremental sync runs might not pick up these changes. This is because they only look to the date someone was added/removed from a group when deciding who to sync.

In these situations you need to [force a re-sync](../howto/sync-single.md).

## Cleaned at Mailchimp

> "Cleaned" contacts cannot be resubscribed.

When Mailchimp finds a hard bounce or knows that an email address is
suspect (by it's own opaque algorithms/lists) it will be 'cleaned', which
means it's removed but remembered as removed. You can't resubscribe
a contact whose email has been cleaned.

If we have someone in CiviCRM with that email added to the group, then
their sync status will marked as "fail"; it will not attempt to subscribe
them because it knows it won't work.

!!! tip "Suggestion"
    If this is the only email address you have for the person, and you trust Mailchimp that it is wrong (Mailchimp also rejects valid emails if they look generic, or test-like, e.g. it bans @mailinator.com addresses), then you should delete their email address and remove them from the group.


## In CiviCRM group, but email is on hold (or no email!)

> This contact's email is on hold in CiviCRM so we're not going to subscribe them.

Mailchimp Sync cannot subscribe someone without an email. Well, duh. And it won't choose an email that is 'on hold' in CiviCRM for subscribing new people.

!!! tip "Suggestion"
    If that's the only email you've got and you believe it's duff, best thing to do is remove them from the group.

If you come across a valid email for them, e.g. you decide to un-hold their email, or they have another email, or they give you another email (etc.), then you can add that and [force a re-sync](../howto/sync-single.md).

## One contact, two emails: one subscribed and one unsubscribed at Mailchimp

>  This CiviCRM contact has 2 emails. This one is unsubscribed at Mailchimp, but the other - wilma@example.com are subscribed. This shows as a fail here but it's fine that we're ignoring it.

A Contact in Mailchimp is defined by their email address (crudely speaking). So two separate email addresses are two separate contacts. However CiviCRM allows contacts to have multiple email addresses.

This error will show on the email address that is unsubscribed at Mailchimp. You don't need to do anything; the sync should work for the other email address. But it exists as a "fail" because we have to track all the contacts at both ends, and we can't do anything with this one.

!!! tip "Suggestion"
    Do nothing.

## Mailchimp update error

> Invalid Resource: Your merge fields were invalid.
> `[{"field":"MMERGE3","message":"Please enter a complete address"}]`

This will occur if the merge field settings at Mailchimp don't match what we're sending. A tpical whoopsie is that someone ticks "Required" against a merge field in Mailchimp (e.g. they wanted it to be required for a Mailchimp signup form) without realising that this stipulation also applies to the API use. Since this extension (by default) only deals with subscribed/unsubscribed (and FNAME, LNAME, if changed), the update will fail because it's not supplying a required field.

!!! info
    This should [no longer happen](https://lab.civicrm.org/extensions/mailchimpsync/issues/5). But if you have any left over from an earlier version, you will then need to [force a re-sync](../howto/sync-single.md) for that contact.

