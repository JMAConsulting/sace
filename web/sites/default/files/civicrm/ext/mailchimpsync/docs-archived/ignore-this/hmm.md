# Plans

Q: which records to process, and how?

Q: are all eventualities covered?

- contact added at MC                               changed
- contact unsubscribed at MC                        changed
- contact cleaned at MC                             changed
- contact archived at MC                            changed
- contact 'deleted' at MC                           ?
- contact interests changed at MC                   changed
- contact other data changed at MC                  changed
- contact added at CiviCRM                          date
- contact removed at CiviCRM                        date
- contact-group deleted at CiviCRM                  ?
- contact deleted at CiviCRM                        ?
- contact fully deleted at CiviCRM                  ?
- contact added to interest group at CiviCRM        ?
- contact removed to interest group at CiviCRM      ?
- contact interest group deletion at CiviCRM        ?
- contact other data changed at CiviCRM             ?


We can access our data live (ish).

By fetching since updated from Mailchimp we can update our cache table with most
of the info.
The things left unchecked are CiviCRM's updates.

Idea: have a "needs update" flag on the cache table; set on MC import; run
various other from civi to also set it.

- Copy the 'needs update on Mailchimp' group to it.
- Scan group subscription history for recent dates; add those.
- Scan contact created dates?
- Various hooks set this?
- option to force update of all contacts.

Process is to loop all needs_update records.

Each one may result in changes at civi or Mailchimp, maybe both?
Mailchimp changes should be batched; CiviCRM changes should be immediate.

Sync status:
- todo
- active
- ok
- mailchimp_updates_json
- batchID (once submitted)

Mailchimp batches
- batchID
- mailchimp batch code/link
- started
- completed

So the only process that can't be interrupted is the initial data fetch; we need
to know that that is complete. That process is:

1. fetch (recent) Mailchimp data for list (slow) - could be broken into jobs
2. removeInvalidContactIds
3. populateMissingContactIds (slowish)
4. createNewContactsFromMailchimp
5. addCiviOnly

There should be a lock on this audience during this process. At the end of the
process, the lock is removed. If any step fails, do not proceed to
reconciliation.


Loop all `todo`, fill up batch queue. If this is interrupted, that's fine, it
will restart.

Second process (or triggered during loop after N records added to batch queue)
creates batches and submits them.

Batches are polled; when one is complete sync status is set to OK for all its
records. If any are not found when checking Mailchimp but still exist in the
batches table, set their records to todo.

## Sync called on an audience (possibly with 'recent' set)

1. Lock audience to prevent another process starting a sync or processing
   records.

2. Gather data

3. If all gatherings completed, reconcile. This leads to immediate changes in
   CiviCRM and a list of changes for Mailchimp. Release lock. If gatherings did
   not complete OK, do NOT reconcile, just release lock. Somehow alert users to
   error.

4. (cron) Look for work in queue; create Mailchimp batches.

5. (cron) Check mailchimp batches; remove from queue when done.

## Locks and config

Use config?

```
config.lists.<listID>.lock = {
    queue_id, // 'mailchimpsync_' + <listID>
    since: <datetime>, // If recent sync
    jobs: [
        mailchimpFetch: { started: <datetime>, completed: null|<datetime>, stats }
        removeInvalidContactIds: ...
        populateMissingContactIds: ...
        createNewContactsFromMailchimp: ...
        addCiviOnly: ...
        reconcile,
    ],
}
```

Each list has it's own settings:
- subscriptionGroupId
- fetch:
    - lastFetchedAt: null|datetime
    - status: live|wait
    - startedAt: null|datetime
    - completedAt: null|datetime
    - updatedAt: null|datetime
    - log: []

## Q. how is a process restartable by cron?


Feels bit worrying putting fast changing data in a JSON structure, but maybe
that's OK.

## Edge cases

If you delete a contact, or delete it's subscription record (how is that
handled?) we'll have something at MC and nothing at Civi and this could lead to
reinstating a record.

This could be handled by a hook on delete that notes in the cache table that the
contact is `known_deleted` and therefore should be removed from Mailchimp lists
during the next sync.

## Thinking through rows in the cache table.

```
m_email    c_id  m_status        m_changed    (c_status) sync
---------- ----- --------------- ------------ ---------- -------
eg1        1     subscribed      today        A/R/-      todo
eg2        2     unsubscribed    yesterday    A/R/-      todo
eg3        3     cleaned         today        A/R/-      todo
eg4        4     pending         today        A/R/-      todo
eg5        5     transactional   today        A/R/-      todo
eg6        6     archived        today        A/R/-      todo
eg7        7     -               -            Added      todo
```

