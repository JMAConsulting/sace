<?php

namespace Drupal\sace_feedback_forms\Form;

use Drupal\sace_feedback_forms\Utils;
use Drupal\sace_feedback_forms\TokenReplacement;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sace_feedback_forms\FeedbackSummary\QuestionSummary;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Sace Feedback Summary Form
 */
class FeedbackSummaryForm extends FormBase
{

  protected const UNASSIGNED_USER_CONTACT_ID = 860; // = Unassigned User Contact

  protected int $bookingId;

  protected array $bookingDetails;

  protected array $summaryFields = [];

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

    $this->getBookingContacts();

    $form['summary_header'] = [
      '#type' => 'fieldset',
      'booking_intro' => $this->getBookingDetailsIntro(),
      'feedback_counts' => $this->getFeedbackCounts(),
    ];

    $form['feedback_summary_fields'] = [
      '#type' => 'fieldset',
      ...$this->getSummaryFields(),
    ];

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

  protected function getBookingContacts() {
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

  protected function getFeedbackCounts(): array {
    $feedbackCounts = [
      '#type' => 'webform_flexbox',
    ];

    $submittedForms = \Civi\Api4\Activity::get(FALSE)
      ->addSelect('source_contact_id')
      ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
      ->addWhere('activity_type_id:name', '!=', 'Feedback Summary')
      ->execute()
      ->column('source_contact_id');

    $submittedOnline = count(array_filter($submittedForms, fn ($sourceContactId) => ($sourceContactId === self::UNASSIGNED_USER_CONTACT_ID)));

    $feedbackCounts['number_participants'] = [
      '#title' => 'Number of participants',
      '#type' => 'number',
      '#default_value' => $this->bookingDetails['Booking_Information.Number_of_Participants_per_course'],
      '#flex' => 1,
      '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
      '#suffix' => '</div></div>',
    ];
    $feedbackCounts['submitted_online'] = [
      '#title' => 'Number of online evaluations',
      '#type' => 'number',
      '#default_value' => $submittedOnline,
      '#flex' => 1,
      '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
      '#suffix' => '</div></div>',
    ];
    $feedbackCounts['submitted_by_staff'] = [
      '#title' => 'Number of staff entered evaluations',
      '#type' => 'number',
      '#default_value' => count($submittedForms) - $submittedOnline,
      '#flex' => 1,
      '#prefix' => '<div class="webform-flex webform-flex--1"><div class="webform-flex--container">',
      '#suffix' => '</div></div>',
    ];

    return [
      'feedback_counts' => $feedbackCounts,
    ];

  }

  /**
   * Fetch info about what fields appear on the feedback form for this activity
   * And from this determine the corresponding summary fields
   * @param int $bookingId
   * @return array
   */
  protected function getSummaryFields(): array
  {
    $summaryFields = [];

    $sourceFields = Utils::getFeedbackCustomFieldsForBooking($this->bookingId);

    $toFetch = array_map(fn ($field) => $field['key'], $sourceFields);

    $feedbackRecords = (array) \Civi\Api4\Activity::get(FALSE)
      ->addWhere('Feedback_Form.Booking', '=', $this->bookingId)
      ->addSelect(...$toFetch)
      ->execute();

    foreach ($sourceFields as $sourceFieldRecord) {
      // api4 key for the field
      $fieldSummary = QuestionSummary::createForField($sourceFieldRecord);
      $fieldset = $fieldSummary->getPrepopulatedFieldset($feedbackRecords);

      $summaryFields[$fieldSummary->getPrefix()] = $fieldset;
    }

    return $summaryFields;
  }

  protected function getVerifiedBy(): array {
    $loggedInContactId = \CRM_Core_Session::getLoggedInContactID();
    $currentContact = \Civi\Api4\Contact::get(FALSE)->addWhere('id', '=', $loggedInContactId)->addSelect('display_name')->execute()->single();
    $name = $currentContact['display_name'];

    return [
      '#type' => 'markup',
      '#markup' => "<div class='webform-flexbox'>
        <strong>Verified By</strong><span>{$name}</span>
      </div>",
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
    $formValues = $form_state->getValues();

    // TODO: what if we want to update an existing summary?
    $saveSummary = \Civi\Api4\Activity::create(FALSE)
      ->addValue('activty_type_id:name', 'Feedback Summary')
      ->addValue('Feedback_Form.Booking', $this->bookingId);
      //->addValue('source_contact_id', $formValues['source_contact']);

    // TODO: add standard fields for feedback counts
    foreach ($formValues as $key => $value) {
      if (\str_starts_with($key, 'summary_') && !\is_null($value)) {
        $storageField = QuestionSummary::getOrCreateSummaryDataField($key);
        $saveSummary->addValue($storageField, $value);
      }
    }

    $summaryId = $saveSummary->execute()->first()['id'];

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
