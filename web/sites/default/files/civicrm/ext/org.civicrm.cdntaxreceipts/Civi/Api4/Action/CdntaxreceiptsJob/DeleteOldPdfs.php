<?php
namespace Civi\Api4\Action\CdntaxreceiptsJob;

class DeleteOldPdfs extends \Civi\Api4\Generic\AbstractAction {

  /**
   * Number of days to keep
   *
   * @var int
   */
  protected int $daysToKeep = 7;

  public function _run(\Civi\Api4\Generic\Result $result): void {
    $results = [
      'deleted' => [],
      'kept' => [],
    ];
    foreach (glob(rtrim(\CRM_Core_Config::singleton()->customFileUploadDir, '/\\') . '/cdntaxreceipts/*', GLOB_ONLYDIR) as $dir) {
      $bucket = 'kept';
      $file = $dir . '/timestamp.txt';
      if (file_exists($file)) {
        $ts = file_get_contents($file);
        if (($ts + $this->daysToKeep * 86400) < time()) {
          \CRM_Utils_File::cleanDir($dir, TRUE, FALSE);
          $bucket = 'deleted';
        }
      }
      $results[$bucket][$dir] = $dir;
    }
    $result->exchangeArray($results);
  }

}
