<?php

namespace Drupal\sace_feedback_forms\FeedbackSummary;

/**
 * SubmittedByCounts is a bit of a special case but has the same public
 * interface
 */
class SubmittedByCounts extends QuestionSummary
{

  /**
   * CiviCRM field key for the feedback form question to be summarised
   * e.g. MyCustomGroup.MyCustomField
   */
  protected string $sourceField = 'source_contact_id';

  /**
   * Normally this is a CiviCRM Custom Field but here its not really used
   *
   */
  protected array $sourceFieldDetails = [];

  public function __construct(string $sourceField, array $sourceFieldDetails = []) {
    if ($sourceField !== 'source_contact_id') {
      throw new \CRM_Core_Exception("SubmittedByCounts cannot be initialised for {$sourceField}");
    }
  }

  public function getPrefix(): string {
    return "submitted_by_counts";
  }

  /**
   * Get the elements to include on the summary form for this question
   */
  public function getFieldset(): array {
    return [
      '#type' => 'webform_flexbox',
      ...$this->getElements(),
    ];
  }

  /**
   * @inheritdoc
   */
  public function getStorageFields(): array {
    return [
      'total_number_participants' => [
        'name' => 'total_number_participants',
        'label' => 'Total number of participants',
        'html_type' => 'number',
      ],
      'feedback_forms_submitted_online' => [
        'name' => 'feedback_forms_submitted_online',
        'label' => 'Number of evaluations submitted online',
        'html_type' => 'number',
      ],
      'feedback_forms_submitted_by_staff' => [
        'name' => 'feedback_forms_submitted_by_staff',
        'label' => 'Number of staff-entered evaluations',
        'html_type' => 'number',
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function getElements(): array {
    return [
      'total_number_participants' => [
        '#title' => 'Number of participants',
        '#type' => 'number',
        // NOTE this must be provided in FeedbackSummaryForm
        // '#default_value' => $this->bookingDetails['Booking_Information.Number_of_Participants_per_course'],
        '#flex' => 1,
        '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
        '#suffix' => '</div></div>',
      ],
      'feedback_forms_submitted_online' => [
        '#title' => 'Number of online evaluations',
        '#type' => 'number',
        '#flex' => 1,
        '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
        '#suffix' => '</div></div>',
      ],
      'feedback_forms_submitted_by_staff' => [
        '#title' => 'Number of staff entered evaluations',
        '#type' => 'number',
        '#flex' => 1,
        '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
        '#suffix' => '</div></div>',
      ],
    ];
  }

  /**
   * @inheritdoc
   */
  public function prepopulateValues(array &$fieldset, array $feedbackRecords): void {
    $submittedSourceContacts = \array_column($feedbackRecords, 'source_contact_id');
    $submittedOnline = count(array_filter($submittedSourceContacts, fn($sourceContactId) => ($sourceContactId === self::UNASSIGNED_USER_CONTACT_ID)));

    $fieldset['feedback_forms_submitted_online']['#default_value'] = $submittedOnline;
    $fieldset['feedback_forms_submitted_by_staff']['#default_value'] = count($submittedSourceContacts) - $submittedOnline;
  }

}
