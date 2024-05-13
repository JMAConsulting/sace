<?php

namespace Drupal\purge\Plugin\Purge\Invalidation;

use Drupal\purge\CacheTagMinificatorInterface;
use Drupal\purge\Plugin\Purge\Invalidation\Exception\InvalidExpressionException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Describes invalidation by Drupal cache tag, e.g.: 'user:1', 'menu:footer'.
 *
 * @PurgeInvalidation(
 *   id = "tag",
 *   label = @Translation("Tag"),
 *   description = @Translation("Invalidates by Drupal cache tag."),
 *   examples = {"node:1", "menu:footer"},
 *   expression_required = TRUE,
 *   expression_can_be_empty = FALSE,
 *   expression_must_be_string = TRUE,
 * )
 */
class TagInvalidation extends InvalidationBase implements InvalidationInterface {

  /**
   * The cache tag minificator service.
   *
   * @var \Drupal\purge\CacheTagMinificatorInterface
   */
  protected $cacheTagMinificator;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, $id, $expression, CacheTagMinificatorInterface $cache_tag_minificator) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $id, $expression);

    $this->cacheTagMinificator = $cache_tag_minificator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      [],
      $plugin_id,
      $plugin_definition,
      $configuration['id'],
      $configuration['expression'],
      $container->get('purge.cache_tag_minificator')
    );
 }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    $string = parent::__toString();

    return $this->cacheTagMinificator->minifyCacheTag($string);
  }

  /**
   * {@inheritdoc}
   */
  public function validateExpression() {
    parent::validateExpression();
    if (strpos($this->expression, '*') !== FALSE) {
      throw new InvalidExpressionException('Tag invalidations cannot contain asterisks.');
    }
  }

}
