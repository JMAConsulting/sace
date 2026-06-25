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
 * @property string $receipt_id
 * @property string $contribution_id
 * @property string $contribution_amount
 * @property string $receipt_amount
 * @property string $receive_date
 */
class CRM_Cdntaxreceipts_DAO_CdntaxreceiptsLogContributions extends CRM_Cdntaxreceipts_DAO_Base {

}
