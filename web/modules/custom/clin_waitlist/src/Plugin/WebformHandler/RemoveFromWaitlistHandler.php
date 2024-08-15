<?php
namespace Drupal\clin_waitlist\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\GroupContact;

/**
 * Clin Remove from Waitlist Handler Plugin.
 *
 * @WebformHandler(
 *   id = "remove_from_waitlist",
 *   label = @Translation("Remove from Waitlist"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Processes removing a contact from a waitlist"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class RemoveFromWaitlistHandler extends WebformHandlerBase {

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
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->validateGroupContact($form_state);
  }

  /**
   * Make sure the client part of the group
   */
  private function validateGroupContact(FormStateInterface $formState) {
    $this->civicrm->initialize();
    $contact_id = $formState->getValue('civicrm_2_contact_1_contact_existing');
    $group_id = $formState->getValue('waitlist');
    $results = \Civi\Api4\GroupContact::get(FALSE)
      ->addWhere('contact_id', '=', $contact_id)
      ->addWhere('group_id', '=', $group_id)
      ->addWhere('status', '=', 'Added')
      ->execute();
    if (!($results->first())) {
      $formState->setErrorByName('waitlist', $this->t('The selected client is not on this waitlist'));
    }
  }

  /**
   * {@inheritdoc}
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
      if (!empty($webform_submission_data['waitlist']) && !empty($data['contact'][2]['id'])){ 
        \Civi\Api4\GroupContact::update(FALSE)
          ->addWhere('contact_id', '=', $data['contact'][2]['id'])
          ->addWhere('group_id', '=', $webform_submission_data['waitlist'])
          ->addValue('status', 'Removed')
          ->execute();
        }
      }
    }
  }
}