<?php

class CRM_Cdntaxreceipts_Utils_Queue {

  public static function createQueue($year, $previewMode, $tasks, $type) {

    $queueName = 'cdntaxreceipt_' . time();

    $queue = Civi::queue($queueName, [
      'type' => 'Sql',
      'runner' => 'task',
      'error' => 'abort',
    ]);

    \Civi\Api4\UserJob::create(FALSE)->setValues([
      'job_type' => 'contact_import',
      'status_id:name' => 'in_progress',
      'queue_id.name' => $queue->getName(),
      'metadata' => [
        'preview' => $previewMode,
        'year' => $year,
        'print' => [],
        'type' => $type,
        'count' => [
          'email' => 0,
          'print' => 0,
          'data' => 0,
          'fail' => 0,
          'total' => count($tasks),
        ],
      ],
    ])->execute();

    foreach ($tasks as $task) {
      $queue->createItem($task);
    }

    return $queue;

  }

  public static function getQueueId($queue) {
    // why can't we get the id from the queue object ?
    $queue = \Civi\Api4\Queue::get(FALSE)
      ->addSelect('*', 'custom.*')
      ->addWhere('name', '=', $queue->getName())
      ->execute()
      ->first();

    return $queue['id'];
  }

  public static function updateMetadata($ret, $method, &$metadata) {
    if ($ret == 0) {
      $metadata['count']['fail']++;
    }
    elseif ($method == 'email') {
      $metadata['count']['email']++;
    }
    elseif ($method == 'print') {
      $metadata['count']['print']++;
    }
    elseif ($method == 'data') {
      $metadata['count']['data']++;
    }
  }

  public static function processOneSingleContributionReceipt(CRM_Queue_TaskContext $ctx, $contributionId, $previewMode, $originalOnly) {

    $queueName = $ctx->queue->getName();

    $contribution = new CRM_Contribute_DAO_Contribution();
    $contribution->id = $contributionId;
    if (!$contribution->find(TRUE)) {
      throw new \CRM_Core_Exception('CDNTaxReceipts: Could not find corresponding contribution id.');
    }

    // if contribution is eligible for receipting, issue the tax receipt.  Otherwise ignore.
    if (cdntaxreceipts_eligibleForReceipt($contribution->id)) {

      $userJob = \Civi\Api4\UserJob::get(FALSE)
        ->addSelect('id', 'metadata', 'queue_id')
        ->addWhere('queue_id.name', '=', $queueName)
        ->setLimit(1)
        ->execute()
        ->first();

      $queueId = $userJob['queue_id'];
      $userJobId = $userJob['id'];
      $metadata = $userJob['metadata'];

      list($issued_on, $receipt_id) = cdntaxreceipts_issued_on($contribution->id);
      if (empty($issued_on) || !$originalOnly) {
        $receiptsForPrinting = cdntaxreceipts_createNewPDF();
        list($ret, $method) = cdntaxreceipts_issueTaxReceipt($contribution, $receiptsForPrinting, $previewMode);
        self::updateMetadata($ret, $method, $metadata);
      }

      // in preview mode print everything
      if ($receiptsForPrinting && ($previewMode || $method == 'print')) {
        $metadata['folderName'] ??= self::generateFolderName();
        if (self::saveReceiptForLaterPrint($receiptsForPrinting, $queueId, $metadata['type'], $contributionId, $metadata['folderName'])) {
          // add contactId to metadata to compute the pdf to print at the end
          if (!isset($metadata['print'])) {
            $metadata['print'] = [];
          }
          $metadata['print'][] = $contributionId;
        }
        else {
          $metadata['count']['fail']++;
        }
      }

      // save metadata
      $results = \Civi\Api4\UserJob::update(FALSE)
        ->addValue('metadata', $metadata)
        ->addWhere('id', '=', $userJobId)
        ->execute();
    }

    return TRUE;
  }

  /**
   * TODO: ensure we don't do the same contactId twice (e.g. how does the queue deal with refresh?)
   */
  public static function processOneAggregateReceipt(CRM_Queue_TaskContext $ctx, $contactId, $year, $previewMode, $contribution_status) {

    $queueName = $ctx->queue->getName();

    $contributions = $contribution_status['contributions'];
    $method = $contribution_status['issue_method'];

    if (empty($issuedOn) && count($contributions) > 0) {

      $userJob = \Civi\Api4\UserJob::get(FALSE)
        ->addSelect('id', 'metadata', 'queue_id')
        ->addWhere('queue_id.name', '=', $queueName)
        ->setLimit(1)
        ->execute()
        ->first();
      $queueId = $userJob['queue_id'];
      $userJobId = $userJob['id'];
      $metadata = $userJob['metadata'];
      $metadata['folderName'] ??= self::generateFolderName();

      $receiptsForPrinting = cdntaxreceipts_createNewPDF();
      $ret = cdntaxreceipts_issueAggregateTaxReceipt($contactId, $year, $contributions, $method, $receiptsForPrinting, $previewMode);
      self::updateMetadata($ret, $method, $metadata);

      // in preview mode print everything
      if ($previewMode || $method == 'print') {
        if (self::saveReceiptForLaterPrint($receiptsForPrinting, $queueId, $metadata['type'], $contactId, $metadata['folderName'])) {
          // add contactId to metadata to compute the pdf to print at the end
          if (!isset($metadata['print'])) {
            $metadata['print'] = [];
          }
          $metadata['print'][] = $contactId;
        }
        else {
          $metadata['count']['fail']++;
        }
      }

      // save metadata
      $results = \Civi\Api4\UserJob::update(FALSE)
        ->addValue('metadata', $metadata)
        ->addWhere('id', '=', $userJobId)
        ->execute();
    }

    return TRUE;
  }

