# Syncing tags and other data.

The normal sync process looks at the membership groups and -if configured-
interest groups, and does a two-way sync on these. However you can also do
a "data sync" by passing the `with_data=1` parameter to the
`Mailchimpsync.fetcfetchandreconcile` API call.

A data sync allows you to send data from CiviCRM to Mailchimp, but not the
other way; it's 1-way only.

Included with this extension is a tag sync: all CiviCRM tags get added to all
members at Mailchimp, with the prefix "CiviCRM:".

So if you have a contact tagged "Volunteer" they'll end up with the tag
"CiviCRM: Volunteer" at Mailchimp. Remove the tag in CiviCRM and after the next
data sync the tag will be removed at Mailchimp. Remember this is a one-way
sync; you're not supposed to add/remove a CiviCRM tag at Mailchimp. (If you do,
it will stay as you set it at Mailchimp until the next data sync that needs to
include that contact, i.e. until you make a tags change in CiviCRM for that
contact. Confusing. Don't do it!)

The data sync only updates the tags registered in CiviCRM. It won't interfere
with any other Mailchimp tags that you may have been using.


## Syncing other data

You may have your own needs for more data in Mailchimp, e.g. membership end
date, or details about the last contribution etc. Or even some other data that
that you want to use to update a different tag at Mailchimp.

You can do this by writing your own extension and implementing a few *hooks*.
When the fetchandreconcile action is called `with_data`, the following hooks
are called:

1. `hook_mailchimpsync_data_updates_check_pre` This is called first and is an
   opportunity to do lookups that are cheaper to do en-masse. 

2. `hook_mailchimpsync_data_updates_check` - called once for every cache
   entry processed.

3. `hook_mailchimpsync_data_updates_check_post` - chance to clean up, if
   needed.


### How to implement the hooks.

These are Symfony hooks, so for example to listen for the first two:

```php
<?php
/**
 * Implements hook_civicrm_container
 *
 * We register 2 hooks to copy CiviCRM tags to Mailchimp.
 *
 * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
 */
function <yourext>_civicrm_container($container) {
  $container->findDefinition('dispatcher')
            ->addMethodCall('addListener', [
              'hook_mailchimpsync_data_updates_check_pre',
              '<your_own_function_for_the_pre_hook>'])
            ->addMethodCall('addListener', [
              'hook_mailchimpsync_data_updates_check',
              '<your_own_function_for_the_main_hook>']);
}
```

### The pre hook. `hook_mailchimpsync_data_updates_check_pre`

Please see the code in `mailchimpsync.php` for the function
`mailchimpsync__tags_sync_pre` which is the implementation of the tags
sync. The `$event->pre_data` array is there for extensions to cache data.
The tags sync uses the `mc_tags` key within that array as a container for
the things it needs, you should use something unique to your extension for
yours. The tag updates then stores a lookup of registered tags for
Contacts, and uses a single SQL query to generate a list of all tags on
all relevant contacts.

### The main in-loop hook. `hook_mailchimpsync_data_updates_check`

Please see the code in `mailchimpsync.php` for the function
`mailchimpsync__tags_sync` which is the implementation of the tags sync.

The `$event->pre_data` array populated in the previous hook is available,
and this hook would be typically implemented by...

- Unserialize the `$event->cache_entry->civicrm_data` and work with
  a unique key within that array, e.g. the tags sync uses `mcs_tags`.

- Figure out what the current data is for this contact. The tags sync is
  gets this from the `pre_data` made in the first hook.

- Using the current data and any metadata previously stored, determine if
  an update is required. The tags sync uses a `previous` key to store the
  last value, so it can compare this now. If an update is required...

- Update the cache entry's status to 'todo' if it would not have normally
  been included in the sync. (Contacts are only usually included if their
  groups have changed since the last sync.)

- Provide data under `mailchimp_updates` and/or `tag_updates` under a key
  in the unserialized `civicrm_data`. The tag sync will create an array
  within `$data['mcs_tags']['tag_updates']` like

   ```json
   [
     { name: "CiviCRM: Volunteer", status: "active" },
     { name: "CiviCRM: NGO", status: "inactive" },
     ...
   ]
   ```

   Things under `tag_updates` need to be in the correct Mailchimp API
   format for the `POST lists/<listId>/members/<memberId>/tags` call.

   Things under `mailchimp_updates` need to be in the correct Mailchimp
   API format for the `POST/PATCH/PUT lists/<listId>/members/<memberId>`
   call, e.g. `{ merge_fields: { MYMERGE: 'my data' }}`

- Update `$event->needs_saving` if the cache entry was changed at all
  (regardless whether that change requires updates).


### The post/cleanup hook `hook_mailchimpsync_data_updates_check_post`

You might use this for example, to delete a table created in the `_pre`
hook.

### Use the Source

As well as reading through the code for the tag sync, you might like to
read the docblock comments in
`CRM_Mailchimpsync_Audience::checkForDataUpdates` and
`CRM_Mailchimpsync_Audience::reconcileExtraData` might also be useful
reading.


### Which contacts are included?

These hooks are called for `civicrm_mailchimpsync_cache` IDs that are
either subscribed at Mailchimp, or already identified as needing an
update. Please read the code comments inside the
`CRM_Mailchimpsync_Audience::reconcileExtraData` method which explain how
and why this is a superset of those that might need updating.

