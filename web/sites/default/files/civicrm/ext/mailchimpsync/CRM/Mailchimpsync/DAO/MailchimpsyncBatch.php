<?php

/**
 * DAOs provide an OOP-style facade for reading and writing database records.
 *
 * DAOs are a primary source for metadata in older versions of CiviCRM (<5.74)
 * and are required for some subsystems (such as APIv3).
 *
 * This stub provides compatibility. It is not intended to be modified in a
 * substantive way. Property annotations may be added, but are not required.
 * @property string $id 
 * @property string $mailchimp_list_id 
 * @property string $mailchimp_batch_id 
 * @property string $status 
 * @property string $submitted_at 
 * @property string $completed_at 
 * @property int|string $finished_operations 
 * @property int|string $errored_operations 
 * @property int|string $total_operations 
 * @property int|string $response_processed 
 */
class CRM_Mailchimpsync_DAO_MailchimpsyncBatch extends CRM_Mailchimpsync_DAO_Base {

  /**
   * Required by older versions of CiviCRM (<5.74).
   * @var string
   */
  public static $_tableName = 'civicrm_mailchimpsync_batch';

}
