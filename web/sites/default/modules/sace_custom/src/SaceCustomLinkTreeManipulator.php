<?php

namespace Drupal\sace_custom;

use Drupal\Core\Access\AccessManagerInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\DefaultMenuLinkTreeManipulators;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * Defines the access control handler for the menu item.
 */
class SaceCustomLinkTreeManipulator extends DefaultMenuLinkTreeManipulators {

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Constructs a \Drupal\Core\Menu\DefaultMenuLinkTreeManipulators object.
   *
   * @param \Drupal\Core\Access\AccessManagerInterface $access_manager
   *   The access manager.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   The current user.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   An implementation of the entity repository interface.
   */
  public function __construct(AccessManagerInterface $access_manager, AccountInterface $account, EntityTypeManagerInterface $entity_type_manager, ConfigManagerInterface $config_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($access_manager, $account, $entity_type_manager);
    $this->configManager = $config_manager->getConfigFactory();
    $this->entityRepository = $entity_repository;
  }


  /**
   * Checks access for one menu link instance.
   *
   * This function adds to the checks provided by
   * DefaultMenuLinkTreeManipulators to allow us to check any roles which
   * have been added to a menu item to allow or deny access.
   *
   * @param \Drupal\Core\Menu\MenuLinkInterface $instance
   *   The menu link instance.
   *
   * @return \Drupal\Core\Access\AccessResult
   *   The access result.
   */
  protected function menuLinkCheckAccess(MenuLinkInterface $instance) {
    $access_result = parent::menuLinkCheckAccess($instance);
    $url = $instance->getUrlObject();
    // Now make sure the module has been enabled and installed correctly.
    if (strpos($url->toString(), '/calendar-team') !== FALSE && $url->toString() !== '/calendar-team/all') {
      // Set the access result as forbidden in case the user does not
      // have a role required.
      $route_name = \Drupal::routeMatch()->getRouteName();
      $access_result = AccessResult::forbidden();
      if ($route_name === 'entity.menu.edit_form') {
        $access_result = AccessResult::allowed();
      }
      else {
        $userId = \Drupal::currentUser()->id();
        $user = \Drupal::entityTypeManager()->getStorage('user')->load($userId);
        $teamIds = $user->get('field_user_team')->getValue();
        foreach ($teamIds as $teamId) {
          if ($teamId['target_id'] == str_replace('/calendar-team/', '', $url->toString())) {
            $access_result = AccessResult::allowed();
          }
        }
      }
    }
    return $access_result->cachePerPermissions();
  }

  /**
   * Check if we need check the access for this item.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *   The config from the menu_item_role_access module.
   * @param \Drupal\Core\Url $url
   *   The current Url object for the menu item.
   *
   * @return bool
   *   Returns TRUE if we need to check access otherwise FALSE.
   */
  private function checkUrl(ImmutableConfig $config, Url $url) {
    $check_internal = $config->get('overwrite_internal_link_target_access');
    // If we want to check this URL or not.
    $check_url = $check_internal == TRUE ? TRUE : !$url->isRouted();

    $special_cases = [
      '<nolink>',
      '<none>',
    ];

    // Check the special case of a no link item.
    if ($url->isExternal() === FALSE && $url->isRouted() && in_array($url->getRouteName(), $special_cases)) {
      $check_url = TRUE;
    }

    return $check_url;
  }

}
