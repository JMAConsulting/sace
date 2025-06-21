<?php

namespace Drupal\sace_feedback_forms\Plugin\WebformHandler;

use Drupal\sace_feedback_forms\Utils;
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

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
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
  public function alterElements(array &$elements, WebformInterface $webform)
  {
    $this->civicrm->initialize();
    $bookingId = \Drupal::request()->query->get('bid');

    $bookingField = $this->getBookingIdField();

    Utils::addElementOrSetDefault($elements, $bookingField, [
      '#type' => 'textfield',
      '#title' => 'Booking Reference ID',
      '#title_display' => 'inline',
      '#states' => [
        'readonly' => [
          ":input[name=\"{$bookingField}\"]" => [
            'filled' => TRUE
          ],
        ],
      ],
      //'#default_value' => '[current-page:query:bid]',
      '#default_value' => $bookingId,
      '#data_type' => 'Int',
      '#form_key' => $bookingField,
      '#extra' => [
        'width' => 20,
      ],
    ]);

    // TODO: if feedback fields were configured through CiviCRM, we could add them here
    //
    //    $fieldGroupId = \Civi\Api4\Activity::get(FALSE)
    //      ->addWhere('id', '=', $bookingId)
    //      ->addSelect('Booking_Information.Feedback_Field_Group')
    //      ->execute()
    //      ->first()['Booking_Information.Feedback_Field_Group'] ?? NULL;

    //    $feedbackFields = \Civi\Api4\CustomField::get(FALSE)
    //      ->addWhere('custom_group_id', '=', $fieldGroupId)
    //      ->addSelect('id', 'html_type', 'label')
    //      ->execute();

    //    foreach ($feedbackFields as $field) {
    //      $elementKey = "civicrm_1_activity_1_cg{$fieldGroupId}_custom_{$field['id']}";
    //      $elements[$elementKey] = [
    //        '#type' => 'text',
    //        '#title' => $field['label'],
    //      ];
    //    }
  }

  //  /**
  //   * @inheritdoc
  //   *
  //   * Populate the booking ID field based on url query param
  //   */
  //  public function alterForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
  //    $elements = WebformFormHelper::flattenElements($form);
  //    if (empty($elements)) {
  //      return;
  //    }

  //    $bookingId = \Drupal::request()->query->get('bid');
  //    if (!$bookingId) {
  //      \Drupal::messenger()->addError("No booking ID passed to feedback form");
  //      return;
  //    }

  //    $this->civicrm->initialize();

  //    $bookingField = $this->getBookingIdField();

  //    $elements[$bookingField]['#default_value'] = $bookingId;
  //  }

  protected function getBookingIdField(): string {
    return Utils::getWebformFieldForCustomField('Booking', 'Feedback_Form', 'activity');
  }

}
