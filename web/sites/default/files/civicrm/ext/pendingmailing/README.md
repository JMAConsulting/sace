# pendingmailing

When is the mailing going to start? Is it stuck? This extension will display a
warning message on the "Find Mailings" and Find SMS screens when there is a
mailing pending. It will display the last successful run time and, if the user
has the permissions to do so, will let them run the mailing immediately.

![Screenshot](/images/screenshot.png)

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.0+
* CiviCRM 5.latest

## Installation

Install as a regular CiviCRM extension.

## Known Issues

* The extension always says "it will run in the next 15 minutes", because that's what I use as a default cron interval.
* It would be nice to let non-admins run the mailing (ex: create a page that calls Job.process_mailing, accessible to those with 'view all contacts'?).

# Support

This extensions has been developed and is supported by [Coop Symbiotic](https://www.symbiotic.coop).
