<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of CRM_Purgelogs_BAO_Purgelogs
 *
 * @author ixiam
 */

use CRM_Purgelogs_ExtensionUtil as E;
use CRM_Purgelogs_Config as C;

class CRM_Purgelogs_BAO_Purgelogs {

  public static function Purge() {
    // Get processes
    $processes = C::singleton()->getParams('processes');
    if (empty($processes)) {
      return NULL;
    }

    // Go through processes
    $processes = json_decode($processes);
    $filesDeleted = array();
    $i = 0;
    $deleteFiles = FALSE;
    foreach ($processes as $process) {
      if ($process->Active) {
        // Get folder path
        switch ($process->Folder) {
          case "ConfigAndLog":
            $basePath = CRM_Core_Config::singleton()->configAndLogDir;
            break;

          case "custom":
            $basePath = CRM_Core_Config::singleton()->customFileUploadDir;
            break;

          case "upload":
            $basePath = CRM_Core_Config::singleton()->uploadDir;
            break;
        }
        // Search files
        $processPath = $basePath . $process->Filename;
        if (glob($processPath)) {
          foreach (glob($processPath) as $file) {
            $now = new DateTime();
            $editDate = new DateTime(date('Y-m-d h:i:s', filemtime($file)));
            $interval = date_diff($editDate, $now);

            // Get number of days of last modification
            $days = $interval->d + ($interval->m * 30) + ($interval->y * 365);
            $period = 1;
            if ($process->Period == 'Months') {
              $period = 30;
            }
            elseif ($process->Period == 'Weeks') {
              $period = 7;
            }

            // Delete file if number of days is bigger than process frequency
            if ($days >= ($process->Frequency * $period)) {
              if (unlink($file)) {
                $filesDeleted[] = $file;
                CRM_Core_Error::debug_log_message('Purgelogs - ' . $now->format("Y-m-d h:i:s") . ": File deleted - " . $file);
                $fileName = str_replace($basePath . $process->Folder, '', $file);
                // Add last activity
                $processes[$i]->LastActivity = $now->format("Y-m-d h:i:s") . ": File deleted: " . $fileName;
                $deleteFiles = TRUE;
              }
            }
          }
        }
      }
      $i++;
    }

    // If files were deleted, the settings are updated (last activity)
    if ($deleteFiles) {
      $config = [];
      $config['processes'] = json_encode($processes);
      C::singleton()->setParams($config);
    }

    return $filesDeleted;
  }

}
