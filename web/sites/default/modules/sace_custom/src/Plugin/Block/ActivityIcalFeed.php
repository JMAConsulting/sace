<?php

namespace Drupal\sace_custom\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Cache\Cache;

/**
 * Provides block with current user's activity ical feed url from CiviCRM
 */

#[Block(
  id: "user_activity_ical_feed",
  admin_label: new TranslatableMarkup("Activity iCal Feed"),
  category: new TranslatableMarkup("CiviCRM")
)]

class ActivityIcalFeed extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::service('civicrm')->initialize();

    $contactId = \CRM_Core_Session::getLoggedInContactId();

    if (!$contactId || !_activityical_contact_has_feed_group($contactId)) {
      return [];
    }

    // Get the feed URL
    $feedUrl = \CRM_Activityical_Feed::getInstance($contactId)->getUrl();

    $markup = <<<HTML
      <a href="{$feedUrl}" target="_blank">
        {$this->t('My Feed')}
        <i class="crm-i fa-rss"></i>
      </a>
    HTML;

    return [
      '#markup' => $markup,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $userId = \Drupal::currentUser()->id();
    return Cache::mergeTags(parent::getCacheTags(), ["user:{$userId}"]);
  }

}
