<?php

namespace Drupal\civicrm_entity_vf\Plugin\views\filter;

use Drupal\views\Views;
use Drupal\views\Plugin\views\filter\InOperator;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\civicrm_entity\CiviCrmApiInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Views filter handler for user contacts.
 *
 * @ViewsFilter("civicrm_entity_vf_uf_select")
 */
class UfSelect extends InOperator implements ContainerFactoryPluginInterface {

  /**
   * User entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $userEntityStorage;

  /**
   * User entity query.
   *
   * @var \Drupal\Core\Entity\Query\QueryInterface
   */
  protected $userQuery;

  /**
   * The CiviCRM API.
   *
   * @var \Drupal\civicrm_entity\CiviCrmApiInterface
   */
  protected $civicrmApi;

  /**
   * Constructs a new instance.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param Drupal\Core\Entity\EntityStorageInterface $userEntityStorage
   *   User entity storage object.
   * @param Drupal\Core\Entity\Query\QueryInterface $userQuery
   *   User entity query object.
   * @param Drupal\civicrm_entity\CiviCrmApiInterface $civicrmApi
   *   The CiviCRM Api.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityStorageInterface $userEntityStorage, QueryInterface $userQuery, CiviCrmApiInterface $civicrmApi) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->userEntityStorage = $userEntityStorage;
    $this->userQuery = $userQuery;
    $this->civicrmApi = $civicrmApi;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('entity_type.manager')->getStorage('user')->getQuery(),
      $container->get('civicrm_entity.api')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {

    if (!isset($this->valueOptions)) {

      // Get active user uids.
      $uids = $this->userQuery
        ->accessCheck()
        ->condition('status', 1)
        ->execute();

      // Get user display names.
      $users = $this->userEntityStorage->loadMultiple($uids);
      $user_display_names = [];
      foreach ($users as $uid => $user) {
        /** @var \Drupal\User\Entity\User $user */
        $user_display_names[$uid] = $user->getDisplayName();
      }

      // Get matching list of CiviCRM contacts.
      $user_contacts = $this->civicrmApi->get('UFMatch', [
        'sequential' => 1,
        'uf_id' => ['IN' => $uids],
        'options' => ['limit' => count($uids)],
        'return' => ['uf_id', 'contact_id.id'],
      ]);

      // Build valueOptions.
      foreach ($user_contacts as $contact) {
        $this->valueOptions[$contact['contact_id.id']] = $user_display_names[$contact['uf_id']];
      }

      // Sort by username.
      natcasesort($this->valueOptions);
    }

    return $this->valueOptions;
  }

  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['expose']['contains']['user_team'] = ['default' => ''];
    return $options;
  }

  public function defaultExposeOptions(): void {
    parent::defaultExposeOptions();
    $this->options['expose']['user_team'] = '';
  }

  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);
    $tids = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->getQuery()
    ->condition('vid', 'user_team')
    ->accessCheck(TRUE)
    ->execute();
    $taxonomies = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
    $user_teams = [];
    foreach ($taxonomies as $taxonomy) {
      $user_teams[$taxonomy->id()] = $taxonomy->name->value;
    }
    $form['expose']['user_team'] = [
      '#type' => 'select',
      '#title' => $this->t('Limit list to selected user team'),
      '#description' => $this->t('If selected, the only users in the specified user team will be selected'),
      // Safety.
      '#default_value' => $this->options['expose']['user_team'],
      '#options' => $user_teams,
      '#multiple' => TRUE,
    ];
  }

  public function buildExposedForm(&$form, FormStateInterface $form_state) {
    parent::buildExposedForm($form, $form_state);
    $view = $form_state->get('view');
    // Check for a views contextual filter with term id args in the 'user_team' taxonomy.
    $user_team_target_ids = [];
    foreach ($view->display_handler->getHandlers('argument') as $handler) {
      if ('taxonomy_term.tid' == $handler->getEntityType() . $handler->getField()) {
        $tids = !empty($_GET['tid']) ? [$_GET['tid']] : explode(' ', $handler->getValue() ?? '');
        $terms = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadMultiple($tids);
        if (!empty($terms)) {
          foreach ($terms as $term) {
            if ('user_team' == $term->bundle()) {
              $user_team_target_ids[] = $term->id();
            }
          }
          if (!empty($user_team_target_ids)) {
            break;
          }
        }
      }
    }
    if (empty($user_team_target_ids) && !empty($this->options['expose']['user_team'])) {
      $user_team_target_ids = array_keys($this->options['expose']['user_team']);
    }
    if (!empty($user_team_target_ids)) {
      $team_users = array_keys(\Drupal::entityTypeManager()->getStorage('user')->loadByProperties([
       'field_user_team' => $user_team_target_ids,
      ]));
      $contact_ids = $team_contact_id_map = [];
      $team_contact_id_lookup = \Drupal::service('civicrm_entity.api')->get('UFMatch', [
        'sequential' => 1,
        'return' => ['uf_id', 'contact_id'],
        'uf_id' => ['IN' => $team_users],
        'options' => ['limit' => 0],
      ]);
      foreach($team_contact_id_lookup as $tc) {
        $contact_ids[$tc['contact_id']] = $tc['contact_id'];
        $team_contact_id_map[$tc['contact_id']] = $tc['uf_id'];
      }
      $options = [];
      foreach ($contact_ids as $contact_id) {
        if (!array_key_exists($contact_id, $options)) {
          $account = \Drupal::entityTypeManager()->getStorage('user')->load($team_contact_id_map[$contact_id]);
          /** @var \Drupal\User\Entity\User $account */
          $options[$contact_id] = $account->getAccountName();
        }
      }
      $form[$this->options['expose']['identifier']]['#options'] = $options;
    }
  }

}
