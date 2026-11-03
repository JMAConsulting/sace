### CiviCRM Core Custom Patches

1. civicrm-core [#36e4a13a9db34f85de628a356b96072e507b1cd7](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/36e4a13a9db34f85de628a356b96072e507b1cd7) Switch social sharing from LinkedIn to Instagram - applied automatically by composer-patches via `patches/civicrm-core-share-instagram.patch` (see `extra.patches` in composer.json, verify in `vendor/civicrm/civicrm-core/PATCHES.txt`)
2. civicrm-drupal [#7d0b62961f988022513c5a9d651376b6cad251d4](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/7d0b62961f988022513c5a9d651376b6cad251d4) Add `web/libraries/civicrm/civicrm.config.php` and `settings_location.php` needed to support kcfinder - check these still exist after a core upgrade

### CiviCRM Extension Custom Patches

1. IatsPayments [#48ca75a3d447302c40e1ee8444571111b7c91e1a](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/48ca75a3d447302c40e1ee8444571111b7c91e1a) (re-applied for iATS 1.12.0 as ff77027ff9) - Fix Filtering of Ipv4 Addresses in FAPs Payment Processor
2. IatsPayments [#fa0be02eac](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/fa0be02eac) (re-applied for iATS 1.12.0 as 920f4c5857) - Apply patch to allow updating of credit card
details with First Pay Credit Card Payment Processor
3. IatsPayments [#7d55f5bf75f5b0ff5a0f2f01ad48a18db1812ce5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/7d55f5bf75f5b0ff5a0f2f01ad48a18db1812ce5) (re-applied for iATS 1.12.0 as 8d47896a6b) Add in missing function credentials
4. IatsPayments [#86fb2260f2747c4a72328571bf5653a9608b60ee](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/86fb2260f2747c4a72328571bf5653a9608b60ee) (re-applied for iATS 1.12.0 as d967090d02) Fix undefined class E
5. IatsPayments [#49a881cec4b72b1691836edf4df9142589fc4558](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/49a881cec4b72b1691836edf4df9142589fc4558) (re-applied for iATS 1.12.0 as 539408f668) Fix applying patch to support updating of card details in the back office - only adds a debug log line, which #9 removes again, so the net change is nothing
6. IatsPayments [#c9e8db49841bdd3b60229a446d6cfe6b04554702](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/c9e8db49841bdd3b60229a446d6cfe6b04554702) (re-applied for iATS 1.12.0 as 12e661e031, plus follow-up 52387f7f0a replacing the undeclared `$this->response` with a local `$response` in FapsRequest.php) Resolve IATS FAPS notices as per upstream PR
7. IatsPayments [#2f0a483e37dff22a754ae12cd21eaf4d203013c5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/2f0a483e37dff22a754ae12cd21eaf4d203013c5) (re-applied for iATS 1.12.0 as 60f81c700f) Update Faps utf8 encoding to match upstream PR
8. IatsPayments [#403347846e2123ad1496c8ddd15b08170e8d7fa5](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/403347846e2123ad1496c8ddd15b08170e8d7fa5) (re-applied for iATS 1.12.0 as 31a811ff94) Switched country and state to use two-letter notation
9. IatsPayments [#f9ec4f488](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/f9ec4f488951d66081b95d1cccdae29b94af58f2) (re-applied for iATS 1.12.0 as 6c6bafde45) Remove FAPS request debug logging
10. ActivityIcal [#7ca595d3314a14b52531fc6f5e1d7762223c9acd](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/7ca595d3314a14b52531fc6f5e1d7762223c9acd) (re-applied as [da470e4026](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/da470e402629e14610c1e797ed1595dcb2da5ed7)) Re-apply necessary patches for SACE
11. ActivityIcal [#47aa3cde805488e446937d177a561f144b5c2433](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/47aa3cde805488e446937d177a561f144b5c2433) Remove activity status filtering from ICAL feed
12. ActivityIcal [#f937fa177cca3b381b32fffbfd6251ce16d9a452](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/f937fa177cca3b381b32fffbfd6251ce16d9a452) Remove some uncessary require onces
13. ActivityIcal [#a6bba54a3](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/a6bba54a3) Added outlook safe handling
14. ActivityIcal [#aa9103c19cafbfa7d6988e2234fb15e625c917a9](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/aa9103c19cafbfa7d6988e2234fb15e625c917a9) Re-apply necessary patches from ical extension (re-applies #11-#13 after an extension upgrade, and adds UTF-8 safe line folding in Feed.php)

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
