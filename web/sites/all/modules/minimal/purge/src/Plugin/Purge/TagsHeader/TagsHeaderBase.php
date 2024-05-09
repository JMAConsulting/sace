<?php

namespace Drupal\purge\Plugin\Purge\TagsHeader;

use Drupal\purge\CacheTagMinificatorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\PluginBase;

/**
 * Base implementation for plugins that add and format a cache tags header.
 */
abstract class TagsHeaderBase extends PluginBase implements TagsHeaderInterface {
  /**
  +   * The cache tag minificator service.
  +   *
  +   * @var \Drupal\purge\CacheTagMinificatorInterface
  +   */
  protected $cacheTagMinificator;

  /**
   * TagsHeaderBase constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition
   * @param \Drupal\purge\CacheTagMinificatorInterface $cache_tag_minificator
   *   The cache tag minificator service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CacheTagMinificatorInterface $cache_tag_minificator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->cacheTagMinificator = $cache_tag_minificator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('purge.cache_tag_minificator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getHeaderName() {
    return $this->getPluginDefinition()['header_name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(array $tags) {
    $tags = array_map([$this->cacheTagMinificator, 'minifyCacheTag'], $tags);
    return implode(' ', $tags);
  }

  /**
   * {@inheritdoc}
   */
  public function isEnabled() {
    return TRUE;
  }

}
