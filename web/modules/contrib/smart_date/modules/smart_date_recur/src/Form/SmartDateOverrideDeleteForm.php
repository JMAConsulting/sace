<?php

namespace Drupal\smart_date_recur\Form;

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a deletion confirmation form for Smart Date Overrides.
 */
class SmartDateOverrideDeleteForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this
      ->t('Are you sure you want to revert this instance to its default values?');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $rrule = $this->entity->rrule->getString();
    return new Url('smart_date_recur.instances', ['rrule' => (int) $rrule]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this
      ->t('Revert to Default');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // TODO: Delete override entity, if it exists.
    $this->entity
      ->delete();
    // TODO: Update parent entity field value.
    $form_state
      ->setRedirectUrl($this
        ->getCancelUrl());
  }

}
