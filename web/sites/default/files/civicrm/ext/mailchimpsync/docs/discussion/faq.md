# Frequently Asked Questions

## Why can't I use Smart Groups?

We rely on the timestamp field from the subscription history table; this
does not exist for smart groups. Using Smart Groups would basically un-do
all the benefits of an incremental sync.

## I get "Your merge fields were invalid" errors {: #mergefields}

e.g. showing in the Cache Browser for a contact. This is probably because you've defined merge fields called `FNAME` and `LNAME` (correct) but you've defined them as *Required*.

!!!summary

    *Do not mark any merge fields as required.*

The problem can arise because you don't have a name in Civi for someone who is Added to your CiviCRM group; therefore the Mailchimp update fails because the name is required by Mailchimp. It *might* arise in relation to fname/lname (I’d need to check) because of the way the subscribe sync works, too, so best set those not-required.

Certainly if you have any other fields required then sync will fail as CiviCRM won't be able to provide that when subscribing people.


## Why make yet another Mailchimp sync extension? {: #why-new}

At the time of writing, there are currently three other Mailchimp sync extensions.

1. <https://civicrm.org/extensions/mailchimp-civicrm-integration>
    - most popular one. Maintained by Veda, although not very actively (last release 2017).
    - Having written the majority of this code when Mailchimp ditched v2 of their API, I'm aware of its limitations and I realised that a fresh start was required.
    - struggles with big lists and fails when data is changed at both ends between syncs; there's no true 2 way sync as you start from the assumption that either CiviCRM *or* Mailchimp is correct.
2. <https://civicrm.org/extensions/mailchimp-sync>
    - not updated since 2014.
    - Uses deprecated Mailchimp API, considered dead.
3. <https://civicrm.org/extensions/civimailchimp>
    - not updated since 2015.
    - Uses deprecated Mailchimp API, considered dead.

I needed a solution that was easier for staff to manage; experience taught me that it needed to be more robust especially around bulk updates at either end. Also, as the two systems don't match up - one's a circle and one's a square - there's always bits that get lost in translation. While these are not fun to deal with, this extension at least allows these to be inspected and understood.

Context: "I" above is Rich Lott / [Artful Robot](https://artfulrobot.uk) and this extension began life in November 2019.
