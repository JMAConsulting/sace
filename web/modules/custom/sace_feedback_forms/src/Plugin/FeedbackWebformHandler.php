<?php

namespace Drupal\sace_feedback_forms\Plugin\FeedbackWebformHandler;

use Drupal\sace_feedback_forms\Utils;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace Feedback Form Handler

 * @WebformHandler(
 *   id = "sace_feedback_form_handler",
 *   label = @Translation("SACE Feedback Form Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler to identify forms used for feedback and propogate booking ID"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class FeedbackWebformHandler extends WebformHandlerBase {
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
   * @inheritdoc
   *
   * Populate the booking ID field based on url query param
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $elements = WebformFormHelper::flattenElements($form);
    if (empty($elements)) {
      return;
    }

    $bookingId = \Drupal::request()->query->get('bid');
    if (!$bookingId) {
      \Drupal::messenger()->addError("No booking ID passed to feedback form");
      return;
    }

    $this->civicrm->initialize();

    $bookingField = Utils::getWebformFieldForCustomField('Booking_Reference_ID', 'PED_Booking_Reference', 'activity');

    $elements[$bookingField]['#default_value'] = $bookingId;
  }

}