Queue runner uses `mcs_locks` setting or such to ensure that 2 runners don't
attack the same audience at once.

Queue runner (not the CiviCRM Queue) processes each row. The result of the
processing is:

- possible updates to CiviCRM; sync → ok
- possible updates to Mailchimp; sync → pending

Queue runner may have `max_processing_time` and exit after that time, leaving
some in `todo` state. We need a way to detect a crash, though.

Mailchimp updates.

-  table needs to know if it's been submitted/done.

- `mailchimpsync_updates` table. This table should use COMPRESSED
    - `ID`
    - `mailchimpsync_cache_id` FK (suppose it could be NULL)
    - `data` TEXT json
    - `batch_id` FK to Mailchimp batches
    - `completed` boolean

- Mailchimp batches table
    - `ID`
    - `batch_id`
    - `complete` (Stats)

- separate process (singleton) grabs 1000 of these that don't have `batch_id` and submits a
  batch and updates the `batch_id`.

- separate process polls MC for all live batches from our batches table. If any
  have completed, then update all the rows in mailchimp updates for that batch,
  setting completed=1. And update the batches row. And do a general SQL call on
  the cache table updating the status to `ok` if it was `pending` and there are
  no incomplete updates in the updates table.

- UI can show all operations:

   - For each audience it can count (pending/todo) and report this to show how
     out of sync it is(!)
   - It can also report on Mailchimp batch updates.
   - This page could refresh by ajax.




## Configuration

To set up sync connections between Mailchimp audiences/lists and CiviCRM
groups, please visit **Administer » System Status » Configure Mailchimp Sync**

After setting up the links between your CiviCRM subscription groups and
Mailchimp Lists/Audiences you will need to start an initial sync of all
contacts.

### Fetch and reconcile

The `Mailchimpsync.Fetchandreconcile` API job needs running regularly.

This should be called only as often as you want to be processing. If
you call it all the time it will run constantly and eat your server
resources for little gain. Hourly is probably reasonable.

By default this API action will try to process all lists within 5 minutes.
You can specify the `max_time` (in seconds) to suit your environment and
a specific `group_id` if you want to run it for a single subscription
group.

Behind the scenes, this API calls `Audience::fetchAndReconcile` for each
list/audience which deals with running everything in order.

## Understanding Mailchimp's data structures, intended use, API

Mailchimp seem to be strongly pushing the idea of one "Audience" (previously "List") for all subscribers and then segmenting according to various data for sending email "Campaigns". The UI (September 2019) refers to singluar "Audience" and will even hide the audience selector unless you have already created several. On a free account you're only allowed a single Audience.

