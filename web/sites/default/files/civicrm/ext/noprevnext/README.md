# noprevnext

This turns off the prevnext cache (e.g. used when paging through search results) and the ACL contact cache, and sets the setting for the smart group contact cache lifetime to 0 (Administer - Customize - Search Preferences).

The motivation is that searches that hang around in the prevnext cache will end up repopulating the ACL contact cache with stale data when rerun, leading to ACLs that allow/disallow access incorrectly until a full cache clear is done or until the prevnext cache expires which can be up to 2 days.

The downside is that without the caches it hurts performance of the site.

The extension is licensed under [AGPL-3.0](LICENSE.txt).

## Requirements

* PHP v7.3+
* CiviCRM 5.37+ (but should work in earlier)

## Installation (Web UI)

Learn more about installing CiviCRM extensions in the [CiviCRM Sysadmin Guide](https://docs.civicrm.org/sysadmin/en/latest/customize/extensions/).

## Installation (CLI, Zip)

Sysadmins and developers may download the `.zip` file for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
cd <extension-dir>
cv dl noprevnext@https://github.com/FIXME/noprevnext/archive/master.zip
```

## Installation (CLI, Git)

Sysadmins and developers may clone the [Git](https://en.wikipedia.org/wiki/Git) repo for this extension and
install it with the command-line tool [cv](https://github.com/civicrm/cv).

```bash
git clone https://github.com/FIXME/noprevnext.git
cv en noprevnext
```

## Getting Started

(* FIXME: Where would a new user navigate to get started? What changes would they see? *)

## Known Issues

(* FIXME *)
