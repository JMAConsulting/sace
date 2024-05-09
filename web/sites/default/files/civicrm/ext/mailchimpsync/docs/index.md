# Mailchimp Sync

This extension keeps Mailchimp Audience(s) in sync with CiviCRM mailing groups.

## Overview of features

- Keep your Mailchimp Audiences/Lists in sync with CiviCRM groups:

    - Being subscribed at Mailchimp means being added to the group in
      CiviCRM; being unsubscribed at Mailchimp means being removed from
      the group in CiviCRM.

    - Two-way sync: subscription or group changes can be made at either
      end.

    - Suitable for big lists (e.g. has run happily with 250,000 contacts)
      thanks to an incremental sync routine that checks for recent
      changes rather than syncing the whole list.

- Link Mailchimp "Interests" with CiviCRM groups.

    - It's cheaper in Mailchimp to have one audience and to segment by
      using "Interest groups" and "Interests". This extension allows you
      to link these interest groups with CiviCRM groups.

    - Two-way sync: changes can be made at either end.

- Pump other data up to Mailchimp so you can segment or include content in
  merge tags.

    - You could send data like Membership end date, date of last donation,
      postal address.

    - You can develop your own extensions to feed Mailchimp with other
      information from CiviCRM using sync hooks.

    - One-way sync: changes made at Mailchimp will never migrate into
      CiviCRM and will be overwritten.

- Supports multiple Mailchimp Accounts.

## Health warning

While these goals sound simple, both services (Mailchimp and CiviCRM) work in
quite different ways and there can be a lot of data. These facts make the
process difficult and give birth to a host of complex situations that can
prevent things being in sync.

Certain decisions have been made, based on a fair bit of experience, to
make things as efficient and reliable as possible, but there are still
limitations.


## If you can, don't use Mailchimp!

In working with the API and the documentation over the last 8 years or so I've found several bugs, reported them, had them acknowledged with a promise of passing it on, yet nothing happens. They've also changed things without publishing the changes. Mailchimp's [API Reference](https://developer.mailchimp.com/documentation/mailchimp/reference/overview/) does not give a complete picture of the service. All this makes integration extensions brittle. See [Mailchimp issues](discussion/mailchimp-issues.md)

Seems the customers have not found a way to apply enough pressure collectively to improve their product. Humph.

All this is a good reason to persuade people not to use Mailchimp at all if feasible. CiviCRM + Mosaico gives a descent experience for 80% of use cases and gives you more segmentation flexibility and usually much lower cost.


## Why not one of the other Mailchimp extensions?

Read the [reasons for writing a new extension](discussion/faq.md#why-new).

## How do I get started?

You backup CiviCRM and Mailchimp, then see [How to Install](howto/install.md) (hint: like any other extension), then head over to the [tutorial](tutorial.md).
