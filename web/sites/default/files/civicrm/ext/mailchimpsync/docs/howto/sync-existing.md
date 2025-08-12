# Sync an existing list or group

If you already have a CiviCRM group and a Mailchimp audience that you'd
like this extension to keep in sync, this page is for you.

## First: Backup

Backup your Mailchimp audience (export all via Mailchimp's website) and your
CiviCRM database before you begin. This extension deals with bulk updates to
data which can be costly if there's a mistake by you or a bug in the software.


## Which system has the correct data?

With two systems storing the same data (who is on a list), changes can happen at either end. It’s important to have an understanding of how the two systems get brought and kept in sync.

If you know that one system has the correct list of subscribers you can set up your sync connection with a "trust override", selecting either CiviCRM or Mailchimp as the authoritative system. If you chose CiviCRM, then whenever the two systems differ for a contact, Civi will win and will update Mailchimp accordingly **on the fist sync**. Trust overrides are temporary and once completed it will re-set to the normal incremental sync algorithm (read on).

Mailchimp records the last date that a member was updated. CiviCRM keeps a record of when someone was added to (or removed from) a group.

In a nutshell, this extension compares these two dates and whichever system has the later date is considered to be the authoritative source of information.

However, please note that mailchimp will update that date when any update (E.g. a name change) is made to the contact, so the date at the Mailchimp end might not be faithful to the date the person subscribed. MailchimpSync includes some code to help minimise these situations, but the nature of them means there could still be edge cases.

Simple example:

|Who          | Mailchimp            | CiviCRM          | Outcome                         |
| ----------- | -------------------- | ---------------- | ------------------------------- |
|Wilma        | subscribed 1 Jan     | added 5 Jan      | stays subscribed                |
|Barney       | unsubscribed 2 Jan   | added 1 Jan      | gets removed from CiviCRM group |
|Fred         | subscribed 3 Jan     | removed 4 Jan    | gets removed from Mailchimp |
|Betty        | (does not exist)     | added 4 Jan      | gets added to Mailchimp |
|Pebbles      | added 1 Jan          | never in group   | gets added to CiviCRM |

!!! important
    To be clear: this means that if you have removed people from the CiviCRM group then the sync will remove them from Mailchimp too (unless they were added at Mailchimp since this removal).

## Set up the sync connection

Once you're confident (and you've made your backups!) you can add the sync connection by going to **Administer » System Settings » Mailchimp Sync**. Which looks like this:

![screenshot](../images/config-tutorial-complete.png)

From here you can

- Add a new Mailchimp account, if needed - most organisations only use one account, then
- **Add new subscription sync**

The next screen shows all your CiviCRM mailing groups and your Mailchimp audiences, so you can just select the correct ones, then ensure the webhooks are properly set up before pressing **Save**.

You can set the trust overrride if you want this type of initial sync instead of the usual "who updated it last" algorithm.

That's it! You can now wait for the sync. For the impatient, see [How to manually start a sync](../howto/run-sync.md).

## Trust override: if you want to

