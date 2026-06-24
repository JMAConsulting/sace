# Installation

You can install this extension in any of the normal ways.

## Method 1

Download the [latest release](https://lab.civicrm.org/extensions/mailchimpsync/-/releases) and unzip it into your extensions directory.

Then navigate to **CiviCRM » Administer » System Settings » Extensions** and click **Install**.

## Method 2

Developers can clone the Git repo into your extensions directory, grab the links from <https://lab.civicrm.org/extensions/mailchimpsync>

## Uninstall/remove

In the normal way.

## Consider indexing your group subscription history table.

The following SQL will add an index to a core CiviCRM table  which will speed up operations on sites with a lot of contacts. It should not have any detrimental effect on your site.

```sql
ALTER TABLE `civicrm_subscription_history`
ADD INDEX IF NOT EXISTS `ui_group_id_date` (group_id, `date`);
```

Should you wish to undo this for whatever reason, use this:
```sql
ALTER TABLE `civicrm_subscription_history`
DROP INDEX IF NOT EXISTS `ui_group_id_date`;
```