  /**
   * TODO: ensure we don't do the same contactId twice (e.g. how does the queue deal with refresh?)
   */
  public static function processOneContactReceipt(CRM_Queue_TaskContext $ctx, $contactId, $year, $previewMode) {

    $queueName = $ctx->queue->getName();

    list($issuedOn, $receiptId) = cdntaxreceipts_annual_issued_on($contactId, $year);
    $contributions = cdntaxreceipts_contributions_not_receipted($contactId, $year);

    if (empty($issuedOn)) {
      $userJob = \Civi\Api4\UserJob::get(FALSE)
        ->addSelect('id', 'metadata', 'queue_id')
        ->addWhere('queue_id.name', '=', $queueName)
        ->setLimit(1)
        ->execute()
        ->first();
      $queueId = $userJob['queue_id'];
      $userJobId = $userJob['id'];
      $metadata = $userJob['metadata'];
      $metadata['folderName'] ??= self::generateFolderName();

      // We don't use "collected" pdf because we save each to a temporary file
      // for later use when the full queue is processed, and then "collect"
      // them when the user clicks to download the results.
      $receiptsForPrinting = cdntaxreceipts_createNewPDF();
      list($ret, $method) = cdntaxreceipts_issueAnnualTaxReceipt($contactId, $year, $receiptsForPrinting, $previewMode);

      // update statistics
      self::updateMetadata($ret, $method, $metadata);

      // in preview mode print everything
      if ($previewMode || $method == 'print') {
        if (self::saveReceiptForLaterPrint($receiptsForPrinting, $queueId, $metadata['type'], $contactId, $metadata['folderName'])) {
          // add contactId to metadata to compute the pdf to print at the end
          if (!isset($metadata['print'])) {
            $metadata['print'] = [];
          }
          $metadata['print'][] = $contactId;
        }
        else {
          $metadata['count']['fail']++;
        }
      }

      // save metadata
      $results = \Civi\Api4\UserJob::update(FALSE)
        ->addValue('metadata', $metadata)
        ->addWhere('id', '=', $userJobId)
        ->execute();
    }

    return TRUE;
  }

  public static function saveReceiptForLaterPrint($receiptsForPrinting, $queueId, $type, $objectId, $folderName) {

    // save the file for later usage
    $filename = self::makeTmpFileName($queueId, $type, $objectId, $folderName);
    if ($receiptsForPrinting->getNumPages() > 0) {
      if (!file_exists($filename)) {
        $receiptsForPrinting->Output($filename, 'F');
      }
    }
    else {
      return FALSE;
    }

    // Ok, created
    return TRUE;
  }

  public static function makeTmpFileName($queueId, $type, $objectId, $folderName) {
    $config = CRM_Core_Config::singleton();
    $baseFolder = rtrim($config->customFileUploadDir, '/\\') . '/cdntaxreceipts/';
    if (!is_dir($baseFolder . $folderName)) {
      mkdir($baseFolder . $folderName, 0777, TRUE);
      // we add a timestamp so the cronjob can clean up later based on time
      file_put_contents($baseFolder . $folderName . '/timestamp.txt', time());
    }
    return $baseFolder . $folderName . '/' . CRM_Utils_File::makeFilenameWithUnicode('queuejob_' . $queueId . '_' . $type . $objectId) . '.pdf';
  }

  public static function getAllCollectedPdfFilename($queueId) {
    $userJob = \Civi\Api4\UserJob::get(FALSE)
      ->addSelect('metadata')
      ->addWhere('queue_id', '=', $queueId)
      ->setLimit(1)
      ->execute()
      ->first();
    $metadata = $userJob['metadata'];
    $previewMode = $metadata['preview'];
    $year = $metadata['year'];
    $type = $metadata['type'];

    // create pdf to combine all the printed ones into one
    $pdf = cdntaxreceipts_createNewPDF();
    // The custom template, if any, is already in each pdf. Don't add twice.
    $pdf->setSuppressHeader(TRUE);
    $objects = $metadata['print'];
    $metadata['folderName'] ??= self::generateFolderName();
    foreach ($objects as $objectId) {
      $filename = self::makeTmpFileName($queueId, $type, $objectId, $metadata['folderName']);
      if (file_exists($filename)) {
        $pageCount = $pdf->setSourceFile($filename);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
          // import a page
          $templateId = $pdf->importPage($pageNo);
          // get the size of the imported page
          $size = $pdf->getTemplateSize($templateId);

          // create a page (landscape or portrait depending on the imported page size)
          if ($size['width'] > $size['height']) {
            $pdf->AddPage('L', array($size['width'], $size['height']));
          }
          else {
            $pdf->AddPage('P', array($size['width'], $size['height']));
          }

          // use the imported page
          $pdf->useTemplate($templateId);
        }
      }
      else {
        // error
        $metadata['count']['fail']++;
      }
    }

    // save metadata
    $results = \Civi\Api4\UserJob::update(FALSE)
      ->addValue('metadata', $metadata)
      ->addWhere('id', '=', $userJob['id'])
      ->execute();

    return $pdf;
  }

  private static function generateFolderName(): string {
    return bin2hex(random_bytes(16));
  }

}
