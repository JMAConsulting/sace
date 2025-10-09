<?php

namespace Drupal\sace_feedback_forms\Form;

use Drupal\sace_feedback_forms\Utils;
use Drupal\sace_feedback_forms\TokenReplacement;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\redirect\Entity\Redirect;
use Drupal\sace_feedback_forms\FeedbackSummary\QuestionSummary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Civi\Api4\Activity;
/**
 * Sace Feedback Summary Form
 */
class FeedbackSummaryForm extends FormBase
{

  protected int $bookingId;

  protected array $bookingDetails;

  protected array $questionSummaries;

  protected array $submittedForms;

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sace_feedback_summary_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $bookingId = NULL) {
    $this->bookingId = $bookingId;

    $this->bookingDetails = Utils::getBookingDetails($this->bookingId);

    $this->fetchBookingContacts();

    $this->fetchQuestions();

    $this->fetchSubmittedForms();

    $form['booking_details'] = [
      '#type' => 'fieldset',
      'booking_intro' => $this->getBookingDetailsIntro(),
    ];

    $form['question_summary_fields'] = [
      '#type' => 'fieldset',
      ...$this->getQuestionSummaryFields(),
    ];

    // unusual case the default value is provided from the booking rather than submitted forms
    $form['question_summary_fields']['submitted_by_counts']['total_number_participants']['#default_value'] = $this->bookingDetails['Booking_Information.Number_of_Participants_per_course'];

    $form['verified_by'] = $this->getVerifiedBy();

    $this->replaceTokens($form);

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    // add custom js/css
    $form['#attached']['library'][] = 'sace_feedback_forms/sace_feedback_summary_form';

    return $form;
  }

  protected function fetchBookingContacts() {
    $activityContacts = \Civi\Api4\ActivityContact::get(FALSE)
      ->addWhere('activity_id', '=', $this->bookingId)
      ->addSelect('contact_id.display_name', 'record_type_id:name')
      ->execute();

    $namesByType = [];

    foreach ($activityContacts as $record) {
      $type = $record['record_type_id:name'];
      $namesByType[$type] ??= [];
      $namesByType[$type][] = $record['contact_id.display_name'];
    }

    $this->bookingDetails['target_contacts'] = $namesByType['Activity Targets'];
    $this->bookingDetails['assignee_contacts'] = $namesByType['Activity Assignees'];
  }

  /**
   * Build model for summary fields based on what fields appear on the feedback form for this activity
   */
  protected function fetchQuestions(): void {
    // always include source contact id, which is summarised into online/staff entered counts
    $sourceFields = [['key' => 'source_contact_id'], ...Utils::getFeedbackCustomFieldsForBooking($this->bookingId)];

    foreach ($sourceFields as $sourceFieldRecord) {
      // api4 key for the field
      $this->questionSummaries[$sourceFieldRecord['key']] = QuestionSummary::createForField($sourceFieldRecord);
    }
  }

  protected function fetchSubmittedForms(): void {
    $toFetch = array_keys($this->questionSummaries);

    $this->submittedForms = (array) \Civi\Api4\Activity::get(FALSE)
      ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
      ->addWhere('activity_type_id:name', '!=', 'Feedback Summary')
      ->addSelect(...$toFetch)
      ->execute();
  }

  protected function getBookingDetailsIntro(): array {
    $details = array_filter([
      "Booking Reference ID" => $this->bookingId,
      "Presentation Topic" => $this->bookingDetails['topic'],
      //"Presentation Topic Detail" => '',
      "With" => implode(', ', $this->bookingDetails['target_contacts']),
      //"Postal Code" => $this->bookingDetails['postal_code'],
      "Date" => \DateTime::createFromFormat("Y-m-d H:i:s", $this->bookingDetails['activity_date_time'])->format('l, F j, Y h:i A'),
      "Length" => "{$this->bookingDetails['duration']} minutes",
      "Audience Age" => $this->bookingDetails['Booking_Information.Youth_or_Adult'],
      "Presenter(s)" => implode(', ', $this->bookingDetails['assignee_contacts']),
    ]);

    $markup = [];

    foreach ($details as $label => $value) {
      $markup[] = "<div class='webform-flexbox'><dt>{$label}</dt><dd>{$value}</dd></div>";
    }

    $markup = implode("\n", $markup);
    $markup = "<div class='sace-feedback-summary-form--booking-details-intro'>{$markup}</div>";

    $bookingIntro = [
      '#type' => 'markup',
      '#markup' => $markup,
    ];

    return $bookingIntro;
  }

  /**
   * Fetch info about what fields appear on the feedback form for this activity
   * And from this determine the corresponding summary fields
   * @return array
   */
  protected function getQuestionSummaryFields(): array {
    $summaryFields = [];

    foreach ($this->questionSummaries as $sourceQuestion) {
      // api4 key for the field
      $fieldset = $sourceQuestion->getPrepopulatedFieldset($this->submittedForms);

      $summaryFields[$sourceQuestion->getPrefix()] = $fieldset;
    }
    return $summaryFields;
  }

  protected function getVerifiedBy(): array {
    $loggedInContactId = \CRM_Core_Session::getLoggedInContactID();
    $currentContact = \Civi\Api4\Contact::get(FALSE)
      ->addWhere('id', '=', $loggedInContactId)
      ->addSelect('display_name')
      ->execute()
      ->single();
    $name = $currentContact['display_name'];

    return [
      '#type' => 'markup',
      '#markup' => "<div class='webform-flexbox'><strong>Verified By</strong><span>{$name}</span></div>",
    ];
  }

  /**
   * Replace any token values in form elements with values for
   * the given booking, using TokenReplacement util class
   */
  protected function replaceTokens(&$form) {
    $tokenValues = [
      '[the presentation topic]' => $this->bookingDetails['topic'] ?: 'the topics covered',
    ];
    TokenReplacement::run($tokenValues, $form);
  }

  /**
   * Save submitted values to CiviCRM activity
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $activity = Activity::get(FALSE)
      ->addSelect('id')
      ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
      ->addWhere('activity_type_id:name', '=', 'Feedback Summary')
      ->execute()
      ->first();

    $saveSummary = Activity::create(FALSE)
      ->addValue('activity_type_id:name', 'Feedback Summary')
      ->addValue('Feedback_Form.Booking', $this->bookingId)
      ->addValue('source_contact_id', \CRM_Core_Session::getLoggedInContactID());

    if ($activity) {
      // update existing summary
      $saveSummary = Activity::update(FALSE)->addWhere('id', '=', $activity['id']);
    }

    // now get submitted values from the form
    $formValues = $form_state->getValues();

    // build model of storage fields based on question summaries
    $storageFields = [];
    foreach ($this->questionSummaries as $sourceQuestion) {
      $storageFields = array_merge($storageFields, $sourceQuestion->getStorageFields());
    }

    foreach ($formValues as $key => $value) {
      if (is_null($value)) {
        continue;
      }
      $storageField = $storageFields[$key] ?? NULL;
      if (!$storageField) {
        continue;
      }

      $storageFieldKey = QuestionSummary::getOrCreateStorageField($storageField);
      $saveSummary->addValue($storageFieldKey, $value);
    }

    $summaryId = $saveSummary->execute()->first()['id'];

    $form_state->setRedirectUrl(Url::fromUri('base:/civicrm/activity', [
      'query' => [
        'reset' => 1,
        'action' => 'view',
        'id' => $summaryId,
      ],
    ]));

//    $activityContactSave = \Civi\Api4\ActivityContact::save(FALSE);

//    foreach ($formValues['target_contacts'] as $contactId) {
//      $activityContactSave->addRecord([
//        'contact_id' => $contactId,
//        'activity_id' => $summaryId,
//        'record_type_id:name' => 'target',
//      ]);
//    }

//    foreach ($formValues['assinee_contacts'] as $contactId) {
//      $activityContactSave->addRecord([
//        'contact_id' => $contactId,
//        'activity_id' => $summaryId,
//        'record_type_id:name' => 'assignee',
//      ]);
//    }

//    $activityContactSave->execute();
  }

}
