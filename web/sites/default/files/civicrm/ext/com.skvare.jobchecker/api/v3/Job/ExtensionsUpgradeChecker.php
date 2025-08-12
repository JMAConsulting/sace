<?php
use CRM_Jobchecker_ExtensionUtil as E;

/**
 * Job.Extensionsupgradechecker API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_job_extensions_upgrade_checker_spec(&$spec) {}

/**
 * Job.Extensionsupgradechecker API
 *
 * @param array $params
 *
 * @return array
 *   API result descriptor
 *
 * @see civicrm_api3_create_success
 *
 * @throws API_Exception
 */
function civicrm_api3_job_extensions_upgrade_checker($params) {
  $messages = [];
   $extensionSystem = CRM_Extension_System::singleton();
   $mapper = $extensionSystem->getMapper();
   $manager = $extensionSystem->getManager();

   if ($extensionSystem->getDefaultContainer()) {
     $basedir = $extensionSystem->getDefaultContainer()->baseDir;
   }

   if (empty($basedir)) {
     // no extension directory
     $messages[] = ts('Directory not writable.<br/> Your extensions directory is not set.  Click <a href="%1">here</a> to set the extensions directory. <br/>',
       [1 => CRM_Utils_System::url('civicrm/admin/setting/path', 'reset=1')]);
   }

   if (!is_dir($basedir)) {
     $messages[] =  ts('Extensions directory incorrect. Your extensions directory path points to %1, which is not a directory.  Please check your file system.',
       [1 => $basedir]);
   }
   elseif (!is_writable($basedir)) {
     $messages[] = ts('Writable.<br> Your extensions directory (%1) is read-only. If you would like to perform downloads or upgrades, then change the file permissions.',
       [1 => $basedir]);
   }

   if (empty($extensionSystem->getDefaultContainer()->baseUrl)) {
     $messages[] = ts('The extensions URL is not properly set. Please go to the <a href="%1">URL setting page</a> and correct it.',
         [1 => CRM_Utils_System::url('civicrm/admin/setting/url', 'reset=1')]);
   }

   if (!$extensionSystem->getBrowser()->isEnabled()) {
     $messages[] = ts('Extensions check disabled.<br> Not checking remote URL for extensions since ext_repo_url is set to false.');
   }

   try {
     $remotes = $extensionSystem->getBrowser()->getExtensions();
   }
   catch (CRM_Extension_Exception | \GuzzleHttp\Exception\GuzzleException $e) {
     $messages[] = ts('Extension download error');
   }

   $stauses = $manager->getStatuses();
   $keys = array_keys($stauses);
   $enabled = array_keys(array_filter($stauses, function($status) {
     return $status === CRM_Extension_Manager::STATUS_INSTALLED;
   }));
   sort($keys);
   $updates = $errors = [];

   $extPrettyLabel = function($key) use ($mapper) {
     // We definitely know a $key, but we may not have a $label.
     // Which is too bad - because it would be nicer if $label could be the reliable start of the string.
     $keyFmt = '<code>' . htmlentities($key) . '</code>';
     try {
       $info = $mapper->keyToInfo($key);
       if ($info->label) {
         return sprintf('"<em>%s</em>" (%s)', htmlentities($info->label), $keyFmt);
       }
     }
     catch (CRM_Extension_Exception $ex) {
       return "($keyFmt)";
     }
   };

   foreach ($keys as $key) {
     try {
       $obj = $mapper->keyToInfo($key);
     }
     catch (CRM_Extension_Exception $ex) {
       $errors[] = ts('Failed to read extension (%1). Please refresh the extension list.', [1 => $key]);
       continue;
     }
     $row = CRM_Admin_Page_Extensions::createExtendedInfo($obj);
     switch ($row['status']) {
       case CRM_Extension_Manager::STATUS_INSTALLED_MISSING:
         $errors[] = ts('%1 is installed but missing files.', [1 => $extPrettyLabel($key)]);
         break;

       case CRM_Extension_Manager::STATUS_INSTALLED:
         $missingRequirements = array_diff($row['requires'], $enabled);
         if (!empty($row['requires']) && $missingRequirements) {
           $errors[] = ts('%1 has a missing dependency on %2', [
             1 => $extPrettyLabel($key),
             2 => implode(', ', array_map($extPrettyLabel, $missingRequirements)),
             'plural' => '%1 has missing dependencies: %2',
             'count' => count($missingRequirements),
           ]);
         }
         elseif (!empty($remotes[$key]) && version_compare($row['version'], $remotes[$key]->version, '<')) {
           $updates[] = $row['label'] . ': ' . $mapper->getUpgradeLink($remotes[$key], $row);
         }
         break;
     }
   }

   if ($errors) {
     $messages[] = ts('There is one extension error:', [
           'count' => count($errors),
           'plural' => 'There are %count extension errors:',
         ])
         . '<ul><li>' . implode('</li><li>', $errors) . '</li></ul>'
         . ts('To resolve any errors, go to <a %1>Manage Extensions</a>.', [
           1 => 'href="' . CRM_Utils_System::url('civicrm/admin/extensions', 'reset=1') . '"',
         ]);
   }

   if ($updates) {
     $messages[] = '<ul><li>' . implode('</li><li>', $updates) . '</li></ul>' .
       ts('Extension Update Available', ['plural' => '%count Extension Updates Available', 'count' => count($updates)]);
   }

   if (!empty($messages)) {
     throw new API_Exception(implode('<br>', $messages));
   }

   return civicrm_api3_create_success(TRUE, $params, 'Job', 'ExtensionsUpgradeChecker');
}
