### CiviCRM extension patches
1. [d7028ae26](https://lab.jmaconsulting.biz/jma/sace/sace-civicrm-site/-/commit/d7028ae26) - Apply patch to Report Error extension to fix issues on D9


**NOTE: In future, please update this file with the commit link whenever a new extension customisation is added. For civicrm core file customization, update the civicrm-patches.patch file to include the diff/patch**

### Recipe to apply CiviCRM extension customisation
1. Update CiviCRM extension files and commit
2. Then checked the commit link patch, if already merged/included in the current extension release. 
3. If not then cherry-pick the commit hash mentioned above.
4. If the patch is included in the current release, ignore and go to next commit, repeat step 2 and 3. 
5. Repeat the same for other commits until you cherry-picked all the commits.

CLI: 
```sh
$ git checkout origin/master -b civicrm_ext_upgrades
# update the CiviCRM core files
$ git add htdocs/sites/default/files/civicrm/ext && git commit  -m "CiviCRM 5.x upgrade" wp-content/plugins/civicrm
$ git cherry-pick 35aa73ec # lets say 35aa73ec is the hashcode of the commit that contains one of the civicrm ext customization changes
# repeat step 2-5 until all the commits are cherry-picked
$ git push origin civicrm_ext_upgrades
## submit Merge Request
