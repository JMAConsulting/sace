<?php

namespace Drupal\pe_update_booking\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Civi\Api4\EntityTag;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "ped_booking_update_handler",
 *   label = @Translation("PED Booking Update Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for adding customisation in all PE Booking update forms"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class PedBookingUpdateWebformHandler extends WebformHandlerBase {

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
    $this->validateTag($form_state);
    $this->validateDateDuration($form_state);
  }

  /**
   * Validate date duration to ensure end date is not before the start date.
   */
  private function validateDateDuration(FormStateInterface $formState) {
    $duration = (int) $formState->getValue('civicrm_1_activity_1_activity_duration') ?? NULL;
    $startDate = $formState->getValue('civicrm_1_activity_1_activity_activity_date_time');
    $endDate = $formState->getValue('civicrm_1_activity_1_cg2_custom_661');
    // if someone manually update the duration to be positive as it is editable, we are using timediff first to ensure start date is always before the end date
    if ((strtotime($endDate) < strtotime($startDate))  || $duration < 0) {
      $formState->setErrorByName('civicrm_1_activity_1_cg2_custom_661', $this->t('Start Date cannot be after the End Date.'));
    }
  }

  /**
   * Validate contact tags.
   */
  private function validateTag(FormStateInterface $formState) {
    $tags = !empty($formState->getValue('civicrm_2_contact_1_other_tag')) ?: NULL;
    if (empty($tags)) {
      $formState->setErrorByName('civicrm_2_contact_1_other_tag', $this->t('Please select one or more tag(s).'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
\CRM_Core_Error::debug_var('a', $webform_submission_data);
    if ($webform_submission_data) {
      $entityTagID = EntityTag::get(FALSE)
        ->addWhere('entity_id', '=', $webform_submission_data['civicrm_2_contact_1_contact_existing'])
        ->addWhere('entity_table', '=', 'civicrm_contact')
        ->addWhere('tag_id:name', '=', 'Unspecified Organization Type')
        ->execute()
        ->first()['id'];
      if (!empty($tagID)) {
        EntityTag::delete(FALSE)->addWhere('id', '=', $entityTagID)->execute();
      }
    }
  }

}
