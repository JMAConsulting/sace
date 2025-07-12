<?php

namespace Drupal\sace_feedback_forms\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\sace_feedback_forms\Utils;

class AssignFeedbackFormToBooking extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'sace_feedback_forms_assign_to_booking';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['booking_id'] = [
      '#type' => 'hidden',
      '#title' => $this->t('Booking ID'),
      '#required' => TRUE,
      '#default_value' => \Drupal::request()->get('bid'),
    ];

    $form['webform'] = [
      '#type' => 'select',
      '#title' => $this->t('Select the evaluation form to use for this booking'),
      '#required' => TRUE,
      '#options' => Utils::getWebformOptionsForHandler('sace_feedback_form_handler'),
      '#empty_option' => $this->t('- Select -'),
    ];

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    \Drupal::service('civicrm')->initialize();
    \Civi\Api4\Activity::update(TRUE)
      ->addWhere('id', '=', $values['booking_id'])
      ->addValue('Booking_Information.Feedback_Webform', $values['webform'])
      ->execute();

    \Drupal::messenger()->addMessage($this->t('Feedback form assigned'));

    $form_state->setRedirect('sace_feedback_forms.feedback', [
      'bookingId' => $values['booking_id']
    ]);
  }

}
