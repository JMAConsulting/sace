# How to force a sync

There is not (yet) a way to force a real-time sync for a contact. However,
you can force-include contacts in the next sync run.

## Option 1: A single audience/group for a single contact

Use the **Mailings » Mailchimp Sync Stats** page's 'cache browser' to find
the contact in the appropriate audience/group. At the top left of their
record it will say, e.g. **ok** or **fail** as their sync status, and next
to that will be a **Schedule Resync** link (this does not show up if the
status is 'live' as that could interfere with a running sync process, and
it does not show up if it is already 'todo' as there would be no point).

Click that link. After a pause it should now show as **todo**. Wait for
the next sync to run.

## Option 2: All audience/groups for one or more contacts

Search for and select the contacts you want to include in the next sync,
and use the "Include in next Mailchimp Sync run" search task. Confirm the
action, and then wait for the next sync run to complete.
