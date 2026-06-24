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
 * @property string $receipt_no
 * @property string $issued_on
 * @property string $contact_id
 * @property string $receipt_amount
 * @property bool|string $is_duplicate
 * @property string $uid
 * @property string $ip
 * @property string $issue_type
 * @property string $issue_method
 * @property string $receipt_status
 * @property string $email_tracking_id
 * @property string $email_opened
 * @property string $location_issued
 */
class CRM_Cdntaxreceipts_DAO_CdntaxreceiptsLog extends CRM_Cdntaxreceipts_DAO_Base {

}
