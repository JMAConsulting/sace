<?php
namespace Drupal\sace_activity_schedule_modification\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\Activity;
use Civi\Api4\ActivityContact;
use Civi\Api4\UFMatch;

/**
 * Sace Activity Schedule Webform Handler plugin.
 *
 * @WebformHandler(
 *   id = "sace_activity_schedule",
 *   label = @Translation("Sace Activity Schedule"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Sace Activity Schedule Handler"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class SaceActivityScheduleWebformHandler extends WebformHandlerBase {

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
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
    if ($webform_submission_data) {
      $contacts = [];
      if (!empty($webform_submission_data['staff'])) {
        $contacts = array_merge($contacts, $webform_submission_data['staff']);
      }
      if (!empty($webform_submission_data['organisation_select'])) {
        $contacts = array_merge($contacts, $webform_submission_data['organisation_select']);
      }
      if (!empty($webform_submission_data['user_team'])) {
        $user_ids = array_keys(\Drupal::entityTypeManager()->getStorage('user')->loadByProperties(['field_user_team' => $webform_submission_data['user_team']]));
        $team_contact_ids = UFMatch::get(FALSE)->addWhere('uf_id', 'IN', $user_ids)->execute()->column('contact_id');
        $contacts = array_merge($contacts, $team_contact_ids);
      }
      if (!empty($contacts)) {
        $unique_contacts = array_unique($contacts);
        foreach ($unique_contacts as $contact_id) {
          ActivityContact::create(FALSE)
            ->addValue('contact_id', $contact_id)
            ->addValue('activity_id', $webform_submission_data['civicrm']['activity'][1]['id'])
            ->addValue('record_type_id:name', 'Activity Assignees')
            ->execute();
        }
      }
    }
  }

}