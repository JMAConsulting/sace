<?php

namespace Drupal\sace_flag_output_modification\Plugin\WebformHandler;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformInterface;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sace Flag Output Modification Webform Handler plugin.
 *
 * @WebformHandler(
 *   id = "sace_flag_webform_handler",
 *   label = @Translation("Sace Flag Webform Handler"),
 *   category = @Translation("CRM"),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */
class SaceFlagWebformHandler extends WebformHandlerBase {

  /**
   * The CiviCRM service.
   *
   * @var \Drupal\civicrm\Civicrm
   */
  protected $civicrm;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->civicrm = $container->get('civicrm');
    return $instance;
  }

  public function alterElements (array &$elements, WebformInterface $webform) {
    $this->civicrm->initialize();
    \Drupal::logger('sace_flag_output_modifications')->notice('Webform Elements %elements', ['%elements' => json_encode($elements)]);
  }

}
