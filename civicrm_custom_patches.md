### CiviCRM Core Custom Patches

### CiviCRM Extension Custom Patches

1. IatsPayments [#48ca75a3d447302c40e1ee8444571111b7c91e1a](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/48ca75a3d447302c40e1ee8444571111b7c91e1a) - Fix Filtering of Ipv4 Addresses in FAPs Payment Processor
2. IatsPayments [#33791bf83](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/33791bf8364bf679d52cb2706337520d5b64f8e4) - Apply patch to allow updating of credit card details with First Pay Credit Card Payment Processor
3. IatsPayments [#f7f2f0a5b](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/f7f2f0a5b) - Apply patch to fix missing function credentials from previous patch
3. IatsPayments [#42f8bf0a5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/42f8bf0a5) - Apply patch to fix unsetting of cryptogram

**NOTE: In future, please update this file with the commit link whenever a new core or extension customisation is added.**

### Recipe to apply CiviCRM Extension customisations
1. Update CiviCRM extension files and commit
2. Then checked the commit link patch, if already merged/included in the current ext release.
3. If not then cherry-pick the commit hash mentioned above.
4. If the patch is included in the current release, ignore and go to next commit, repeat step 2 and 3.
5. Repeat the same for other commits until you cherry-picked all the commits.

CLI:
```sh
$ git checkout origin/master -b civicrm_ext_upgrade
# update the CiviCRM core files
$ git add wp-content/uploads/civicrm/ext && git commit  -m "CiviCRM extension upgrade - Mosaico, CiviVolunteer" wp-content/uploads/civicrm/ext
$ git cherry-pick 35aa73ec # lets say 35aa73ec is the hashcode of the commit that contains one of the civicrm core customization changes
# repeat step 2-5 until all the commits are cherry-picked
$ git push origin civicrm_ext_upgrade
## submit Merge Request
```

