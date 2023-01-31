<?php

namespace Drupal\pe_booking_activity_role\Plugin\WebformHandler;

use Civi\Api4\ActivityRole;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "pe_activity_role_update",
 *   label = @Translation("PE Activity Role update"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Handle Updating Acivity Assignee Role appropriately."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class PeActivityRoleWebformHandler extends WebformHandlerBase {

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

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $this->database = \Drupal::database();
    $webform_submission_data = $webform_submission->getData();
    $civicrm_submission_data = $this->database->query("SELECT civicrm_data FROM {webform_civicrm_submissions} WHERE sid = :sid", [
      ':sid' => $webform_submission->id(),
    ]);

    if ($civicrm_submission_data) {
      while ($row = $civicrm_submission_data->fetchAssoc()) {
        $data = unserialize($row['civicrm_data']);
        foreach ($data['contact'] as $key => $contactId) {
          // Contact 1 on the PE Appointment Create and PE Update Booking are the Organisation and organisation contact that the booking is for not staff members
          if ($key == 1 || $key == 2) {
            continue;
          }
          $role = FALSE;
          if (!empty($webform_submission_data['contact_' . $key . '_shadowing'])) {
            $role = 'Shadowing';
          }
          elseif (!empty($webform_submission_data['contact_' . $key . '_facilitator'])) {
            $role = 'Facilitator';
          }
          if (!empty($role)) {
            ActivityRole::create(FALSE)->addValue('assignee_contact_id', $contactId['id'])->addValue('activity_id', $data['activity'][1]['id'])->addValue('role_id:name', $role)->execute();
          }
        }
      }
    }
  }

}
