<?php

namespace Drupal\sace_feedback_forms\Controller;

use Drupal\sace_feedback_forms\Utils;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

class FormFinder extends ControllerBase {

  public function feedbackForm($bookingId) {
    \Drupal::service('civicrm')->initialize();
    $feedbackForm = Utils::getFeedbackFormForBooking($bookingId);

    if (!$feedbackForm) {
      return 'No feedback form is set for this booking';
    }

    // urls use hyphenated key for some reason
    // Q: are there any other transformations?
    $feedbackForm = \str_replace('_', '-', $feedbackForm);

    // this is passed through from the URL previously. not sure
    // how it varies - maybe should be stored on the booking?
    $orgContact = \Drupal::request()->query->get('cid1');

    $url = "/form/{$feedbackForm}?bid={$bookingId}&cid1={$orgContact}";
    return new RedirectResponse($url);
  }

  public function feedbackSummaryForm($bookingId) {
    \Drupal::service('civicrm')->initialize();


// TODO: maybe you want to configure a special summary form
// BUT: this won't work with the autopopulation in our FeedbackSummaryForm webhandler
// currently
//    $summaryForm = \Civi\Api4\Activity::get(FALSE)
//      ->addWhere('id', '=', $bookingId)
//      ->addSelect('Booking_Information.Feedback_Summary_Webform')
//      ->execute()
//      ->first()['Booking_Information.Feedback_Summary_Webform'] ?? NULL;

//    if (!$summaryForm) {
//      // use generic summary form
//      // (summary fields will be autopopulated based on configured Feedback Form)
//      $summaryForm = 'feedback_summary';
//    }

    // for now just check feedback is configured - otherwise summary form makes no sense
    $feedbackForm = Utils::getFeedbackFormForBooking($bookingId);

    if (!$feedbackForm) {
      return 'No feedback form is set for this booking';
    }

    $summaryForm = 'feedback-summary';

    // this is passed through from the URL previously. not sure
    // how it varies - maybe should be stored on the booking?
    $orgContact = \Drupal::request()->query->get('cid1');

    $url = "/form/{$summaryForm}?bid={$bookingId}&cid1={$orgContact}";
    return new RedirectResponse($url);
  }

}
