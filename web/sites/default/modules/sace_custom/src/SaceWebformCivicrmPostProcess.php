<?php

namespace Drupal\sace_custom;


/**
 * @file
 * Front-end form validation and post-processing.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform_civicrm\WebformCivicrmBase;


class SaceWebformCivicrmPostProcess extends WebformCivicrmBase implements WebformCivicrmPostProcessInterface {

  /**
   * Process webform submission after it is has been saved. Called by the following hooks:
   * @see webform_civicrm_webform_submission_insert
   * @see webform_civicrm_webform_submission_update
   * @param stdClass $submission
   */
  public function postSave(WebformSubmissionInterface $webform_submission) {
   $this->submission = $webform_submission;
   print_r($this);exit;
  }

}
