<?php

namespace Drupal\sace_feedback_forms\Plugin\WebformHandler;

use Drupal\sace_feedback_forms\Utils;
use Drupal\sace_feedback_forms\TokenReplacement;
use Drupal\webform\Utility\WebformFormHelper;
use Drupal\webform\Utility\WebformElementHelper;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\WebformInterface;
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
class FeedbackForm extends WebformHandlerBase
{
  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  protected int $bookingId;

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
   *
   * Add booking ID field to the form
   * This is populated from query param in postSave
   */
  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $this->civicrm->initialize();
    $this->bookingId = \Drupal::request()->query->get('bid') ?: 0;

    foreach ($this->getHeaderFields() as $key => $fieldDesc) {
      Utils::addElementOrSetDefault($form, $key, $fieldDesc);
    }

    if ($this->bookingId) {
      $bookingDetails = Utils::getBookingDetails($this->bookingId);
      TokenReplacement::run(['[the presentation topic]' => $bookingDetails['topic'] ?: 'the topics covered'], $form);
    }
  }

  protected function getHeaderFields(): array {
    $bookingIdField = Utils::getWebformFieldForCustomField('Booking', 'Feedback_Form', 'activity');
    $fields = [
      $bookingIdField => [
        '#type' => 'textfield',
        '#title' => 'Booking Reference ID',
        '#title_display' => 'inline',
        '#states' => [
          'readonly' => [
            ":input[name=\"{$bookingIdField}\"]" => [
              'filled' => TRUE,
            ],
          ],
        ],
        '#data_type' => 'Int',
        '#form_key' => $bookingIdField,
        '#extra' => [
          'width' => 20,
        ],
      ],
      'date_of_presentation' => [
        '#type' => 'datetime',
        '#title' => 'Date of Presentation',
      ],
    ];

    if ($this->bookingId) {
      $fields[$bookingIdField]['#default_value'] = $this->bookingId;

      $bookingDate = \Civi\Api4\Activity::get(FALSE)
        ->addWhere('id', '=', $this->bookingId)
        ->addSelect('activity_date_time')
        ->execute()
        ->first()['activity_date_time'] ?? NULL;

      // $fields['date_of_presentation']['#default_value'] = $bookingDate;
    }

    return $fields;
  }

}
