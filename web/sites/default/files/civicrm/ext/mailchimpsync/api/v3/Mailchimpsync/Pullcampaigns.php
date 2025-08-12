<?php

use Civi\Api4\Activity;
use CRM_Mailchimpsync_ExtensionUtil as E;

/**
 * Mailchimpsync.Pullcampaigns API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see https://docs.civicrm.org/dev/en/latest/framework/api-architecture/
 */
function _civicrm_api3_mailchimpsync_Pullcampaigns_spec(&$spec) {
  // $spec['config']['api.required'] = 1;
  $spec['since_date']['description'] = 'Fetch campaigns since this date. Default is 1 week ago but useful to provide older date for first import.';
  $spec['since_date']['api.default'] = '- 1 week';
}

/**
 * Mailchimpsync.Pullcampaigns API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_mailchimpsync_Pullcampaigns($params) {
  $since = strtotime($params['since_date']);
  if ($since === FALSE) {
    throw new CRM_Core_Exception("Invalid since_date parameter in Mailchimpsync.Pullcampaigns");
  }
  $since = date('Y-m-d', $since);

  Civi::log()->debug("Mailchimpsync.Pullcampaigns", ['=start' => 'mcsPullCampaigns', '=timed' => 1]);

  $config = CRM_Mailchimpsync::getConfig();
  $accounts = array_keys($config['accounts'] ?? []);
  $campaignsImported = Activity::get(FALSE)
    ->addWhere('activity_type_id:name', '=', 'mailchimp_campaign_sent')
    ->addSelect('Mailchimp_Campaign_ID.Mailchimp_Campaign_ID')
    ->execute()->column('Mailchimp_Campaign_ID.Mailchimp_Campaign_ID');

  $values = [];
  foreach ($accounts as $apiKey) {
    $api = CRM_Mailchimpsync::getMailchimpApi($apiKey);
    $campaigns = $api->get("campaigns", [
      'count' => 1000,
      'status' => 'sent',
      'since_sent_time' => $since,
    ]);

    foreach ($campaigns['campaigns'] as $campaign) {
      if (in_array($campaign['id'], $campaignsImported)) {
        Civi::log()->debug("Campaign already imported {id}", ['id' => $campaign['id']]);
        // Already done this one.
        continue;
      }

      // Create a campaign.
      $audience = htmlspecialchars($campaign['recipients']['list_name']);
      $url = htmlspecialchars($campaign['long_archive_url'] ?? '');
      $segmentHtml = trim(CRM_Utils_String::purifyHTML($campaign['recipients']['segment_text'] ?? ''));
      $subject = $campaign['settings']['subject_line'];
      $from = $campaign['settings']['from_name'];

      $activity = Activity::create(FALSE)
        ->addValue('subject', ($campaign['settings']['title'] ?? '') ?: $subject)
        ->addValue('activity_type_id:name', 'mailchimp_campaign_sent')
        ->addValue('activity_date_time', $campaign['send_time'])
        ->addValue('status_id:name', 'Completed')
        ->addValue('source_contact_id', CRM_Core_BAO_Domain::getDomain()->contact_id)
        ->addValue('Mailchimp_Campaign_ID.Mailchimp_Campaign_ID', $campaign['id'])
        ->addValue('details', <<<HTML
        <p>To Audience: <strong>$audience</strong></p>
        <p>From: <strong>$from</strong></p>
        <p>Subject: <strong>$subject</strong></p>
        <p><a href="$url" target=_blank rel="noopener noreferrer">Archive link</a></p>
        $segmentHtml
        HTML)
        ->execute()->first();
      Civi::log()->debug("Campaign imported as activity {id}: activity ID {aid}. Importing sent-to", ['id' => $campaign['id'], 'aid' => $activity['id']]);

      $batch = 1000;
      $offset = 0;
      do {
        Civi::log()->debug("Loading $batch from $offset");
        $recipients = $api->get("reports/{$campaign['id']}/sent-to", [
          'count' => $batch,
          'offset' => $offset,
          'fields' => 'total_items,sent_to.email_id',
        ]);
        $offset += count($recipients['sent_to']);

        // Construct SQL to insert the contact_id that matches this mailchimp email_id
        $params = [1 => [$activity['id'], 'Positive']];
        $n = 2;
        $in = [];
        foreach ($recipients['sent_to'] as $recipient) {
          $params[$n] = [$recipient['email_id'], 'String'];
          $in[] = "%$n";
          $n++;
        }
        $in = implode(", ", $in);
        $sql = <<<SQL
          INSERT IGNORE INTO civicrm_activity_contact (record_type_id, activity_id, contact_id)
          SELECT 3 record_type_id, %1 activity_id, cts.civicrm_contact_id contact_id
          FROM civicrm_mailchimpsync_cache cts
          WHERE mailchimp_member_id IN ($in);
        SQL;
        CRM_Core_DAO::executeQuery($sql, $params);
        Civi::log()->debug("Added activity contact records. Done $offset/{$recipients['total_items']}");
      } while ($offset < $recipients['total_items']);
      $values[] = $activity['id'];
    }
  }

  Civi::log()->debug("", ['=pop' => 1]);
  return civicrm_api3_create_success($values, $params, 'Mailchimpsync', 'Pullcampaigns');
}
