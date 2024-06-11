<?php

namespace Drupal\clin_security\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\WebformSubmissionInterface;
use Drupal\webform\Plugin\WebformHandlerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace CiviCRM Activity Update Handler.
 *
 * @WebformHandler(
 *   id = "clin_security_handler",
 *   label = @Translation("CLIN Security Handler"),
 *   category = @Translation("CRM"),
 *   description = @Translation("Webform Handler for fixing the CLIN Security Form"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class ClinSecurityWebformHandler extends WebformHandlerBase {

  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  private $database;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->civicrm = $container->get('civicrm');
    $instance->database = \Drupal::database();
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
    $this->civicrm->initialize();
    $webform_submission_data = $webform_submission->getData();
    if ($webform_submission_data) {
      if(isset($webform_submission_data['is_active']) && $webform_submission_data['is_active'] != '') {
        $results = \Civi\Api4\Relationship::update(TRUE)
        ->addValue('is_active', TRUE)
        ->addWhere('id', 'IN', explode(',', $webform_submission_data['is_active']))
        ->execute();
      }
      if(isset($webform_submission_data['is_not_active']) && $webform_submission_data['is_not_active'] != ''){
        $results = \Civi\Api4\Relationship::update(TRUE)
        ->addValue('is_active', FALSE)
        ->addWhere('id', 'IN', explode(',', $webform_submission_data['is_not_active']))
        ->execute();
      }
    }
  }
}
