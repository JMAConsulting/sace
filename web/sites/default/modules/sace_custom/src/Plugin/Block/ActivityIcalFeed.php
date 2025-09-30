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

  protected function getContactId(): ?int {
    $path = \Drupal::service('path.current')->getPath();

    // if we are on a user page, check which user
    if (str_starts_with($path, '/user/')) {
      $userId = explode('/', $path)[2];

      if (!is_numeric($userId)) {
        // not a user profile page - disable the block
        return NULL;
      }

      $userId = (int) $userId;
      return \Civi\Api4\UFMatch::get(FALSE)
        ->addSelect('contact_id')
        ->addWhere('uf_id', '=', $userId)
        ->execute()
        ->first()['contact_id'] ?? NULL;
    }

    // not a user page - use logged in user
    return \CRM_Core_Session::getLoggedInContactId();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    \Drupal::service('civicrm')->initialize();

    $contactId = $this->getContactId();

    if (!$contactId || !_activityical_contact_has_feed_group($contactId)) {
      return [];
    }

    // Get the feed URL
    $feedUrl = \CRM_Activityical_Feed::getInstance($contactId)->getUrl();

    $markup = <<<HTML
      <a href="{$feedUrl}" target="_blank">
        {$this->t('Activity Feed')}
        <i class="fa fa-rss"></i>
      </a>
    HTML;

    return [
      '#markup' => $markup,
      '#cache' => [
        'max-age' => 0, // No caching
        'tags' => ["contact:{$contactId}"],
      ]
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    \Drupal::service('civicrm')->initialize();
    $contactId = $this->getContactId();
    return Cache::mergeTags(parent::getCacheTags(), ["contact:{$contactId}"]);
  }

}
