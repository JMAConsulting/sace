<?php

namespace Drupal\sace_feedback_forms\Controller;

use Drupal\sace_feedback_forms\Utils;
use Drupal\sace_feedback_forms\Form\FeedbackSummaryForm;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class FormFinder extends ControllerBase {

  /**
   */
  protected $civicrm;

  /**
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  public static function create(ContainerInterface $container) {
    $instance = new self($container);
    $instance->civicrm = $container->get('civicrm');
    $instance->formBuilder = $container->get('form_builder');
    return $instance;
  }

  public function feedbackWebform($bookingId) {
    $this->civicrm->initialize();
    $feedbackForm = Utils::getFeedbackFormForBooking($bookingId);

    if (!$feedbackForm) {
      return 'No feedback form is set for this booking';
    }

    // urls use hyphenated key for some reason
    // Q: are there any other transformations?
    $feedbackForm = \str_replace('_', '-', $feedbackForm);

    $userContact = \CRM_Core_Session::getLoggedInContactID();

    // this is passed through from the URL previously. not sure
    // how it varies - maybe should be stored on the booking?
    $orgContact = \Drupal::request()->query->get('cid2') ?: 1;
    $userContact = \Drupal::request()->query->get('cid1') ?: $userContact;

    $url = "/form/{$feedbackForm}?bid={$bookingId}&cid1={$userContact}&cid2={$orgContact}";
    return new RedirectResponse($url);
  }

  public function feedbackSummaryForm($bookingId) {
    $this->civicrm->initialize();

    // check we have a user contact, required for form
    $contact = \CRM_Core_Session::getLoggedInContactID();
    if (!$contact) {
      return new Response('Feedback summary forms require a CiviCRM user contact', 400);
    }


    // for now just check feedback is configured - otherwise summary form makes no sense
    $feedbackForm = Utils::getFeedbackFormForBooking($bookingId);

    if (!$feedbackForm) {
      return new Response('Feedback summary forms require a CiviCRM user contact', 400);
    }



    return $this->formBuilder->getForm(FeedbackSummaryForm::class, $bookingId);
  }

}
