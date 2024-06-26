<?php

namespace Drupal\clin_update_waitlist_entry\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\GroupContact;

/**
 * Clin Add to Waitlist Handler Plugin.
 *
 * @WebformHandler(
 *   id = "update_waitlist",
 *   label = @Translation("Update Waitlist"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Updates Waitlist entries"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class UpdateWaitlistHandler extends WebformHandlerBase {

  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->civicrm = $container->get('civicrm');
    $instance->database = \Drupal::database();
    return $instance;
  }

  /**
   * {@inheritdoc}
   * Remove the contact from the waitlist group if needed and add to any new groups
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
    $civicrm_submission_data = $this->database->query("SELECT civicrm_data FROM {webform_civicrm_submissions} WHERE sid = :sid", [
      ':sid' => $webform_submission->id(),
    ]);
    if ($webform_submission_data && $civicrm_submission_data) {
      while ($row = $civicrm_submission_data->fetchAssoc()) {
        $data = unserialize($row['civicrm_data']);
        if (!empty($webform_submission_data['existing_waitlist']) && !empty($data['contact'][2]['id'])) {
          if ($webform_submission_data['existing_waitlist'] == 'Remove from Waitlist') {
            $group_contacts = \Civi\Api4\GroupContact::update(FALSE)
              ->addWhere('contact_id', '=', $data['contact'][2]['id'])
              ->addWhere('group_id.Waitlist_Group.Waitlist_Group_', '=', 1)
              ->addValue('status', 'Removed')
              ->execute();
          }
        }
        if (!empty($webform_submission_data['add_to_waitlist']) && !empty($data['contact'][2]['id'])) {
          $update = \Civi\Api4\GroupContact::update(FALSE)
            ->addWhere('contact_id', '=', $data['contact'][2]['id'])
            ->addWhere('group_id', '=', $webform_submission_data['add_to_waitlist'])
            ->addValue('status', 'Added')
            ->execute();
          if (!$update->first()) { // Could not update so we create a new GroupContact
            \Civi\Api4\GroupContact::create(FALSE)
              ->addValue('contact_id', $data['contact'][2]['id'])
              ->addValue('group_id', $webform_submission_data['add_to_waitlist'])
              ->execute();
            }
        }
      }
    }
  }
}