> If you can, it's best to have one audience that you organize with tags, groups, or segments, rather than maintain multiple audiences in your account. Mailchimp treats all the audiences in your account separately, and billing is based on the total number of subscribed contacts across all your audiences. If you have duplicate contacts across audiences, having one audience could save you money. One audience is also easier to manage and keep clean.
>
> [Source: Mailchimp: Best practices](https://mailchimp.com/help/requirements-best-practices-audiences/)

The extension should support multiple audiences but users should keep in mind it's not "the mailchimp way", so should probably not use it like that.

Annoyingly, of course, this means that the unsubscribe link covers all purposes. e.g. if you have one audience and within it you have 'major donors' and 'daily newsletter' segments, a major donor who thinks they're unsubscribing from the daily newsletter removes themselves from the important major donors segment, too. Mailchimp provide their own forms for "updating preferences" but who among us, when faced with wanting to unsubscribe, fancies interacting with an unknown and probably awkward profile update form when there's an unsubscribe link right next to it?

Audience members are categorised as follows ([source Sep 2019](https://mailchimp.com/help/getting-started-audience/)):

> - **Subscribed contact** Someone who has opted in to receive your email marketing campaigns.
> - **Unsubscribed contact** Someone who was opted in to receive your email marketing campaigns, but isn't currently.
> - **Non-subscribed contact** Someone who has interacted with your online store, but hasn't opted in to receive your email marketing campaigns.

The API, however, returns one of these statuses

* **subscribed**
* **unsubscribed** 
   - if done by the member via a Mailchimp unsubscribe link you can't resubscribe them via API (but you can set "pending").
   - if done via the API, or the UI, you can resubscribe them. Mailchimp does not send a confirmation email or unsubscribe notice.
* **cleaned** (removed by Mailchimp due to bounces)
* **pending** - Mailchimp will send an email to them requiring them to confirm subscription.
* **transactional** ?? no reference to this in the API or other documenation, perhaps it means "non-subscribed contact"
* **archived** [documentation](https://mailchimp.com/help/archive-unarchive-contacts/). An archived contact is not billed-for and you can't interact with their data in any way except to un-archive them. If they were to resubscribe using a Mailchimp form they become un-archived. In the UI un-archiving restores the original status, but in the API there does not seem to be the equivalent method - you have to choose one of these.

## How to 'unsusbscribe'

If someone leaves the group in CiviCRM that syncs with an audience's membership, we have several options:

- delete the member. No! This makes it impossible for us to resubscribe them via the API at all.
- unsubscribe the member. This is reversible via API.
- archive the member. This is reversible.

What's the difference between unsubscribe and archive? Neither is billed. Unsubscribed represents the intent of the person better an archived contact that is un-archived (via UI) would become subscribed again. So it still looks sensible to use unsubscribe.

If the person uses a Mailchimp unsubscribe link, we need to remove the contact from our CiviCRM group. Note that we won't be able to resubscribe them directly from the API - the first call will fail and we need to detect this and try again with 'pending'. **Q. is there a way to detect before the fail?** (would solve issue of not getting feedback from bulk updates)

## Segmentation

Because of the focus on a single audience we need to handle not just the complete unsubscribe-from-everything situation, but also the "update preferences". This means we need a 2 way sync on something other than list membership.

Previous extensions have used Mailchimp's **Interest Groupings** for this (ambiguously called "Groups" in mailchimp). Mailchimp allows groupings of groups (Drupal users: think vocabularies of terms) that are binary yes-or-no for a contact. **This seems pretty sensible still.**

Since them Mailchimp has introduced **Tags**, but these are for "internal use" ([source](https://mailchimp.com/help/getting-started-tags/)); they're not editable by contacts themselves using Mailchimp preference forms. Therefore these do not seem like a sensible way to do most segmentation whereby people can 'unsubscribe' from a segment without unsubscribing from whole audience.

Previous extensions used CiviCRM groups mapped to interest groups for this. An
alternative is CiviCRM Tags. e.g. mirror Mailchimp's interests with tags (could
even be automated). But tags have no metadata and no history, so groups will
still be needed, especially for determining which source has most recently been
updated.

We ought to look at the group subscription history date in CiviCRM on a per
group basis and compare it against the lastest update date for the Mailchimp
contact.

There is still the case where:

1. Contact is in sync and is in subscription group (is subscribed in audience);
   and is in interest group A but not B.

2. Staff member adds them to group B in CiviCRM.

3. Contact updates via mailchimp UI interests, removing themself from group A.
   They do this before a sync has happened.

4. Mailchimp only gives us a last updated date for the contact, not per group.
   As here Mailchimp's last update will be later than our group updates, it will
   win and we will lose data.

   - Group A: Mailchimp's data wins: remove from CiviCRM group. This is fine.

   - Group B: Mailchimp's data wins: remove them from CiviCRM group!

(Note this can be avoided if 2, 3 are swapped in time.)

This can be minimised but not completely mitigated by frequent syncs. I think
this reduces the chance of it happening to 'not at all likely' but users would
need to understand the risk for their particular use.

## Smart and Hierarchical groups

Smart groups are not allowed to be used for subscriptions or interests because
there's no subscription history records for them, and we need to know when the
contact was added/removed from a group.

CiviCRM allows hierarchical groups. In summary the logic is "a contact is in a
group if it is included in any child (or grand child etc.) group".

At first I thought this would be a logical configuration for addressing interest
groups within a subscription group (audience).

However the way this is implemented opens up a lot of confusion. The sentence
stating the logic above is true for searches, but apparently not true if you
inspect a single contact's record - the parent group(s) are not listed, even if
you look under Smart Groups. It's also confusing because if you Remove your
contact from the parent group (by manually *adding* then *removing* them),
they're still considered to be in the group if they are in a child group; the
only way to remove someone from a parent group is to remove from all child
groups.

This is way too complex to use sensibly (consider case that a contact is
'Removed' in CiviCRM from main subscription group but is still Added to a child
group. The sync would see subscription history for the removal, but would have
to know to ignore that by doing a search on child groups).

Therefore this extension requires that subscription/interest groups are not
hierarchical.

## Comments - please use issue queue


I'd love to hear your thoughts.



11. We only look at group membership. We ignore the following list of things:

  * No bulk mail

  * Do not email

  * Deceased contacts

  * Deleted contacts

  * On hold email addresses

  These should be handled outside of the sync situation; e.g. remove people from group in the above situations. The sync code will, however, prefer contacts that the above does not apply to and should (optionially?) flag up this stuff if found.
