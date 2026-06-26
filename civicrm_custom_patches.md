### CiviCRM Core Custom Patches

### CiviCRM Extension Custom Patches

1. IatsPayments [#48ca75a3d447302c40e1ee8444571111b7c91e1a](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/48ca75a3d447302c40e1ee8444571111b7c91e1a) - Fix Filtering of Ipv4 Addresses in FAPs Payment Processor
2. IatsPayments [#fa0be02eac](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/fa0be02eac) - Apply patch to allow updating of credit card
details with First Pay Credit Card Payment Processor
3. IatsPayments [#7d55f5bf75f5b0ff5a0f2f01ad48a18db1812ce5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/7d55f5bf75f5b0ff5a0f2f01ad48a18db1812ce5) Add in missing function credentials
3. IatsPayments [#86fb2260f2747c4a72328571bf5653a9608b60ee](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/86fb2260f2747c4a72328571bf5653a9608b60ee) Fix undefined class E
4. IatsPayments [#49a881cec4b72b1691836edf4df9142589fc4558](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/49a881cec4b72b1691836edf4df9142589fc4558) Fix applying patch to support updating of card details in the back office
5. IatsPayments [#c9e8db49841bdd3b60229a446d6cfe6b04554702](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/c9e8db49841bdd3b60229a446d6cfe6b04554702) Resolve IATS FAPS notices as per upstream PR
6. IatsPayments [#2f0a483e37dff22a754ae12cd21eaf4d203013c5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/2f0a483e37dff22a754ae12cd21eaf4d203013c5) Update Faps utf8 encoding to match upstream PR
7. IatsPayments [#403347846e2123ad1496c8ddd15b08170e8d7fa5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/403347846e2123ad1496c8ddd15b08170e8d7fa5) Switched country and state to use two-letter notation
8. ActivityIcal [#7ca595d3314a14b52531fc6f5e1d7762223c9acd](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/7ca595d3314a14b52531fc6f5e1d7762223c9acd) Re-apply necessary patches for SACE
9. ActivityIcal [#47aa3cde805488e446937d177a561f144b5c2433](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/47aa3cde805488e446937d177a561f144b5c2433) Remove activity status filtering from ICAL feed
10. ActivityIcal [#f937fa177cca3b381b32fffbfd6251ce16d9a452](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/f937fa177cca3b381b32fffbfd6251ce16d9a452) Remove some uncessary require onces

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
